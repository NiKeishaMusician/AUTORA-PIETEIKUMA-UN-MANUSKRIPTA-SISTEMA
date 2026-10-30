<?php
require 'auth.php';
require 'db.php';

$id = $_GET['id'] ?? '';
if (!ctype_digit($id)) {
    http_response_code(400);
    exit('Nederīgs pieprasījums.');
}

$stmt = $pdo->prepare(
    'SELECT p.faila_nosaukums, p.faila_cels, f.saturs, octet_length(f.saturs) AS garums
     FROM pieteikumi p
     LEFT JOIN faili f ON f.pieteikuma_id = p.id
     WHERE p.id = ?'
);
$stmt->execute([$id]);
$stmt->bindColumn('saturs', $saturs, PDO::PARAM_LOB);
$stmt->bindColumn('faila_nosaukums', $nosaukums);
$stmt->bindColumn('faila_cels', $cels);
$stmt->bindColumn('garums', $garums);

if (!$stmt->fetch(PDO::FETCH_BOUND)) {
    http_response_code(404);
    exit('Pieteikums nav atrasts.');
}

$nosaukums = $nosaukums ?: 'manuskripts';
$paplasinajums = strtolower(pathinfo($nosaukums, PATHINFO_EXTENSION));

$tipi = [
    'pdf'  => 'application/pdf',
    'doc'  => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
];
$tips = $tipi[$paplasinajums] ?? 'application/octet-stream';
$nosaukumsAscii = preg_replace('/[^A-Za-z0-9._-]/', '_', $nosaukums);

function nosutitGalvenes($tips, $nosaukums, $nosaukumsAscii, $garums = null) {
    header('Content-Type: ' . $tips);
    header('Content-Disposition: attachment; filename="' . $nosaukumsAscii . '"; filename*=UTF-8\'\'' . rawurlencode($nosaukums));
    if ($garums !== null) {
        header('Content-Length: ' . $garums);
    }
    header('X-Content-Type-Options: nosniff');
}

session_write_close();

// 1. Jaunie pieteikumi: fails ir datubāzē
if ($saturs !== null && $saturs !== false) {
    nosutitGalvenes($tips, $nosaukums, $nosaukumsAscii, $garums);
    if (is_resource($saturs)) {
        fpassthru($saturs);
    } else {
        echo $saturs;
    }
    exit;
}

// 2. Vecie pieteikumi: fails varētu būt vēl uz diska
$uploadsMape = realpath(__DIR__ . '/uploads');
$fails = $uploadsMape ? realpath(__DIR__ . '/' . $cels) : false;

if ($fails !== false
    && strpos($fails, $uploadsMape . DIRECTORY_SEPARATOR) === 0
    && is_file($fails)) {
    nosutitGalvenes($tips, $nosaukums, $nosaukumsAscii, filesize($fails));
    readfile($fails);
    exit;
}

http_response_code(404);
exit('Šis fails vairs nav pieejams (vecs pieteikums, kura fails netika saglabāts datubāzē).');