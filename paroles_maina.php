<?php
require 'db.php';

$lietotajvards = 'admin';
$parole = 'Gramatas2026!';

if ($parole === 'Gramatas2026!' || strlen($parole) < 12) {
    exit('Ieraksti savu jauno paroli failā (vismaz 12 zīmes).');
}

$hash = password_hash($parole, PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    'INSERT INTO lietotaji (lietotajvards, paroles_hash) VALUES (?, ?)
     ON DUPLICATE KEY UPDATE paroles_hash = ?'
);
$stmt->execute([$lietotajvards, $hash, $hash]);

echo 'Parole nomainīta. Tagad IZDZĒS failu paroles_maina.php!';