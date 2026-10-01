<?php
require 'db.php';

$pieteikumi = $pdo->query('SELECT * FROM pieteikumi ORDER BY id DESC')->fetchAll();
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
      <h2 style="margin: 0;">Saņemtie pieteikumi (<?= count($pieteikumi) ?>)</h2>
      <a href="index.html" class="nav-poga-admin">← Atpakaļ uz formu</a>
    </div>

    <?php if (empty($pieteikumi)): ?>
      <p>Pagaidām nav neviena pieteikuma.</p>
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
            <th>Manuskripts</th>
            <th>Darbības</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pieteikumi as $p): ?>
            <tr>
              <td><?= htmlspecialchars($p['pieteikuma_nr']) ?></td>
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
                <a href="<?= htmlspecialchars($p['faila_cels']) ?>" target="_blank" class="lejupieladet">
                  Lejupielādēt
                </a>
              </td>
              <td>
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