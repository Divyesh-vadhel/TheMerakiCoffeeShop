<?php
// Load Configuration if available
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}

$host = defined('DB_HOST') ? DB_HOST : 'localhost';
$user = defined('DB_USER') ? DB_USER : 'root';
$password = defined('DB_PASSWORD') ? DB_PASSWORD : '';
$database = defined('DB_NAME') ? DB_NAME : 'kapetann';

// Create connection
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    if (!defined('SUPPRESS_DB_ERROR')) {
        die("Connection failed: " . $conn->connect_error);
    }
}
?>
