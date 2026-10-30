<?php
require 'auth.php';
require 'db.php';

$id = $_GET['id'] ?? '';
if (!ctype_digit($id)) {
    header('Location: admin.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM pieteikumi WHERE id = ?');
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) {
    header('Location: admin.php');
    exit;
}

$veidi = ['E-pasts', 'Telefons', 'Cita saziņa'];
$kludas = [];
$vertibas = [
    'datums'                => date('Y-m-d\TH:i'),
    'veids'                 => 'E-pasts',
    'kopsavilkums'          => '',
    'nakama_darbiba'        => '',
    'nakama_darbiba_datums' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $darbiba = $_POST['darbiba'] ?? '';

    if ($darbiba === 'pievienot') {
        foreach ($vertibas as $lauks => $noklusets) {
            $vertibas[$lauks] = trim($_POST[$lauks] ?? '');
        }

        $dt = DateTime::createFromFormat('Y-m-d\TH:i', $vertibas['datums']);
        if (!$dt) {
            $kludas[] = 'Norādi derīgu saziņas datumu un laiku.';
        }
        if (!in_array($vertibas['veids'], $veidi, true)) {
            $kludas[] = 'Izvēlies saziņas veidu.';
        }
        if ($vertibas['kopsavilkums'] === '') {
            $kludas[] = 'Ieraksti sarunas vai vēstules kopsavilkumu.';
        }
        if ($vertibas['nakama_darbiba_datums'] !== ''
            && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $vertibas['nakama_darbiba_datums'])) {
            $kludas[] = 'Nederīgs nākamās darbības datums.';
        }

        if (!$kludas) {
            $stmt = $pdo->prepare(
                'INSERT INTO sazina (pieteikuma_id, datums, veids, kopsavilkums, nakama_darbiba, nakama_darbiba_datums)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $p['id'],
                $dt->format('Y-m-d H:i:s'),
                $vertibas['veids'],
                $vertibas['kopsavilkums'],
                $vertibas['nakama_darbiba'] !== '' ? $vertibas['nakama_darbiba'] : null,
                $vertibas['nakama_darbiba_datums'] !== '' ? $vertibas['nakama_darbiba_datums'] : null,
            ]);
            header('Location: pieteikums.php?id=' . $p['id']);
            exit;
        }
    }

    if ($darbiba === 'dzest') {
        $piezimesId = $_POST['piezimes_id'] ?? '';
        if (ctype_digit($piezimesId)) {
            $pdo->prepare('DELETE FROM sazina WHERE id = ? AND pieteikuma_id = ?')
                ->execute([$piezimesId, $p['id']]);
        }
        header('Location: pieteikums.php?id=' . $p['id']);
        exit;
    }
}

$stmt = $pdo->prepare('SELECT * FROM sazina WHERE pieteikuma_id = ? ORDER BY datums DESC, id DESC');
$stmt->execute([$p['id']]);
$piezimes = $stmt->fetchAll();

$sodien = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="lv">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pieteikums <?= htmlspecialchars($p['pieteikuma_nr']) ?></title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="admin-apvalks" style="max-width: 900px;">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
      <h2 style="margin: 0;">Pieteikums <?= htmlspecialchars($p['pieteikuma_nr']) ?></h2>
      <div style="display: flex; gap: 10px;">
        <a href="admin.php" class="nav-poga-admin">← Uz sarakstu</a>
        <a href="logout.php" class="nav-poga-admin">Iziet</a>
      </div>
    </div>

    <!-- Pieteikuma informācija -->
    <div style="background: #f8f7fc; border-left: 4px solid #764ba2; border-radius: 12px; padding: 18px 22px; margin-bottom: 25px; line-height: 1.8; font-size: 14px;">
      <strong>Autors:</strong> <?= htmlspecialchars($p['vards'] . ' ' . $p['uzvards']) ?><br>
      <strong>E-pasts:</strong> <?= htmlspecialchars($p['epasts']) ?><br>
      <strong>Tālrunis:</strong> <?= htmlspecialchars($p['talrunis']) ?><br>
      <strong>Grāmata:</strong> <?= htmlspecialchars($p['nosaukums']) ?> (<?= htmlspecialchars($p['zanrs']) ?>)<br>
      <strong>Apraksts:</strong> <?= nl2br(htmlspecialchars($p['apraksts'])) ?><br>
      <strong>Lappuses:</strong> <?= htmlspecialchars($p['lappusu_skaits']) ?>
      &nbsp;|&nbsp; <strong>Tirāža:</strong> <?= htmlspecialchars($p['tiraza']) ?>
      &nbsp;|&nbsp; <strong>Vēlamais izdošanas laiks:</strong> <?= htmlspecialchars($p['izdosanas_laiks']) ?><br>
      <strong>Saņemts:</strong> <?= date('d.m.Y H:i', strtotime($p['sanemts_datums'])) ?><br>
      <strong>Statuss:</strong> <?= htmlspecialchars($p['statuss']) ?><br>
      <strong>Manuskripts:</strong>
      <a href="lejupielade.php?id=<?= $p['id'] ?>" class="lejupieladet">
        Lejupielādēt (<?= htmlspecialchars($p['faila_nosaukums']) ?>)
      </a>
    </div>

    <!-- Jauna piezīme -->
    <h3 style="color: #3b2f63; margin-top: 0;">Jauna saziņas piezīme</h3>

    <?php if ($kludas): ?>
      <div id="kludas">
        <?php foreach ($kludas as $k): ?>
          <?= htmlspecialchars($k) ?><br>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <form method="post"
          style="max-width: none; margin: 0 0 30px; padding: 0; background: none; box-shadow: none; border-radius: 0;">
      <input type="hidden" name="darbiba" value="pievienot">

      <div style="display: flex; gap: 15px; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 200px;">
          <label>Saziņas datums un laiks</label>
          <input type="datetime-local" name="datums" value="<?= htmlspecialchars($vertibas['datums']) ?>" required>
        </div>
        <div style="flex: 1; min-width: 200px;">
          <label>Saziņas veids</label>
          <select name="veids">
            <?php foreach ($veidi as $v): ?>
              <option <?= $vertibas['veids'] === $v ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <label>Sarunas vai vēstules kopsavilkums</label>
      <textarea name="kopsavilkums" rows="3" required><?= htmlspecialchars($vertibas['kopsavilkums']) ?></textarea>

      <div style="display: flex; gap: 15px; flex-wrap: wrap;">
        <div style="flex: 2; min-width: 200px;">
          <label>Nākamā plānotā darbība (nav obligāta)</label>
          <input type="text" name="nakama_darbiba" value="<?= htmlspecialchars($vertibas['nakama_darbiba']) ?>">
        </div>
        <div style="flex: 1; min-width: 160px;">
          <label>Nākamās darbības datums</label>
          <input type="date" name="nakama_darbiba_datums" value="<?= htmlspecialchars($vertibas['nakama_darbiba_datums']) ?>">
        </div>
      </div>

      <button type="submit" style="width: auto; padding: 12px 28px;">Saglabāt piezīmi</button>
    </form>

    <!-- Saziņas vēsture -->
    <h3 style="color: #3b2f63;">Saziņas vēsture (<?= count($piezimes) ?>)</h3>

    <?php if (!$piezimes): ?>
      <p>Šim pieteikumam vēl nav saziņas piezīmju.</p>
    <?php else: ?>
      <?php foreach ($piezimes as $z): ?>
        <div style="border: 1px solid #e4e0f2; border-radius: 12px; padding: 14px 18px; margin-bottom: 12px;">
          <div style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: #764ba2; font-weight: 600;">
            <span>
              <?= date('d.m.Y H:i', strtotime($z['datums'])) ?> · <?= htmlspecialchars($z['veids']) ?>
            </span>
            <form method="post"
                  style="max-width: none; margin: 0; padding: 0; background: none; box-shadow: none; border-radius: 0;"
                  onsubmit="return confirm('Vai tiešām dzēst šo piezīmi?');">
              <input type="hidden" name="darbiba" value="dzest">
              <input type="hidden" name="piezimes_id" value="<?= $z['id'] ?>">
              <button type="submit"
                      style="width: auto; margin: 0; padding: 0; background: none; color: #c53030; font-size: 13px; box-shadow: none;">
                Dzēst
              </button>
            </form>
          </div>

          <p style="margin: 8px 0;"><?= nl2br(htmlspecialchars($z['kopsavilkums'])) ?></p>

          <?php if ($z['nakama_darbiba'] || $z['nakama_darbiba_datums']): ?>
            <?php
              $steidzas = $z['nakama_darbiba_datums'] && $z['nakama_darbiba_datums'] <= $sodien;
            ?>
            <div style="font-size: 13px; color: <?= $steidzas ? '#c53030' : '#555' ?>;">
              <strong>Nākamā darbība:</strong>
              <?= htmlspecialchars($z['nakama_darbiba'] ?? '') ?>
              <?php if ($z['nakama_darbiba_datums']): ?>
                (līdz <?= date('d.m.Y', strtotime($z['nakama_darbiba_datums'])) ?>)
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

  </div>
</body>
</html>