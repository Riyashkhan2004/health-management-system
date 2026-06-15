<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

function sendMail($toEmail, $subject, $messageBody, $doctorEmail, $doctorPassword, $doctorName) {
    $mail = new PHPMailer(true);

    try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = $doctorEmail; // Doctor’s Gmail
        $mail->Password = $doctorPassword; // Doctor’s App Password (NOT Gmail password)
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Sender info
        $mail->setFrom($doctorEmail, $doctorName);
        $mail->addAddress($toEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $messageBody;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mail Error: {$mail->ErrorInfo}");
        return false;
    }
}
?>
