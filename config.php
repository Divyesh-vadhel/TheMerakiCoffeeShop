<?php
/**
 * Application Configuration File
 * Store all sensitive credentials and settings here
 */

// ==============================
// Database Configuration
// ==============================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'kapetann');

// ==============================
// SMTP Configuration (Email)
// ==============================
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls'); // ✅ FIX: REQUIRED FOR PHPMailer
define('SMTP_USERNAME', 'etumatrix0@gmail.com');   // change this
define('SMTP_PASSWORD', 'thnx eeot ibrd hltq');      // Gmail App Password
define('SMTP_FROM_NAME', 'Meraki Coffee Shop');
define('EMAIL_FROM_ADDRESS', SMTP_USERNAME);

// Admin / Owner Email
define('ADMIN_EMAIL', 'etumatrix0@gmail.com');

// ==============================
// Application Settings
// ==============================
define('APP_NAME', 'Meraki Coffee Shop');
define('APP_URL', 'http://' . $_SERVER['HTTP_HOST']);
define('PASSWORD_RESET_EXPIRY', 3600); // 1 hour

// ==============================
// Email Subjects
// ==============================
define('EMAIL_SUBJECT_RESET', 'Password Reset Request');
