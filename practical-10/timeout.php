<?php
/**
 * Practical 10: Session Timeout Handling
 * File: timeout.php
 *
 * Responsibilities:
 * - Starts session with secure cookie parameters if not already active.
 * - Enforces session inactivity timeout (defaults to 15 minutes / 900 seconds).
 * - Updates last activity timestamp on each active user request.
 */

if (session_status() === PHP_SESSION_NONE) {
    // Configure secure cookie parameters
    $cookieParams = [
        'lifetime' => 0, // Session cookie lasts until browser close
        'path'     => '/',
        'domain'   => '',
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true, // Prevent JavaScript from accessing session cookie
        'samesite' => 'Lax'
    ];

    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params($cookieParams);
    } else {
        session_set_cookie_params(
            $cookieParams['lifetime'],
            $cookieParams['path'] . '; samesite=' . $cookieParams['samesite'],
            $cookieParams['domain'],
            $cookieParams['secure'],
            $cookieParams['httponly']
        );
    }

    session_start();
}

// Timeout duration in seconds (15 minutes = 900s)
// For quick viva demo/testing, can also test with ?test_timeout=1
$timeout_duration = 900; 

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout_duration)) {
        // Session timed out: clean up session data and cookie
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();

        // Redirect to login with explicit timeout status
        header("Location: login.php?msg=timeout");
        exit;
    }
    // Update last activity timestamp
    $_SESSION['last_activity'] = time();
}
