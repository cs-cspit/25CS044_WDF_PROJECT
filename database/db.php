<?php
/**
 * Practical 8: Secure Database Connection using PHP Data Objects (PDO)
 * File: db.php
 *
 * Requirements:
 * - Secure PDO connection
 * - Try-catch error handling
 * - UTF-8 character encoding
 * - Exception error mode enabled
 * - Emulated prepared statements turned OFF for enhanced SQL Injection protection
 */

// Database configuration settings
$db_host = 'localhost';
$db_port = '3306';
$db_name = 'studenthub_db';
$db_user = 'root';        // Default XAMPP username
$db_pass = '';            // Default XAMPP password is empty

// Data Source Name (DSN)
$dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";

// PDO driver options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on SQL errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // Return associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // True native prepared statements
    PDO::ATTR_PERSISTENT         => false                   // Non-persistent connection
];

try {
    // Instantiate PDO connection
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
} catch (PDOException $e) {
    // Graceful error handling (logs actual error and outputs user-friendly response)
    error_log("Database Connection Error: " . $e->getMessage());
    die(json_encode([
        'status'  => 'error',
        'message' => 'Database connection failed. Please ensure MySQL is started in XAMPP.',
        'details' => $e->getMessage()
    ]));
}
