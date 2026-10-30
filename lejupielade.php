<?php
require 'auth.php';
require 'db.php';

$id = $_GET['id'] ?? '';
if (!ctype_digit($id)) {
    http_response_code(400);
    exit('Nederīgs pieprasījums.');
}

$stmt = $pdo->prepare('SELECT faila_cels, faila_nosaukums FROM pieteikumi WHERE id = ?');
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) {
    http_response_code(404);
    exit('Pieteikums nav atrasts.');
}

// Pārliecināmies, ka fails tiešām atrodas mapē uploads
$uploadsMape = realpath(__DIR__ . '/uploads');
$fails = realpath(__DIR__ . '/' . $p['faila_cels']);

if ($fails === false
    || strpos($fails, $uploadsMape . DIRECTORY_SEPARATOR) !== 0
    || !is_file($fails)) {
    http_response_code(404);
    exit('Fails nav atrasts.');
}

$tipi = [
    'pdf'  => 'application/pdf',
    'doc'  => 'application/msword',
    'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
];
$paplasinajums = strtolower(pathinfo($fails, PATHINFO_EXTENSION));
$tips = $tipi[$paplasinajums] ?? 'application/octet-stream';

// Faila nosaukums lejupielādei (ar latviešu burtiem)
$nosaukums = $p['faila_nosaukums'];
$nosaukumsAscii = preg_replace('/[^A-Za-z0-9._-]/', '_', $nosaukums);

session_write_close();

header('Content-Type: ' . $tips);
header('Content-Disposition: attachment; filename="' . $nosaukumsAscii . '"; filename*=UTF-8\'\'' . rawurlencode($nosaukums));
header('Content-Length: ' . filesize($fails));
header('X-Content-Type-Options: nosniff');
readfile($fails);
exit;