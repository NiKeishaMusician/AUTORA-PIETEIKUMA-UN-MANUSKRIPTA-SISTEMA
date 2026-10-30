<?php
require 'auth.php';
require 'db.php';

$atlautie = [
    'Saņemts',
    'Izskatīšanā',
    'Nepieciešama papildu informācija',
    'Piedāvājums sagatavots',
    'Sadarbība apstiprināta',
    'Noraidīts',
    'Pabeigts',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $statuss = $_POST['statuss'] ?? '';

    if (ctype_digit($id) && in_array($statuss, $atlautie, true)) {
        $stmt = $pdo->prepare('UPDATE pieteikumi SET statuss = ?, statuss_mainits = NOW() WHERE id = ?');
        $stmt->execute([$statuss, $id]);
    }
}

header('Location: admin.php');
exit;