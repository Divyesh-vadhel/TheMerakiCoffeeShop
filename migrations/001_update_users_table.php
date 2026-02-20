<?php
/**
 * Run this script once (visit in browser or CLI) to update the users table:
 * - enlarge password column to 255
 * - add is_active and activation_token columns
 */

$host='localhost'; $user='root'; $password=''; $database='kapetann';
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) die('DB connection failed: ' . $conn->connect_error);

// Check current columns
$res = $conn->query("SHOW COLUMNS FROM users LIKE 'is_active'");
if ($res && $res->num_rows > 0) {
    echo "Migration already applied.\n";
    exit;
}

// Modify password column
if (!$conn->query("ALTER TABLE users MODIFY password VARCHAR(255) NOT NULL")) {
    echo "Failed to modify password column: " . $conn->error;
    exit;
}

// Add is_active and activation_token
if (!$conn->query("ALTER TABLE users ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 0, ADD COLUMN activation_token VARCHAR(64) DEFAULT NULL")) {
    echo "Failed to add columns: " . $conn->error;
    exit;
}

echo "Migration completed successfully.\n";

// Close
$conn->close();

?>
