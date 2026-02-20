<?php
session_start();
include 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Login required']);
    exit;
}

$table_id = isset($_POST['table_id']) ? (int)$_POST['table_id'] : 0;
$session_id = session_id();
$user_id = $_SESSION['user_id'];

if ($table_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid table']);
    exit;
}

// Clean up old locks
$conn->query("DELETE FROM table_locks WHERE expires_at < NOW()");

// Check if anyone else has a lock
$stmt = $conn->prepare("SELECT id, session_id FROM table_locks WHERE table_id = ? AND expires_at > NOW() AND session_id != ?");
$stmt->bind_param('is', $table_id, $session_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'This table is currently being reserved by someone else. Please try again in 15 minutes.']);
    exit;
}
$stmt->close();

// Create or update lock
$expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));
$check = $conn->prepare("SELECT id FROM table_locks WHERE table_id = ? AND session_id = ?");
$check->bind_param('is', $table_id, $session_id);
$check->execute();
$check_res = $check->get_result();

if ($check_res->num_rows > 0) {
    $row = $check_res->fetch_assoc();
    $update = $conn->prepare("UPDATE table_locks SET expires_at = ? WHERE id = ?");
    $update->bind_param('si', $expires_at, $row['id']);
    $update->execute();
} else {
    $insert = $conn->prepare("INSERT INTO table_locks (table_id, user_id, session_id, expires_at) VALUES (?, ?, ?, ?)");
    $insert->bind_param('iiss', $table_id, $user_id, $session_id, $expires_at);
    $insert->execute();
}

echo json_encode(['success' => true]);
?>
