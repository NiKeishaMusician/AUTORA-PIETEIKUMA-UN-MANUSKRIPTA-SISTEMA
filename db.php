<?php
// Latvijas laika josla (PHP)
date_default_timezone_set('Europe/Riga');

$pdo = new PDO(
    'mysql:host=127.0.0.1;dbname=izdevnieciba;charset=utf8mb4',
    'root',
    '',
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);

// Tā pati laika josla datubāzei (lai NOW() un saņemšanas datums sakristu)
$nobide = (new DateTime('now', new DateTimeZone('Europe/Riga')))->format('P');
$pdo->exec("SET time_zone = '$nobide'");