<?php
date_default_timezone_set('Europe/Riga');

$host = getenv('DB_HOST');
$db   = getenv('DB_NAME');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');

try {
    $pdo = new PDO("pgsql:host=$host;dbname=$db", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec("SET TIME ZONE 'Europe/Riga'");

    // Tabulu un konta sagatavošana notiek tikai vienreiz uz servera palaišanu
    $karogs = sys_get_temp_dir() . '/db_gatavs_v1.flag';

    if (!file_exists($karogs)) {

        // 1. Tabulas
        $pdo->exec("CREATE TABLE IF NOT EXISTS lietotaji (
            id SERIAL PRIMARY KEY,
            lietotajvards VARCHAR(50) UNIQUE NOT NULL,
            paroles_hash VARCHAR(255) NOT NULL,
            izveidots TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );");

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

        // 2. Trūkstošās kolonnas vecākās tabulās
        $pdo->exec("ALTER TABLE pieteikumi ADD COLUMN IF NOT EXISTS statuss VARCHAR(50) NOT NULL DEFAULT 'Saņemts';");
        $pdo->exec("ALTER TABLE pieteikumi ADD COLUMN IF NOT EXISTS statuss_mainits TIMESTAMP DEFAULT CURRENT_TIMESTAMP;");
        $pdo->exec("ALTER TABLE pieteikumi ADD COLUMN IF NOT EXISTS pieteikuma_nr VARCHAR(20);");
        $pdo->exec("ALTER TABLE pieteikumi ADD COLUMN IF NOT EXISTS uzvards VARCHAR(100);");
        $pdo->exec("ALTER TABLE pieteikumi ADD COLUMN IF NOT EXISTS lappusu_skaits INT DEFAULT 0;");
        $pdo->exec("ALTER TABLE pieteikumi ADD COLUMN IF NOT EXISTS tiraza INT DEFAULT 0;");
        $pdo->exec("ALTER TABLE pieteikumi ADD COLUMN IF NOT EXISTS izdosanas_laiks DATE;");
        $pdo->exec("ALTER TABLE pieteikumi ADD COLUMN IF NOT EXISTS faila_nosaukums VARCHAR(255);");
        $pdo->exec("ALTER TABLE pieteikumi ADD COLUMN IF NOT EXISTS sanemts_datums TIMESTAMP DEFAULT CURRENT_TIMESTAMP;");

        $pdo->exec("ALTER TABLE sazina ADD COLUMN IF NOT EXISTS nakama_darbiba VARCHAR(255);");
        $pdo->exec("ALTER TABLE sazina ADD COLUMN IF NOT EXISTS nakama_darbiba_datums DATE;");
        $pdo->exec("ALTER TABLE sazina ADD COLUMN IF NOT EXISTS datums TIMESTAMP DEFAULT CURRENT_TIMESTAMP;");
        $pdo->exec("ALTER TABLE sazina ADD COLUMN IF NOT EXISTS veids VARCHAR(30);");
        $pdo->exec("ALTER TABLE sazina ADD COLUMN IF NOT EXISTS kopsavilkums TEXT;");

        // 3. Administratora konts: parole tiek ņemta no vides mainīgā ADMIN_PASS
        $adminParole = getenv('ADMIN_PASS');

        $st = $pdo->prepare('SELECT paroles_hash FROM lietotaji WHERE lietotajvards = ?');
        $st->execute(['admin']);
        $esosaisHash = $st->fetchColumn();

        if ($adminParole === false || strlen($adminParole) < 12) {
            // Nav derīgas paroles: izdzēšam vecā nedrošā konta paroli (admin123)
            if ($esosaisHash !== false && password_verify('admin123', $esosaisHash)) {
                $pdo->prepare('DELETE FROM lietotaji WHERE lietotajvards = ?')->execute(['admin']);
            }
        } else {
            $jaunsHash = password_hash($adminParole, PASSWORD_DEFAULT);
            if ($esosaisHash === false) {
                $pdo->prepare('INSERT INTO lietotaji (lietotajvards, paroles_hash) VALUES (?, ?)')
                    ->execute(['admin', $jaunsHash]);
            } elseif (!password_verify($adminParole, $esosaisHash)) {
                $pdo->prepare('UPDATE lietotaji SET paroles_hash = ? WHERE lietotajvards = ?')
                    ->execute([$jaunsHash, 'admin']);
            }
        }

        @file_put_contents($karogs, date('c'));
    }

} catch (PDOException $e) {
    error_log('Datubāzes kļūda: ' . $e->getMessage());
    http_response_code(500);
    exit('Neizdevās pieslēgties datubāzei. Lūdzu, mēģiniet vēlāk.');
}