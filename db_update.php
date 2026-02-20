<?php
$conn = new mysqli('localhost', 'root', '', 'kapetann');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if column exists
$result = $conn->query("SHOW COLUMNS FROM orders LIKE 'status'");
if ($result->num_rows == 0) {
    // Add column
    $sql = "ALTER TABLE orders ADD COLUMN status VARCHAR(20) DEFAULT 'Pending' AFTER invoice_number";
    if ($conn->query($sql) === TRUE) {
        echo "✅ Successfully added 'status' column to 'orders' table.";
    } else {
        echo "❌ Error adding column: " . $conn->error;
    }
} else {
    echo "ℹ️ 'status' column already exists.";
}
$conn->close();
?>