<?php
session_start();
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

// 4. Atveram augšupielādēto failu lasīšanai
$saturs = fopen($fails['tmp_name'], 'rb');
if ($saturs === false) {
    exit('Neizdevās nolasīt failu.');
}

// 5. Pieteikums un manuskripts tiek saglabāti vienā darbībā
$tagad = date('Y-m-d H:i:s');

$sql = "INSERT INTO pieteikumi
        (vards, uzvards, epasts, talrunis, nosaukums, zanrs, apraksts,
         lappusu_skaits, tiraza, izdosanas_laiks, faila_nosaukums, faila_cels,
         sanemts_datums, statuss_mainits)
        VALUES
        (:vards, :uzvards, :epasts, :talrunis, :nosaukums, :zanrs, :apraksts,
         :lappusu_skaits, :tiraza, :izdosanas_laiks, :faila_nosaukums, :faila_cels,
         :sanemts_datums, :statuss_mainits)";

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare($sql);
    $stmt->execute($dati + [
        'faila_nosaukums' => $fails['name'],
        'faila_cels'      => 'datubaze',
        'sanemts_datums'  => $tagad,
        'statuss_mainits' => $tagad,
    ]);

    // Pieteikuma numurs no ieraksta id, piemēram, PIET-2026-0001
    $id = $pdo->lastInsertId();
    $nr = 'PIET-' . date('Y') . '-' . str_pad($id, 4, '0', STR_PAD_LEFT);
    $pdo->prepare('UPDATE pieteikumi SET pieteikuma_nr = ? WHERE id = ?')->execute([$nr, $id]);

    // Manuskripta saturs
    $stmt = $pdo->prepare('INSERT INTO faili (pieteikuma_id, saturs) VALUES (:id, :saturs)');
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->bindParam(':saturs', $saturs, PDO::PARAM_LOB);
    $stmt->execute();

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Pieteikuma saglabāšanas kļūda: ' . $e->getMessage());
    exit('Neizdevās saglabāt pieteikumu. Lūdzu, mēģiniet vēlreiz. <a href="index.html">Atpakaļ uz formu</a>');
}

fclose($saturs);

// 6. Atceramies numuru paldies lapai
$_SESSION['paldies_nr'] = $nr;
session_write_close();

// 7. E-pasts tiek sūtīts tikai tad, ja vidē ir SMTP_ENABLED=1
if (getenv('SMTP_ENABLED') === '1') {
    try {
        if (file_exists(__DIR__ . '/vendor/autoload.php') && file_exists(__DIR__ . '/epasts.php')) {
            require 'epasts.php';
            nosutitApstiprinajumu($dati['epasts'], $dati['vards'], $nr);
        }
    } catch (Throwable $e) {
        error_log('E-pasta kļūda: ' . $e->getMessage());
    }
}

header('Location: paldies.php');
exit;