<?php
// vendor/autoload.php - Manual Loader
// Use this ONLY if you cannot run 'composer install'

// 1. Define the path to your PHPMailer files
// This assumes you have the 'src' folder in your main project folder.
// If your PHPMailer files are in a subfolder (like 'PHPMailer-master'), change the path below.

$baseDir = dirname(__DIR__); // Points to Project-2 folder

// List of possible locations where PHPMailer/src might be
$possiblePaths = [
    $baseDir . '/src',                 // If src is directly in Project-2
    $baseDir . '/PHPMailer/src',       // If inside a PHPMailer folder
    $baseDir . '/PHPMailer-master/src' // If inside PHPMailer-master
];

$found = false;
foreach ($possiblePaths as $path) {
    if (file_exists($path . '/PHPMailer.php')) {
        require_once $path . '/Exception.php';
        require_once $path . '/PHPMailer.php';
        require_once $path . '/SMTP.php';
        $found = true;
        break;
    }
}

if (!$found) {
    // If the files are still missing, stop and show a clear error
    die("<b>Error:</b> Could not find PHPMailer source files.<br>
         Please make sure you have the <b>'src'</b> folder from the PHPMailer library inside your Project-2 folder.");
}
?>