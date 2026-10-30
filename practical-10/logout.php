<?php
/**
 * Practical 10: Secure Logout and Session Destruction
 * File: logout.php
 *
 * Requirements:
 * - Unset all session variables.
 * - Expire and delete the session cookie.
 * - Destroy the session on server with session_destroy().
 * - Redirect to login.php with logged_out message.
 */

require_once __DIR__ . '/timeout.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/audit.php';

// Log voluntary logout in audit trail if user was logged in
if (isset($_SESSION['user_id'])) {
    log_auth_action($conn, 'LOGOUT_SUCCESS', "User logged out voluntarily", (int)$_SESSION['user_id']);
}

// 1. Unset all session values
$_SESSION = [];

// 2. Invalidate session cookie from browser
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 3. Destroy session on server
session_destroy();

// 4. Redirect to login with confirmation
header("Location: login.php?msg=logged_out");
exit;
