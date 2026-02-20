<?php
include 'db_connect.php';

$sql = "CREATE TABLE IF NOT EXISTS table_locks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    table_id INT NOT NULL,
    user_id INT DEFAULT NULL,
    session_id VARCHAR(255) NOT NULL,
    locked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    INDEX (table_id),
    INDEX (expires_at)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table 'table_locks' created successfully";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
