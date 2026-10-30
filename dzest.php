<?php
require 'auth.php';
require 'db.php';

$id = $_GET['id'] ?? null;

if (!$id || !ctype_digit($id)) {
    header('Location: admin.php');
    exit;
}

$stmt = $pdo->prepare('SELECT faila_cels FROM pieteikumi WHERE id = ?');
$stmt->execute([$id]);
$pieteikums = $stmt->fetch();

if ($pieteikums) {
    // Vecie pieteikumi: izdzēšam failu no diska, ja tas vēl eksistē
    $uploadsMape = realpath(__DIR__ . '/uploads');
    $fails = $uploadsMape ? realpath(__DIR__ . '/' . $pieteikums['faila_cels']) : false;
    if ($fails !== false
        && strpos($fails, $uploadsMape . DIRECTORY_SEPARATOR) === 0
        && is_file($fails)) {
        unlink($fails);
    }

    // Pieteikuma dzēšana izdzēš arī manuskriptu (tabula faili) un saziņas piezīmes
    $pdo->prepare('DELETE FROM pieteikumi WHERE id = ?')->execute([$id]);
}

header('Location: admin.php');
exit;