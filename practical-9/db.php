<?php
/**
 * Practical 9: Database Connection using MySQLi (Object-Oriented)
 * File: db.php
 *
 * Requirements:
 * - MySQLi connection with host, user, pass, dbname, charset utf8mb4.
 * - mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT)
 * - try-catch: log real error with error_log(), show only generic message (never credentials).
 */

// Database Connection Parameters
$db_host = 'localhost';
$db_port = 3306;
$db_user = 'root';
$db_pass = '';
$db_name = 'studenthub';

// Enable strict MySQLi exception reporting
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Initialize MySQLi connection
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);

    // Set charset to utf8mb4 for full Unicode and emoji support
    $conn->set_charset('utf8mb4');

} catch (mysqli_sql_exception $e) {
    // Log the actual exception details securely on the server
    error_log("[MySQLi Connection Error] " . $e->getMessage() . " (Code: " . $e->getCode() . ")");

    // User-facing generic error (no credentials or internal host names exposed)
    http_response_code(500);
    die('<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Database Service Unavailable</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f8fafc; padding: 50px 20px; display: flex; justify-content: center; }
        .error-card { background: #ffffff; border: 1px solid #fecaca; border-radius: 12px; padding: 30px; max-width: 550px; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.08); text-align: center; }
        .error-icon { font-size: 42px; margin-bottom: 12px; }
        h2 { color: #991b1b; margin-bottom: 10px; }
        p { color: #64748b; font-size: 0.95rem; line-height: 1.5; }
        .badge { background: #fee2e2; color: #991b1b; padding: 4px 10px; border-radius: 6px; font-size: 0.82rem; font-weight: 600; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon">⚠️</div>
        <h2>Database Service Unavailable</h2>
        <p>We are temporarily unable to connect to the database service. Please check back shortly or ensure that the database server is running in your control panel.</p>
        <span class="badge">HTTP 500 &bull; Database Connection Failure</span>
    </div>
</body>
</html>');
}
