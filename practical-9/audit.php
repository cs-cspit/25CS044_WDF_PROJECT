<?php
/**
 * Practical 9: Security Audit Logging Function
 * File: audit.php
 *
 * Requirements:
 * - log_action($conn, $action, $details, $user_id = null)
 * - Records REGISTRATION_SUCCESS, REGISTRATION_FAILED_DUPLICATE, CSRF_FAILED, etc.
 * - Captures client IP address and timestamp.
 * - Uses MySQLi prepared statements to prevent SQL injection.
 */

/**
 * Retrieves the client's IP address accurately even behind proxies
 *
 * @return string
 */
function get_client_ip(): string {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // HTTP_X_FORWARDED_FOR can contain comma-separated IPs; pick the first
        $ipList = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($ipList[0]);
    }

    // Sanitize / validate IP format (IPv4 or IPv6)
    if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
        $ip = '127.0.0.1';
    }

    return substr($ip, 0, 45);
}

/**
 * Logs a security event into the `audit_logs` table using prepared statements
 *
 * @param mysqli $conn Active MySQLi database connection
 * @param string $action Event action name (e.g., REGISTRATION_SUCCESS, REGISTRATION_FAILED_DUPLICATE, CSRF_FAILED)
 * @param string $details Extra metadata, error details, or identifiers
 * @param int|null $user_id Optional user ID associated with the action
 * @return bool True if logged successfully, false otherwise
 */
function log_action(mysqli $conn, string $action, string $details = '', ?int $user_id = null): bool {
    $ip = get_client_ip();
    $sql = "INSERT INTO `audit_logs` (`user_id`, `action`, `details`, `ip_address`, `created_at`) 
            VALUES (?, ?, ?, ?, NOW())";

    try {
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("[Audit Log Prepare Failed] " . $conn->error);
            return false;
        }

        // Bind parameters: user_id (integer or null), action (string), details (string), ip_address (string)
        $stmt->bind_param("isss", $user_id, $action, $details, $ip);
        $result = $stmt->execute();
        $stmt->close();
        return $result;

    } catch (Throwable $e) {
        error_log("[Audit Log Error] " . $e->getMessage());
        return false;
    }
}
