<?php
/**
 * Practical 10: Authentication Processor
 * File: authenticate.php
 *
 * Requirements:
 * - Login authentication using password_verify().
 * - Session ID regeneration (session_regenerate_id(true)) upon successful authentication.
 * - Redirects to dashboard.php on success, or back to login.php with error message on failure.
 */

require_once __DIR__ . '/timeout.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/audit.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

// 1. Verify CSRF Token
$token = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    $_SESSION['auth_error'] = 'Security validation failed. Please try again.';
    header("Location: login.php");
    exit;
}

// 2. Validate Inputs
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($email === '' || $password === '') {
    $_SESSION['auth_error'] = 'Both email and password are required.';
    header("Location: login.php");
    exit;
}

// 3. Query User using MySQLi Prepared Statement
$sql = "SELECT user_id, full_name, username, email, password_hash, role, status 
        FROM `users` 
        WHERE `email` = ? 
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// 4. Verify Credentials
if (!$user) {
    // Record audit log
    log_auth_action($conn, 'LOGIN_FAILED_NOT_FOUND', "Failed attempt for non-existent email: $email");
    $_SESSION['auth_error'] = 'Invalid email or password.';
    header("Location: login.php");
    exit;
}

// Check active status
if ($user['status'] !== 'active') {
    log_auth_action($conn, 'LOGIN_FAILED_DEACTIVATED', "Deactivated account: {$user['user_id']}", $user['user_id']);
    $_SESSION['auth_error'] = 'Account deactivated. Please contact college administration.';
    header("Location: login.php");
    exit;
}

// Check password using password_verify()
if (!password_verify($password, $user['password_hash'])) {
    log_auth_action($conn, 'LOGIN_FAILED_WRONG_PASSWORD', "Wrong password for user_id: {$user['user_id']}", $user['user_id']);
    $_SESSION['auth_error'] = 'Invalid email or password.';
    header("Location: login.php");
    exit;
}

// 5. SUCCESSFUL AUTHENTICATION
// CRUCIAL: Regenerate session ID to prevent Session Fixation attacks
session_regenerate_id(true);

// Set authenticated session variables
$_SESSION['user_id'] = (int)$user['user_id'];
$_SESSION['full_name'] = $user['full_name'];
$_SESSION['email'] = $user['email'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role'];
$_SESSION['last_activity'] = time();

// Rotate CSRF token
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

// Log success in audit trail
log_auth_action($conn, 'LOGIN_SUCCESS', "User authenticated as {$user['role']}", $user['user_id']);

// Redirect to main dashboard
header("Location: dashboard.php");
exit;
