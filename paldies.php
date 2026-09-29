<?php $nr = $_GET['nr'] ?? ''; ?>
<!DOCTYPE html>
<html lang="lv">
<head>
  <meta charset="UTF-8">
  <title>Paldies!</title>
  <link rel="stylesheet" href="style.css">
</head>
<body style="text-align: center; padding: 60px;">
  <h2>Paldies! Jūsu pieteikums ir saņemts.</h2>
  <p>Pieteikuma numurs: <strong><?= htmlspecialchars($nr) ?></strong></p>
  <a href="index.html">Iesniegt jaunu pieteikumu</a>
</body>
</html>