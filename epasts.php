<?php
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ===== IESTATĪJUMI (maini tikai šeit) =====
const SMTP_EPASTS     = 'izdevniecibademo@gmail.com';
const SMTP_PAROLE     = 'kkvv hwoy mrvb brrv';   // <- ieliec savu 16 zīmju paroli
const SUTITAJA_VARDS  = 'Izdevniecība';
const ATBILDES_EPASTS = '';                         // piem., 'diana@izdevnieciba.lv'. Tukšs = nav
// ==========================================

function nosutitApstiprinajumu($saniemeja_epasts, $vards, $pieteikuma_nr) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_EPASTS;
        $mail->Password   = SMTP_PAROLE;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(SMTP_EPASTS, SUTITAJA_VARDS);
        if (ATBILDES_EPASTS !== '') {
            $mail->addReplyTo(ATBILDES_EPASTS, SUTITAJA_VARDS);
        }
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