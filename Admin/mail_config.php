<?php
// mail_config.php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Ensure Composer is installed

// --- CONFIGURATION ---
define('SMTP_HOST', 'smtp.gmail.com');      // e.g., smtp.gmail.com
define('SMTP_USER', 'your_email@gmail.com'); // Your email
define('SMTP_PASS', 'your_app_password');    // Your App Password (not login password)
define('SMTP_FROM_NAME', 'Meraki Coffee Admin');

function sendMail($to, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use ENCRYPTION_SMTPS for port 465
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom(SMTP_USER, SMTP_FROM_NAME);
        $mail->addAddress($to);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return ['status' => true, 'msg' => 'Email sent successfully'];
    } catch (Exception $e) {
        return ['status' => false, 'msg' => "Mailer Error: {$mail->ErrorInfo}"];
    }
}
?>