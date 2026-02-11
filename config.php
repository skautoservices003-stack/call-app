<?php
// ===============================
// config.php
// ===============================

// Show errors (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ===============================
// Database Configuration
// ===============================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'video_call');

// Create DB connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// ===============================
// Base URL (optional but useful)
// ===============================
define('BASE_URL', 'http://localhost/call-php/');

// ===============================
// Security Headers (basic)
// ===============================
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: no-referrer-when-downgrade");

// ===============================
// Helper: check login
// ===============================
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit;
    }
}