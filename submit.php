<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.html');
    exit;
}

$kludas = [];

// 1. Teksta lauki
$lauki = ['vards', 'uzvards', 'epasts', 'talrunis', 'nosaukums',
          'zanrs', 'apraksts', 'lappusu_skaits', 'tiraza', 'izdosanas_laiks'];
$dati = [];
foreach ($lauki as $lauks) {
    $dati[$lauks] = trim($_POST[$lauks] ?? '');
    if ($dati[$lauks] === '') {
        $kludas[] = "Lauks '$lauks' ir jāaizpilda.";
    }
}

if (!filter_var($dati['epasts'], FILTER_VALIDATE_EMAIL)) {
    $kludas[] = 'Nederīgs e-pasts.';
}
if (!ctype_digit($dati['lappusu_skaits']) || (int)$dati['lappusu_skaits'] < 1) {
    $kludas[] = 'Lappušu skaitam jābūt pozitīvam skaitlim.';
}
if (!ctype_digit($dati['tiraza']) || (int)$dati['tiraza'] < 1) {
    $kludas[] = 'Tirāžai jābūt pozitīvam skaitlim.';
}

// 2. Fails
$fails = $_FILES['manuskripts'] ?? null;
$atlautie = ['pdf', 'doc', 'docx'];
$maksBaiti = 10 * 1024 * 1024;

if (!$fails || $fails['error'] !== UPLOAD_ERR_OK) {
    $kludas[] = 'Manuskripta augšupielāde neizdevās.';
} else {
    $paplasinajums = strtolower(pathinfo($fails['name'], PATHINFO_EXTENSION));
    if (!in_array($paplasinajums, $atlautie, true)) {
        $kludas[] = 'Atļauti tikai PDF, DOC un DOCX faili.';
    }
    if ($fails['size'] > $maksBaiti) {
        $kludas[] = 'Fails ir lielāks par 10 MB.';
    }
}

// 3. Ja ir kļūdas, parādām tās
if ($kludas) {
    echo '<h3>Pieteikums netika nosūtīts:</h3><ul>';
    foreach ($kludas as $k) {
        echo '<li>' . htmlspecialchars($k) . '</li>';
    }
    echo '</ul><a href="index.html">Atpakaļ uz formu</a>';
    exit;
}

// 4. Saglabājam failu ar unikālu nosaukumu
$jaunsNosaukums = bin2hex(random_bytes(8)) . '.' . $paplasinajums;
$cels = 'uploads/' . $jaunsNosaukums;

if (!move_uploaded_file($fails['tmp_name'], __DIR__ . '/' . $cels)) {
    exit('Neizdevās saglabāt failu.');
}

// 5. Ierakstām datubāzē
$sql = "INSERT INTO pieteikumi
        (vards, uzvards, epasts, talrunis, nosaukums, zanrs, apraksts,
         lappusu_skaits, tiraza, izdosanas_laiks, faila_nosaukums, faila_cels)
        VALUES
        (:vards, :uzvards, :epasts, :talrunis, :nosaukums, :zanrs, :apraksts,
         :lappusu_skaits, :tiraza, :izdosanas_laiks, :faila_nosaukums, :faila_cels)";

$stmt = $pdo->prepare($sql);
$stmt->execute($dati + [
    'faila_nosaukums' => $fails['name'],
    'faila_cels'      => $cels,
]);

// 6. Pieteikuma numurs no ieraksta id, piemēram, PIET-2026-0001
$id = $pdo->lastInsertId();
$nr = 'PIET-' . date('Y') . '-' . str_pad($id, 4, '0', STR_PAD_LEFT);
$pdo->prepare('UPDATE pieteikumi SET pieteikuma_nr = ? WHERE id = ?')->execute([$nr, $id]);

header('Location: paldies.php?nr=' . urlencode($nr));
exit;