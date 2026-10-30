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

    // Izveido tabulu 'pieteikumi'
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
        izveidots TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );");

    // Izveido noklusējuma administratora lietotāju (lietotājvārds: admin, parole: admin123)
    $defaultAdmin = 'admin';
    $defaultHash = password_hash('admin123', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO lietotaji (lietotajvards, paroles_hash) VALUES (?, ?) ON CONFLICT (lietotajvards) DO NOTHING");
    $stmt->execute([$defaultAdmin, $defaultHash]);

    echo "<h2>Tabulas un admin lietotājs veiksmīgi izveidoti!</h2>";
    echo "<p>Admin pieteikšanās: lietotājvārds: <b>admin</b>, parole: <b>admin123</b></p>";
} catch (PDOException $e) {
    echo "Kļūda veidojot tabulas: " . $e->getMessage();
}
?>