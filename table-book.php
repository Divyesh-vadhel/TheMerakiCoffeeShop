<?php
session_start();

/* ==========================
   DATABASE CONNECTION
========================== */
define('SUPPRESS_DB_ERROR', true);
include 'db_connect.php';
if ($conn->connect_error) {
    error_log('DB connection error (table-book): ' . $conn->connect_error);
    header('Location: table.php?error=1');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: table.php');
    exit;
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

/* ==========================
   GET FORM DATA
========================== */
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$date = $_POST['date'] ?? '';
$time = $_POST['time'] ?? '';
$person = (int) ($_POST['person'] ?? 1);
$table_id = (int) ($_POST['table_id'] ?? 0);
$location = trim($_POST['location'] ?? '');
$duration_mins = (int) ($_POST['duration_mins'] ?? 10);

// Calculate Booking Fee: ₹10 per minute (Security Matching Frontend)
$booking_fee = $duration_mins * 10.00; 

// $phone    = trim($_POST['phone'] ?? ''); // Not in new UI but kept for backend compatibility if needed

$user_id = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;

/* ==========================
   VALIDATION
========================== */
if (
    empty($name) ||
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    empty($date) ||
    empty($time) ||
    $table_id === 0
) {
    header('Location: table.php?error=1');
    exit;
}

/* ==========================
   HOURS & TIME VALIDATION
========================== */
$timestamp = strtotime($date . ' ' . $time);
$now = time();

// Prevent past bookings (require 5 min advance notice)
if ($timestamp < ($now + 300)) { // 300s = 5 min advance
    header('Location: table.php?error=past');
    exit;
}

// Operating Hours Check
$day_of_week = date('N', strtotime($date)); // 1 (Mon) to 7 (Sun)
$hour = (int)date('H', strtotime($time));
$is_weekend = ($day_of_week >= 6); // 6=Sat, 7=Sun

$open = 7;
$close = $is_weekend ? 23 : 21;

if ($hour < $open || $hour >= $close) {
    header('Location: table.php?error=hours');
    exit;
}

/* ==========================
   AVAILABILITY CHECK
========================== */
// AVAILABILITY CHECK (Overlap Detection)
$requested_start = strtotime($date . ' ' . $time);
$requested_end = $requested_start + ($duration_mins * 60);

$reschedule_id = (int)($_POST['reschedule_id'] ?? 0);
$conflict_sql = "SELECT time, duration_mins FROM reservations WHERE table_id = ? AND date = ? AND status NOT IN ('Cancelled')";
if ($reschedule_id > 0) {
    $conflict_sql .= " AND id != $reschedule_id";
}

$conflict_stmt = $conn->prepare($conflict_sql);
$conflict_stmt->bind_param('is', $table_id, $date);
$conflict_stmt->execute();
$conflict_result = $conflict_stmt->get_result();

while ($row = $conflict_result->fetch_assoc()) {
    $existing_start = strtotime($date . ' ' . $row['time']);
    $existing_end = $existing_start + ($row['duration_mins'] * 60);
    if ($requested_start < $existing_end && $requested_end > $existing_start) {
        header('Location: table.php?error=booked');
        exit;
    }
}
$conflict_stmt->close();

// LATCH CHECK: Ensure no 15-min lock from someone else
$session_id = session_id();
$lock_check = $conn->prepare("SELECT id FROM table_locks WHERE table_id = ? AND expires_at > NOW() AND session_id != ?");
$lock_check->bind_param('is', $table_id, $session_id);
$lock_check->execute();
if ($lock_check->get_result()->num_rows > 0) {
    header('Location: table.php?error=locked');
    exit;
}
$lock_check->close();


/* ==========================
   INSERT OR UPDATE RESERVATION
========================== */
if ($reschedule_id > 0) {
    $sql = "
        UPDATE reservations 
        SET name=?, email=?, date=?, time=?, person=?, user_id=?, table_id=?, status='Confirmed', location=?, booking_fee=?, duration_mins=?, created_at=NOW()
        WHERE id=? AND user_id=?
    ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log('Prepare failed (reservation update): ' . $conn->error);
        header('Location: table.php?error=1');
        exit;
    }
    $stmt->bind_param('ssssiiissdiii', $name, $email, $date, $time, $person, $user_id, $table_id, $location, $booking_fee, $duration_mins, $reschedule_id, $user_id);
} else {
    $sql = "
        INSERT INTO reservations 
        (name, email, date, time, person, user_id, table_id, status, created_at, location, booking_fee, duration_mins)
        VALUES (?, ?, ?, ?, ?, ?, ?, 'Confirmed', NOW(), ?, ?, ?)
    ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log('Prepare failed (reservation insert): ' . $conn->error);
        header('Location: table.php?error=1');
        exit;
    }
    $stmt->bind_param('ssssiiisdi', $name, $email, $date, $time, $person, $user_id, $table_id, $location, $booking_fee, $duration_mins);
}

if (!$stmt->execute()) {
    error_log('Reservation save failed: ' . $stmt->error);
    header('Location: table.php?error=1');
    exit;
}

$new_res_id = ($reschedule_id > 0) ? $reschedule_id : $stmt->insert_id;

// RELEASE LOCK
$conn->query("DELETE FROM table_locks WHERE table_id = $table_id AND session_id = '$session_id'");


/* ==========================
   OPTIONAL: EMAIL ADMIN
========================= */
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}

if (
    defined('SMTP_HOST') &&
    defined('SMTP_USERNAME') &&
    defined('SMTP_PASSWORD') &&
    defined('ADMIN_EMAIL') &&
    file_exists(__DIR__ . '/PHPMailer-master/src/PHPMailer.php')
) {
    require_once __DIR__ . '/PHPMailer-master/src/Exception.php';
    require_once __DIR__ . '/PHPMailer-master/src/PHPMailer.php';
    require_once __DIR__ . '/PHPMailer-master/src/SMTP.php';

    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = defined('SMTP_SECURE') ? SMTP_SECURE : 'tls';
        $mail->Port = defined('SMTP_PORT') ? SMTP_PORT : 587;

        $fromEmail = defined('EMAIL_FROM_ADDRESS') ? EMAIL_FROM_ADDRESS : SMTP_USERNAME;
        $fromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'The Tavern';

        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress(ADMIN_EMAIL);

        $mail->isHTML(true);
        $mail->Subject = 'New Table Reservation (Confirmed)';
        $mail->Body = "
            <h2>New Reservation Confirmed</h2>
            <p><strong>Name:</strong> {$name}</p>
            <p><strong>Email:</strong> {$email}</p>
            <p><strong>Table:</strong> {$table_id}</p>
            <p><strong>Location:</strong> {$location}</p>
            <p><strong>Date:</strong> {$date}</p>
            <p><strong>Time:</strong> {$time}</p>
            <p><strong>Guests:</strong> {$person}</p>
            <p><strong>Duration:</strong> {$duration_mins} Minutes</p>
            <p><strong>Booking Fee:</strong> ₹{$booking_fee}</p>
        ";

        $mail->send();
    } catch (Exception $e) {
        error_log('Admin notification email failed: ' . $e->getMessage());
    }
}

/* ==========================
   SUCCESS
========================== */
header('Location: checkout.php?res_success=' . $new_res_id);
exit;
