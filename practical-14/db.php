<?php
/**
 * Practical 14: MySQL Database Connection
 * File: practical-14/db.php
 */

$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'studenthub';

mysqli_report(MYSQLI_REPORT_OFF);

$conn = @new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    // If running in development/preview without live MySQL
    $db_connected = false;
} else {
    $db_connected = true;
    $conn->set_charset("utf8mb4");
}
