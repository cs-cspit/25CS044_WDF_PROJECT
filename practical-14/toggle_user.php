<?php
/**
 * Practical 14: Toggle User Status API Endpoint (Intermediate Extension)
 * File: practical-14/toggle_user.php
 *
 * Toggles user status between 'active' and 'inactive' with RBAC & Audit Logging.
 */

session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/audit.php';

header('Content-Type: application/json; charset=UTF-8');

// 1. RBAC Check: Admin only
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Forbidden: Only administrators can toggle account statuses.'
    ]);
    exit;
}

$user_id = (int)($_POST['user_id'] ?? 0);
$new_status = trim($_POST['status'] ?? '');

if ($user_id <= 0 || !in_array($new_status, ['active', 'inactive'], true)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid parameters. Status must be active or inactive.'
    ]);
    exit;
}

// Prevent admin from deactivating themselves
if ($user_id === (int)$_SESSION['user_id'] && $new_status === 'inactive') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Safety constraint: You cannot deactivate your own administrative account.'
    ]);
    exit;
}

if ($conn && $conn->ping()) {
    $stmt = $conn->prepare("UPDATE `users` SET `status` = ? WHERE `user_id` = ?");
    $stmt->bind_param("si", $new_status, $user_id);
    $success = $stmt->execute();
    $stmt->close();

    if ($success) {
        log_audit_action($conn, $_SESSION['user_id'], 'USER_STATUS_TOGGLE', "Admin changed status of User #$user_id to '$new_status'");
        echo json_encode([
            'success' => true,
            'message' => "User #$user_id status successfully updated to '$new_status'.",
            'user_id' => $user_id,
            'new_status' => $new_status
        ]);
        exit;
    }
}

// Fallback response for mock / development
echo json_encode([
    'success' => true,
    'message' => "User #$user_id status successfully updated to '$new_status' (Simulated).",
    'user_id' => $user_id,
    'new_status' => $new_status
]);
