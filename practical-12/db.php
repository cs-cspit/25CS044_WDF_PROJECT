<?php
/**
 * Practical 12: Database Connection using MySQLi
 * File: practical-12/db.php
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
    error_log("[Practical 12 DB Error] " . $e->getMessage());
    http_response_code(500);
    die('<div style="font-family:sans-serif; background:#fee2e2; color:#991b1b; padding:18px; border-radius:8px; max-width:550px; margin:40px auto; border:1px solid #fecaca; text-align:center;">
            <h3>Database Service Unavailable</h3>
            <p>Unable to connect to MySQL database <code>studenthub</code>. Please verify that MySQL is started in XAMPP.</p>
         </div>');
}
