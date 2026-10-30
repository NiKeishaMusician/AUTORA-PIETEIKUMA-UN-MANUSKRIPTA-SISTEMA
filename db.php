<?php
$host = getenv('DB_HOST');
$db   = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Automātiski izveidojam tabulas un trūkstošās kolonnas
    $pdo->exec("CREATE TABLE IF NOT EXISTS lietotaji (
        id SERIAL PRIMARY KEY,
        lietotajvards VARCHAR(50) UNIQUE NOT NULL,
        paroles_hash VARCHAR(255) NOT NULL,
        izveidots TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );");

    $pdo->exec("CREATE TABLE IF NOT EXISTS pieteikumi (
        id SERIAL PRIMARY KEY,
        vards VARCHAR(100) NOT NULL,
        epasts VARCHAR(100) NOT NULL,
        talrunis VARCHAR(30),
        nosaukums VARCHAR(255) NOT NULL,
        zanrs VARCHAR(100),
        apraksts TEXT,
        faila_cels VARCHAR(255),
        statusa_kods VARCHAR(20) DEFAULT 'jauns',
        statuss VARCHAR(50) DEFAULT 'Saņemts',
        statuss_mainits TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        izveidots TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );");

    // Drošības labad papildus pievienojam kolonnas, ja tabula tika izveidota agrāk
    $pdo->exec("ALTER TABLE pieteikumi ADD COLUMN IF NOT EXISTS statuss VARCHAR(50) DEFAULT 'Saņemts';");
    $pdo->exec("ALTER TABLE pieteikumi ADD COLUMN IF NOT EXISTS statuss_mainits TIMESTAMP DEFAULT CURRENT_TIMESTAMP;");

} catch (\PDOException $e) {
    echo "Kļūda pieslēdzoties datubāzei: " . $e->getMessage();
}
?>