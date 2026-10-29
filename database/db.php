<?php
/**
 * Practical 8: Secure Database Connection
 * File: db.php
 *
 * Requirements:
 * - Credentials defined at top
 * - charset utf8mb4
 * - PDO::ATTR_ERRMODE => ERRMODE_EXCEPTION
 * - ATTR_DEFAULT_FETCH_MODE => FETCH_ASSOC
 * - ATTR_EMULATE_PREPARES => false
 * - try-catch logging real error with error_log() and showing only generic message
 */

// Database Connection Configuration
$host = 'localhost';
$port = '3306';
$dbname = 'studenthub';
$user = 'root';
$pass = '';

$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_PERSISTENT         => false
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    // Log the real error to server log (never expose credentials or raw exception to client)
    error_log("[Database Error] " . $e->getMessage());

    // Show only generic user-friendly message
    die('<div style="font-family:sans-serif; background:#fee2e2; color:#991b1b; padding:18px; border-radius:8px; margin:20px auto; max-width:600px; border:1px solid #fecaca;">
            <strong>Unable to connect to the database.</strong><br>
            Please verify that MySQL is started in your XAMPP Control Panel and database <code>studenthub</code> exists.
         </div>');
}
