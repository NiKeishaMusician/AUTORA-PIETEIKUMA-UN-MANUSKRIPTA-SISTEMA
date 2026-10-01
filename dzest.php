<?php
require 'db.php';

$id = $_GET['id'] ?? null;

if (!$id || !ctype_digit($id)) {
    header('Location: admin.php');
    exit;
}

// Vispirms atrodam ierakstu, lai zinātu, kuru failu dzēst
$stmt = $pdo->prepare('SELECT faila_cels FROM pieteikumi WHERE id = ?');
$stmt->execute([$id]);
$pieteikums = $stmt->fetch();

if ($pieteikums) {
    // Izdzēšam failu no servera, ja tas eksistē
    $failaCels = __DIR__ . '/' . $pieteikums['faila_cels'];
    if (file_exists($failaCels)) {
        unlink($failaCels);
    }

    // Izdzēšam ierakstu no datubāzes
    $pdo->prepare('DELETE FROM pieteikumi WHERE id = ?')->execute([$id]);
}

header('Location: admin.php');
exit;