<?php
/**
 * Practical 13: Database Connection using MySQLi
 * File: practical-13/db.php
 */

$host = 'localhost';
$port = 3306;
$user = 'root';
$pass = '';
$dbname = 'studenthub';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $pass, $dbname, $port);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    error_log("[Practical 13 DB Error] " . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'status_code' => 500,
        'message' => 'Database connection failed. Please ensure MySQL is running in XAMPP.',
        'error_detail' => $e->getMessage()
    ]);
    exit;
}
