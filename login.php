<?php
session_start();
require 'db.php';

if (!empty($_SESSION['admin_id'])) {
    header('Location: admin.php');
    exit;
}

$kluda = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lietotajvards = trim($_POST['lietotajvards'] ?? '');
    $parole = $_POST['parole'] ?? '';

    $stmt = $pdo->prepare('SELECT id, paroles_hash FROM lietotaji WHERE lietotajvards = ?');
    $stmt->execute([$lietotajvards]);
    $lietotajs = $stmt->fetch();

    if ($lietotajs && password_verify($parole, $lietotajs['paroles_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $lietotajs['id'];
        header('Location: admin.php');
        exit;
    }

    $kluda = 'Nepareizs lietotājvārds vai parole.';
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pieteikšanās</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<form method="post" style="max-width: 420px; margin-top: 80px;">
  <h2>Administrācijas pieteikšanās</h2>
  <div id="kludas"><?= htmlspecialchars($kluda) ?></div>

  <label>Lietotājvārds</label>
  <input type="text" name="lietotajvards" required autofocus>

  <label>Parole</label>
  <input type="password" name="parole" required>

  <button type="submit">Pieteikties</button>
</form>
</body>
</html>