<?php
/**
 * Practical 14: Centralized Audit Logging Service
 * File: practical-14/audit.php
 *
 * Records administrative and security events into `audit_logs` table.
 */

if (!function_exists('log_audit_action')) {
    function log_audit_action($conn, $user_id, $action, $details) {
        if (!$conn || !($conn instanceof mysqli)) {
            return false;
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if ($ip === '::1') {
            $ip = '127.0.0.1';
        }

        $user_id_val = ($user_id !== null && $user_id > 0) ? (int)$user_id : null;

        $sql = "INSERT INTO `audit_logs` (`user_id`, `action`, `details`, `ip_address`, `created_at`) 
                VALUES (?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("isss", $user_id_val, $action, $details, $ip);
            $res = $stmt->execute();
            $stmt->close();
            return $res;
        }
        return false;
    }
}
