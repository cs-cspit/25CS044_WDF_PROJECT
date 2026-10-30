<?php
/**
 * Practical 10: Security Audit Logger
 * File: practical-10/audit.php
 */

function get_client_ip(): string {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        $ip = trim($list[0]);
    }
    if (filter_var($ip, FILTER_VALIDATE_IP) === false) {
        $ip = '127.0.0.1';
    }
    return substr($ip, 0, 45);
}

function log_auth_action(mysqli $conn, string $action, string $details = '', ?int $user_id = null): bool {
    $ip = get_client_ip();
    $sql = "INSERT INTO `audit_logs` (`user_id`, `action`, `details`, `ip_address`, `created_at`) 
            VALUES (?, ?, ?, ?, NOW())";
    try {
        $stmt = $conn->prepare($sql);
        if (!$stmt) return false;
        $stmt->bind_param("isss", $user_id, $action, $details, $ip);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    } catch (Throwable $e) {
        error_log("[Auth Audit Error] " . $e->getMessage());
        return false;
    }
}
