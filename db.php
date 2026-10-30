<?php
$host = getenv('DB_HOST');
$db   = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Tabula 'lietotaji'
    $pdo->exec("CREATE TABLE IF NOT EXISTS lietotaji (
        id SERIAL PRIMARY KEY,
        lietotajvards VARCHAR(50) UNIQUE NOT NULL,
        paroles_hash VARCHAR(255) NOT NULL,
        izveidots TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );");

    // 2. Tabula 'pieteikumi' (pielāgota precīzi tavai struktūrai)
    $pdo->exec("CREATE TABLE IF NOT EXISTS pieteikumi (
        id SERIAL PRIMARY KEY,
        pieteikuma_nr VARCHAR(20) UNIQUE,
        vards VARCHAR(100) NOT NULL,
        uzvards VARCHAR(100) NOT NULL,
        epasts VARCHAR(150) NOT NULL,
        talrunis VARCHAR(30) NOT NULL,
        nosaukums VARCHAR(200) NOT NULL,
        zanrs VARCHAR(100) NOT NULL,
        apraksts TEXT NOT NULL,
        lappusu_skaits INT NOT NULL DEFAULT 0,
        tiraza INT NOT NULL DEFAULT 0,
        izdosanas_laiks DATE DEFAULT CURRENT_DATE,
        faila_nosaukums VARCHAR(255) DEFAULT '',
        faila_cels VARCHAR(255) NOT NULL,
        sanemts_datums TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        statuss VARCHAR(50) NOT NULL DEFAULT 'Saņemts',
        statuss_mainits TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );");

    // 3. Tabula 'sazina'
    $pdo->exec("CREATE TABLE IF NOT EXISTS sazina (
        id SERIAL PRIMARY KEY,
        pieteikuma_id INT REFERENCES pieteikumi(id) ON DELETE CASCADE,
        datums TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        veids VARCHAR(30) NOT NULL,
        kopsavilkums TEXT NOT NULL,
        nakama_darbiba VARCHAR(255),
        nakama_darbiba_datums DATE,
        izveidots TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );");

    // Drošības nolūkā izveidojam noklusējuma admin lietotāju, ja tāda vēl nav
    $defaultAdmin = 'admin';
    $defaultHash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO lietotaji (lietotajvards, paroles_hash) VALUES (?, ?) ON CONFLICT (lietotajvards) DO NOTHING");
    $stmt->execute([$defaultAdmin, $defaultHash]);

} catch (\PDOException $e) {
    echo "Kļūda pieslēdzoties datubāzei: " . $e->getMessage();
}
?>