<?php
require_once 'db.php';

try {
    // Izveido tabulu 'lietotaji'
    $pdo->exec("CREATE TABLE IF NOT EXISTS lietotaji (
        id SERIAL PRIMARY KEY,
        lietotajvards VARCHAR(50) UNIQUE NOT NULL,
        paroles_hash VARCHAR(255) NOT NULL,
        izveidots TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );");

    // Izveido tabulu 'pieteikumi' ar visām nepieciešamajām kolonnām
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

    // Ja tabula 'pieteikumi' jau bija izveidota bez šīm kolonnām, pievienojam tās klāt:
    $pdo->exec("ALTER TABLE pieteikumi ADD COLUMN IF NOT EXISTS statuss VARCHAR(50) DEFAULT 'Saņemts';");
    $pdo->exec("ALTER TABLE pieteikumi ADD COLUMN IF NOT EXISTS statuss_mainits TIMESTAMP DEFAULT CURRENT_TIMESTAMP;");

    // Izveido noklusējuma administratora lietotāju
    $defaultAdmin = 'admin';
    $defaultHash = password_hash('admin123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO lietotaji (lietotajvards, paroles_hash) VALUES (?, ?) ON CONFLICT (lietotajvards) DO NOTHING");
    $stmt->execute([$defaultAdmin, $defaultHash]);

    echo "<h2>Datubāzes struktūra veiksmīgi atjaunināta!</h2>";
} catch (PDOException $e) {
    echo "Kļūda: " . $e->getMessage();
}
?>