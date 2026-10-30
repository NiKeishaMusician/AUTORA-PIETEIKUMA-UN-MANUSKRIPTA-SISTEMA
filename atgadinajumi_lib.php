<?php
// Cik dienas pieteikums var būt statusā "Saņemts", pirms parādās atgādinājums
const ATGADINAJUMS_DIENAS = 3;

// Šiem statusiem atgādinājumi nav vajadzīgi
const SLEGTIE_STATUSI = ['Noraidīts', 'Pabeigts'];

// Pieteikumi, kas ilgāk par $dienas dienām nav izskatīti
function neizskatitiPieteikumi(PDO $pdo, int $dienas): array
{
    $robeza = date('Y-m-d', strtotime("-$dienas days"));

    $stmt = $pdo->prepare(
        "SELECT * FROM pieteikumi
         WHERE statuss = 'Saņemts' AND DATE(statuss_mainits) <= ?
         ORDER BY statuss_mainits ASC"
    );
    $stmt->execute([$robeza]);
    return $stmt->fetchAll();
}

// Pieteikumi, kuriem pēdējās piezīmes nākamās darbības datums ir pienācis
function sazinasTermini(PDO $pdo): array
{
    $sodien = date('Y-m-d');
    $vietturi = implode(',', array_fill(0, count(SLEGTIE_STATUSI), '?'));

    $stmt = $pdo->prepare(
        "SELECT p.*, s.nakama_darbiba, s.nakama_darbiba_datums
         FROM pieteikumi p
         JOIN sazina s ON s.pieteikuma_id = p.id
         WHERE s.id = (SELECT MAX(id) FROM sazina WHERE pieteikuma_id = p.id)
           AND s.nakama_darbiba_datums IS NOT NULL
           AND s.nakama_darbiba_datums <= ?
           AND p.statuss NOT IN ($vietturi)
         ORDER BY s.nakama_darbiba_datums ASC"
    );
    $stmt->execute(array_merge([$sodien], SLEGTIE_STATUSI));
    return $stmt->fetchAll();
}