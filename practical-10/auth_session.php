<?php
/**
 * Practical 10: Secure Session Manager & RBAC Middleware
 * File: practical-10/auth_session.php
 *
 * Responsibilities:
 * - Start secure session with cookie security flags (HttpOnly, SameSite, Secure).
 * - Session regeneration on login to prevent Session Fixation.
 * - Inactivity timeout (e.g., 30 minutes).
 * - Role-Based Access Control (RBAC) verification helper functions.
 */

// Configure secure session cookie parameters before session_start()
if (session_status() === PHP_SESSION_NONE) {
    $cookieParams = [
        'lifetime' => 0, // Session cookie expires when browser closes
        'path'     => '/',
        'domain'   => '',
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
        'httponly' => true, // Mitigate XSS cookie stealing
        'samesite' => 'Lax' // Mitigate CSRF
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

// -----------------------------------------------------------------------------
// Session Inactivity Timeout Check (30 Minutes)
// -----------------------------------------------------------------------------
$session_timeout = 1800; // 30 minutes in seconds

if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $session_timeout)) {
    // Session expired: clean up and redirect
    session_unset();
    session_destroy();
    header("Location: login.php?msg=session_expired");
    exit;
}
$_SESSION['last_activity'] = time(); // Update activity timestamp

/**
 * Check if a user is currently logged in
 *
 * @return bool
 */
function is_logged_in(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Require authentication; redirects to login.php if unauthenticated
 */
function require_login(): void {
    if (!is_logged_in()) {
        header("Location: login.php?msg=auth_required");
        exit;
    }
}

/**
 * Require a specific role or array of allowed roles
 *
 * @param string|array $allowed_roles e.g. 'admin' or ['admin', 'organizer']
 */
function require_role($allowed_roles): void {
    require_login();

    $user_role = $_SESSION['user_role'] ?? 'student';
    $roles = is_array($allowed_roles) ? $allowed_roles : [$allowed_roles];

    if (!in_array($user_role, $roles, true)) {
        http_response_code(403);
        die('<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>403 - Access Forbidden</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f8fafc; padding: 60px 20px; display: flex; justify-content: center; }
        .box { background: #ffffff; border: 1px solid #fecaca; border-radius: 12px; padding: 32px; max-width: 500px; text-align: center; box-shadow: 0 4px 18px rgba(0,0,0,0.06); }
        h1 { color: #dc2626; font-size: 1.8rem; margin-bottom: 8px; }
        p { color: #64748b; font-size: 0.95rem; margin-bottom: 20px; }
        .btn { display: inline-block; padding: 10px 20px; background: #0a66ff; color: #fff; text-decoration: none; border-radius: 6px; font-weight: 600; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="box">
        <div style="font-size: 40px; margin-bottom: 12px;">🚫</div>
        <h1>403 Forbidden</h1>
        <p>You do not have permission to access this resource. Your current role is <strong>' . htmlspecialchars($user_role, ENT_QUOTES, 'UTF-8') . '</strong>.</p>
        <a href="dashboard.php" class="btn">Return to My Dashboard</a>
    </div>
</body>
</html>');
    }
}
