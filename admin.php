<?php
require 'auth.php';
require 'db.php';
require 'atgadinajumi_lib.php';

$statusi = [
    'Saņemts',
    'Izskatīšanā',
    'Nepieciešama papildu informācija',
    'Piedāvājums sagatavots',
    'Sadarbība apstiprināta',
    'Noraidīts',
    'Pabeigts',
];

// --- Atgādinājumu skaits (katrs pieteikums tiek skaitīts vienreiz) ---
$atgId = [];
foreach (neizskatitiPieteikumi($pdo, ATGADINAJUMS_DIENAS) as $a) {
    $atgId[$a['id']] = true;
}
foreach (sazinasTermini($pdo) as $a) {
    $atgId[$a['id']] = true;
}
$atgSkaits = count($atgId);

// --- Filtru vērtības no adreses (GET) ---
$meklet = trim($_GET['meklet'] ?? '');
$filtrsStatuss = $_GET['statuss'] ?? '';
$no = $_GET['no'] ?? '';
$lidz = $_GET['lidz'] ?? '';
$kartot = ($_GET['kartot'] ?? 'jaunakie') === 'vecakie' ? 'ASC' : 'DESC';

$datumaFormats = '/^\d{4}-\d{2}-\d{2}$/';

$nosacijumi = [];
$parametri = [];

if ($meklet !== '') {
    $nosacijumi[] = "(CONCAT(vards, ' ', uzvards) LIKE ? OR nosaukums LIKE ?)";
    $parametri[] = '%' . $meklet . '%';
    $parametri[] = '%' . $meklet . '%';
}

if (in_array($filtrsStatuss, $statusi, true)) {
    $nosacijumi[] = 'statuss = ?';
    $parametri[] = $filtrsStatuss;
}

if (preg_match($datumaFormats, $no)) {
    $nosacijumi[] = 'DATE(sanemts_datums) >= ?';
    $parametri[] = $no;
} else {
    $no = '';
}

if (preg_match($datumaFormats, $lidz)) {
    $nosacijumi[] = 'DATE(sanemts_datums) <= ?';
    $parametri[] = $lidz;
} else {
    $lidz = '';
}

$sql = 'SELECT * FROM pieteikumi';
if ($nosacijumi) {
    $sql .= ' WHERE ' . implode(' AND ', $nosacijumi);
}
$sql .= " ORDER BY sanemts_datums $kartot, id $kartot";

$stmt = $pdo->prepare($sql);
$stmt->execute($parametri);
$pieteikumi = $stmt->fetchAll();

$irFiltri = $meklet !== '' || $filtrsStatuss !== '' || $no !== '' || $lidz !== '';
?>
<!DOCTYPE html>
<html lang="lv">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Administrācija — Pieteikumi</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="admin-apvalks">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
      <h2 style="margin: 0;">Pieteikumi (<?= count($pieteikumi) ?>)</h2>
      <div style="display: flex; gap: 10px;">
        <a href="atgadinajumi.php" class="nav-poga-admin"
           style="<?= $atgSkaits > 0 ? 'color: #c53030; border-color: #feb2b2; background: #fff5f5;' : '' ?>">
          🔔 Atgādinājumi<?= $atgSkaits > 0 ? ' (' . $atgSkaits . ')' : '' ?>
        </a>
        <a href="index.html" class="nav-poga-admin">← Uz formu</a>
        <a href="logout.php" class="nav-poga-admin">Iziet</a>
      </div>
    </div>

    <!-- Meklēšanas un filtru josla -->
    <form method="get" action="admin.php"
          style="max-width: none; margin: 0 0 20px; padding: 15px; background: #f8f7fc; border-radius: 12px; box-shadow: none; display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end;">

      <div>
        <label style="margin: 0 0 4px; font-size: 12px;">Meklēt (autors vai grāmata)</label>
        <input type="text" name="meklet" value="<?= htmlspecialchars($meklet) ?>"
               placeholder="Meklēt..." style="width: 220px;">
      </div>

      <div>
        <label style="margin: 0 0 4px; font-size: 12px;">Statuss</label>
        <select name="statuss" style="width: auto;">
          <option value="">Visi statusi</option>
          <?php foreach ($statusi as $s): ?>
            <option value="<?= htmlspecialchars($s) ?>" <?= $filtrsStatuss === $s ? 'selected' : '' ?>>
              <?= htmlspecialchars($s) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label style="margin: 0 0 4px; font-size: 12px;">Iesniegts no</label>
        <input type="date" name="no" value="<?= htmlspecialchars($no) ?>" style="width: auto;">
      </div>

      <div>
        <label style="margin: 0 0 4px; font-size: 12px;">Iesniegts līdz</label>
        <input type="date" name="lidz" value="<?= htmlspecialchars($lidz) ?>" style="width: auto;">
      </div>

      <div>
        <label style="margin: 0 0 4px; font-size: 12px;">Kārtot pēc datuma</label>
        <select name="kartot" style="width: auto;">
          <option value="jaunakie" <?= $kartot === 'DESC' ? 'selected' : '' ?>>Jaunākie pirmie</option>
          <option value="vecakie" <?= $kartot === 'ASC' ? 'selected' : '' ?>>Vecākie pirmie</option>
        </select>
      </div>

      <div style="display: flex; gap: 8px;">
        <button type="submit" style="width: auto; margin: 0; padding: 10px 20px;">Meklēt</button>
        <?php if ($irFiltri): ?>
          <a href="admin.php" class="nav-poga-admin">Notīrīt</a>
        <?php endif; ?>
      </div>
    </form>

    <?php if (empty($pieteikumi)): ?>
      <p><?= $irFiltri ? 'Pēc šiem kritērijiem nekas netika atrasts.' : 'Pagaidām nav neviena pieteikuma.' ?></p>
    <?php else: ?>
      <table class="admin-tabula">
        <thead>
          <tr>
            <th>Nr.</th>
            <th>Autors</th>
            <th>Kontakti</th>
            <th>Grāmata</th>
            <th>Žanrs</th>
            <th>Lpp.</th>
            <th>Tirāža</th>
            <th>Izdošana</th>
            <th>Saņemts</th>
            <th>Statuss</th>
            <th>Manuskripts</th>
            <th>Darbības</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pieteikumi as $p): ?>
            <tr>
              <td>
                <a href="pieteikums.php?id=<?= $p['id'] ?>" class="lejupieladet">
                  <?= htmlspecialchars($p['pieteikuma_nr']) ?>
                </a>
              </td>
              <td><?= htmlspecialchars($p['vards'] . ' ' . $p['uzvards']) ?></td>
              <td>
                <?= htmlspecialchars($p['epasts']) ?><br>
                <small><?= htmlspecialchars($p['talrunis']) ?></small>
              </td>
              <td><?= htmlspecialchars($p['nosaukums']) ?></td>
              <td><?= htmlspecialchars($p['zanrs']) ?></td>
              <td><?= htmlspecialchars($p['lappusu_skaits']) ?></td>
              <td><?= htmlspecialchars($p['tiraza']) ?></td>
              <td><?= htmlspecialchars($p['izdosanas_laiks']) ?></td>
              <td><?= htmlspecialchars($p['sanemts_datums']) ?></td>
              <td>
                <form action="statuss.php" method="post"
                      style="max-width: none; margin: 0; padding: 0; background: none; box-shadow: none; border-radius: 0;">
                  <input type="hidden" name="id" value="<?= $p['id'] ?>">
                  <select name="statuss" onchange="this.form.submit()"
                          style="width: auto; padding: 6px 8px; font-size: 12px;">
                    <?php foreach ($statusi as $s): ?>
                      <option value="<?= htmlspecialchars($s) ?>" <?= $p['statuss'] === $s ? 'selected' : '' ?>>
                        <?= htmlspecialchars($s) ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                </form>
              </td>
              <td>
                <a href="lejupielade.php?id=<?= $p['id'] ?>" class="lejupieladet">
                  Lejupielādēt
                </a>
              </td>
              <td style="white-space: nowrap;">
                <a href="pieteikums.php?id=<?= $p['id'] ?>" class="lejupieladet">Atvērt</a>
                &nbsp;
                <a href="dzest.php?id=<?= $p['id'] ?>"
                   class="dzest-poga"
                   onclick="return confirm('Vai tiešām dzēst pieteikumu no <?= htmlspecialchars($p['vards'] . ' ' . $p['uzvards']) ?>?');">
                  Dzēst
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</body>
</html>