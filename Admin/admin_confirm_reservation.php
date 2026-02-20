<?php
session_start();

/* =========================
   ADMIN AUTH CHECK
   ========================= */
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

/* =========================
   DATABASE CONNECTION
   ========================= */
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'kapetann';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die('Database connection failed');
}

/* =========================
   VALIDATE ID
   ========================= */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: reservations.php?error=invalid');
    exit;
}

$reservation_id = (int) $_GET['id'];

/* =========================
   FETCH RESERVATION
   ========================= */
$stmt = $conn->prepare("SELECT * FROM reservations WHERE id = ? AND status = 'pending'");
$stmt->bind_param("i", $reservation_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: reservations.php?error=notfound');
    exit;
}

$data = $result->fetch_assoc();

/* =========================
   UPDATE STATUS
   ========================= */
$update = $conn->prepare("UPDATE reservations SET status='confirmed' WHERE id=?");
$update->bind_param("i", $reservation_id);
$update->execute();

/* =========================
   REDIRECT & FLUSH (For Speed)
   ========================= */
session_write_close();
header('Location: table.php?confirmed=1');
header('Connection: close');
header('Content-Length: 0');
if (ob_get_level() > 0)
    ob_end_flush();
flush();

/* =========================
   EMAIL SETUP & SEND (Background)
   ========================= */
require_once __DIR__ . '/../PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/../PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer-master/src/SMTP.php';
require_once __DIR__ . '/../config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = SMTP_HOST;
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USERNAME;
    $mail->Password = SMTP_PASSWORD;
    $mail->SMTPSecure = SMTP_SECURE ?? 'tls';
    $mail->Port = SMTP_PORT ?? 587;

    $mail->setFrom(SMTP_USERNAME, 'The Meraki Restaurant');
    $mail->addAddress($data['email'], $data['name']);

    $mail->isHTML(true);
    $mail->Subject = '✅ Your Table Reservation is Confirmed!';

    /* =========================
       BEAUTIFUL EMAIL TEMPLATE
       ========================= */
    $mail->Body = "
    <div style='font-family: Arial; max-width:600px; margin:auto; border:1px solid #ddd; padding:20px;'>
        <h2 style='color:#2c7;'>Reservation Confirmed 🎉</h2>
        <p>Hello <strong>{$data['name']}</strong>,</p>
        <p>Your table reservation has been <strong>successfully confirmed</strong>.</p>

        <table style='width:100%; border-collapse:collapse;'>
            <tr><td><strong>Booking ID</strong></td><td>#{$data['id']}</td></tr>
            <tr><td><strong>Date</strong></td><td>{$data['date']}</td></tr>
            <tr><td><strong>Time</strong></td><td>{$data['time']}</td></tr>
            <tr><td><strong>Guests</strong></td><td>{$data['person']}</td></tr>
            <tr><td><strong>Duration</strong></td><td>{$data['duration_mins']} Minutes</td></tr>
            <tr><td><strong>Booking Fee</strong></td><td>₹{$data['booking_fee']}</td></tr>
        </table>

        <p style='margin-top:15px;'>📍 Please arrive 10 minutes early.</p>

        <p>We look forward to serving you 🍽️</p>

        <p style='margin-top:20px;'>
            Regards,<br>
            <strong>The Tavern Team</strong>
        </p>
    </div>
    ";

    $mail->send();

} catch (Exception $e) {
    error_log("Confirmation email failed: " . $e->getMessage());
}

exit;
