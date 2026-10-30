<?php
date_default_timezone_set('Europe/Riga');

$pdo = new PDO(
    'mysql:host=sql209.infinityfree.com;dbname=if0_43311041_izdevnieciba;charset=utf8mb4',
    'if0_43311041',
    'Izdevnieciba2026',
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);

$nobide = (new DateTime('now', new DateTimeZone('Europe/Riga')))->format('P');
$pdo->exec("SET time_zone = '$nobide'");