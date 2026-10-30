<?php
require 'auth.php';
require 'db.php';
require 'atgadinajumi_lib.php';

$dienas = ATGADINAJUMS_DIENAS;
if (isset($_GET['dienas']) && ctype_digit($_GET['dienas']) && (int)$_GET['dienas'] <= 365) {
    $dienas = (int)$_GET['dienas'];
}

$neizskatiti = neizskatitiPieteikumi($pdo, $dienas);
$termini = sazinasTermini($pdo);
?>
<!DOCTYPE html>
<html lang="lv">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Atgādinājumi</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="admin-apvalks" style="max-width: 900px;">

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <h2 style="margin: 0;">Atgādinājumi</h2>
      <div style="display: flex; gap: 10px;">
        <a href="admin.php" class="nav-poga-admin">← Uz sarakstu</a>
        <a href="logout.php" class="nav-poga-admin">Iziet</a>
      </div>
    </div>

    <!-- 1. Neizskatīti pieteikumi -->
    <h3 style="color: #3b2f63; margin-top: 0;">Neizskatīti pieteikumi (<?= count($neizskatiti) ?>)</h3>

    <form method="get" action="atgadinajumi.php"
          style="max-width: none; margin: 0 0 15px; padding: 12px 15px; background: #f8f7fc; border-radius: 12px; box-shadow: none; display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
      <div>
        <label style="margin: 0 0 4px; font-size: 12px;">Statusā "Saņemts" ilgāk par (dienas)</label>
        <input type="number" name="dienas" min="0" max="365" value="<?= $dienas ?>" style="width: 120px;">
      </div>
      <button type="submit" style="width: auto; margin: 0; padding: 10px 20px;">Atjaunot</button>
    </form>

    <?php if (!$neizskatiti): ?>
      <p>Nav neizskatītu pieteikumu, kas ilgāk par <?= $dienas ?> dienām gaida statusā "Saņemts". </p>
    <?php else: ?>
      <?php foreach ($neizskatiti as $p): ?>
        <?php $gaida = (new DateTime($p['statuss_mainits']))->setTime(0, 0)->diff(new DateTime('today'))->days; ?>
        <div style="border: 1px solid #e4e0f2; border-left: 4px solid #dd6b20; border-radius: 12px; padding: 12px 18px; margin-bottom: 10px;">
          <a href="pieteikums.php?id=<?= $p['id'] ?>" class="lejupieladet">
            <?= htmlspecialchars($p['pieteikuma_nr']) ?>
          </a>
          · <?= htmlspecialchars($p['vards'] . ' ' . $p['uzvards']) ?>
          · „<?= htmlspecialchars($p['nosaukums']) ?>”
          <div style="font-size: 13px; color: #c05621; margin-top: 4px;">
            Gaida izskatīšanu: <?= $gaida ?> d. (kopš <?= date('d.m.Y', strtotime($p['statuss_mainits'])) ?>)
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <!-- 2. Pienācis saziņas datums -->
    <h3 style="color: #3b2f63; margin-top: 30px;">Pienācis saziņas datums (<?= count($termini) ?>)</h3>

    <?php if (!$termini): ?>
      <p>Nav pieteikumu, kuriem būtu pienācis plānotās saziņas datums.</p>
    <?php else: ?>
      <?php foreach ($termini as $p): ?>
        <?php $kave = (new DateTime($p['nakama_darbiba_datums']))->diff(new DateTime('today'))->days; ?>
        <div style="border: 1px solid #e4e0f2; border-left: 4px solid #c53030; border-radius: 12px; padding: 12px 18px; margin-bottom: 10px;">
          <a href="pieteikums.php?id=<?= $p['id'] ?>" class="lejupieladet">
            <?= htmlspecialchars($p['pieteikuma_nr']) ?>
          </a>
          · <?= htmlspecialchars($p['vards'] . ' ' . $p['uzvards']) ?>
          · „<?= htmlspecialchars($p['nosaukums']) ?>”
          · <small><?= htmlspecialchars($p['statuss']) ?></small>
          <div style="font-size: 13px; color: #c53030; margin-top: 4px;">
            <strong>Nākamā darbība:</strong>
            <?= htmlspecialchars($p['nakama_darbiba'] ?? '') ?>
            (termiņš <?= date('d.m.Y', strtotime($p['nakama_darbiba_datums'])) ?>,
            <?= $kave === 0 ? 'šodien' : 'kavējums ' . $kave . ' d.' ?>)
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

  </div>
</body>
</html>