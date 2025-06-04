<?php
/**
 * Helper functions for Online Retail System
 */ 

function checkAuth() {
    if (!isset($_SESSION['logged_in'])) {
        header("Location: index.php");
        exit();
    }
}

// ...existing code...
/**
 * Sanitizes user input
 * @param mixed $input The input to sanitize
 * @return mixed The sanitized input
 */
function sanitizeInput($input) {
    if (is_string($input)) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    return $input;
}

/**
 * Formats currency values
 * @param float $amount The amount to format
 * @return string The formatted amount
 */
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

/**
 * Redirects to a new page
 * @param string $location The URL to redirect to
 */
function redirect($location) {
    header("Location: " . $location);
    exit();
}

/**
 * Logs error messages
 * @param string $message The error message
 * @param string $level The error level (error, warning, info)
 */
function logError($message, $level = 'error') {
    $logFile = __DIR__ . '/logs/' . date('Y-m-d') . '.log';
    $timestamp = date('[Y-m-d H:i:s]');
    $logMessage = "{$timestamp} [{$level}] {$message}" . PHP_EOL;
    
    // Create logs directory if it doesn't exist
    if (!is_dir(__DIR__ . '/logs')) {
        mkdir(__DIR__ . '/logs', 0777, true);
    }
    
    error_log($logMessage, 3, $logFile);
}

/**
 * Gets current page name
 * @return string The current page name
 */
function getCurrentPage() {
    return basename($_SERVER['PHP_SELF'], '.php');
}

/**
 * Checks if current page is active
 * @param string $page The page to check
 * @return bool True if current page
 */
function isActivePage($page) {
    return getCurrentPage() === $page;
}

/**
 * Generate CSRF token
 * @return string The CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 * @param string $token The token to verify
 * @return bool True if valid
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}