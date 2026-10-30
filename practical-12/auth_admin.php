<?php
/**
 * Practical 12: Admin Session & Role-Based Access Control Middleware
 * File: practical-12/auth_admin.php
 *
 * Responsibilities:
 * - Starts session securely.
 * - Restricts access strictly to 'admin' role.
 * - If unauthenticated, redirects to login.php.
 * - If authenticated as non-admin (e.g. 'student'), terminates with 403 Forbidden
 *   "Unauthorized user access restriction".
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function check_admin_access(): void {
    // 1. Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php?msg=auth_required");
        exit;
    }

    // 2. Check if user has admin role
    $role = $_SESSION['role'] ?? 'student';
    if ($role !== 'admin') {
        http_response_code(403);
        die('<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>403 - Unauthorized User Access Restriction</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../practical-10/style.css">
    <style>
        body { display: flex; justify-content: center; align-items: center; min-height: 100vh; background: #f8fafc; }
        .restrict-box { background: #ffffff; border: 1px solid #fecaca; border-radius: 12px; padding: 40px 32px; max-width: 520px; text-align: center; box-shadow: 0 10px 30px rgba(220, 38, 38, 0.08); }
        .icon { font-size: 52px; margin-bottom: 12px; }
        h1 { color: #dc2626; font-size: 1.65rem; margin-bottom: 8px; }
        p { color: #64748b; font-size: 0.94rem; margin-bottom: 24px; line-height: 1.5; }
        .btn-act { display: inline-block; padding: 10px 22px; background: #0a66ff; color: #fff; text-decoration: none; border-radius: 7px; font-weight: 600; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="restrict-box">
        <div class="icon">🚫</div>
        <h1>Unauthorized User Access Restriction</h1>
        <p>Access Denied! You do not have administrative privileges to manage campus events or upload posters. Your current authenticated role is <strong>' . htmlspecialchars($role, ENT_QUOTES, 'UTF-8') . '</strong>.</p>
        <a href="login.php?role_switch=admin" class="btn-act">Login with Admin Credentials</a>
    </div>
</body>
</html>');
    }
}
