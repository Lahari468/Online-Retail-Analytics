<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Define constants
define('SITE_URL', 'http://localhost/ors');
define('SITE_NAME', 'Online Retail Analytics');

// Set timezone
date_default_timezone_set('Asia/Kolkata');

// Admin credentials
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); // hashed 'admin'

// Include database connection last
require_once 'db.php';