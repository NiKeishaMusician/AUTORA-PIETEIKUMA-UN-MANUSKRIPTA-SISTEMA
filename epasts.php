<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function nosutitApstiprinajumu($saniemeja_epasts, $vards, $pieteikuma_nr) {
    $smtpEpasts = getenv('SMTP_USER') ?: 'izdevniecibademo@gmail.com';
    $smtpParole = getenv('SMTP_PASS') ?: 'kkvv hwoy mrvb brrv';

    if ($smtpParole === '') {
        error_log('E-pasts nav nosūtīts: nav iestatīts SMTP_PASS.');
        return false;
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpEpasts;
        $mail->Password   = $smtpParole;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        $mail->Timeout    = 8;

        $mail->setFrom($smtpEpasts, 'Izdevniecība');
        $mail->addAddress($saniemeja_epasts, $vards);

        $vardsDrosi = htmlspecialchars($vards);
        $nrDrosi = htmlspecialchars($pieteikuma_nr);

        $mail->isHTML(true);
        $mail->Subject = 'Jūsu pieteikums ir saņemts';
        $mail->Body    = "
            <p>Sveiki, {$vardsDrosi}!</p>
            <p>Paldies! Jūsu pieteikums izdevniecībai ir saņemts.</p>
            <p>Jūsu pieteikuma numurs: <strong>{$nrDrosi}</strong></p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('E-pasta kļūda: ' . $mail->ErrorInfo);
        return false;
    }
}