<?php
session_start();

// Paldies lapu rāda tikai pēc īsta pieteikuma nosūtīšanas
if (empty($_SESSION['paldies_nr'])) {
    header('Location: index.html');
    exit;
}

$nr = $_SESSION['paldies_nr'];
?>
<!DOCTYPE html>
<html lang="lv">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Paldies!</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="nav-josla">
    <a href="admin.php" class="nav-poga">🛠 Administrācijas panelis</a>
  </div>

  <div class="paldies-karte">
    <div class="ikona">✓</div>
    <h2>Paldies! Jūsu pieteikums ir saņemts.</h2>
    <p class="apraksts">Mēs izskatīsim jūsu manuskriptu un sazināsimies ar jums tuvākajā laikā.</p>
    <div class="numurs-kaste">
      <span class="numurs-etikete">Pieteikuma numurs</span>
      <span class="numurs"><?= htmlspecialchars($nr) ?></span>
    </div>
    <a href="index.html" class="poga-atpakal">Iesniegt jaunu pieteikumu</a>
  </div>
</body>
</html>