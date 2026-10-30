<?php
/**
 * Practical 10: Main Dashboard Router & Session Validation
 * File: dashboard.php
 *
 * Requirements:
 * - Session validation: checks if user is logged in.
 * - Session timeout handling (via timeout.php).
 * - Routes user to their designated dashboard based on role:
 *     * admin   --> admin_dashboard.php
 *     * student --> student_dashboard.php
 */

require_once __DIR__ . '/timeout.php';

// Session Validation: ensure user is authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?msg=auth_required");
    exit;
}

// Role-Based Router
$role = $_SESSION['role'] ?? 'student';

if ($role === 'admin') {
    header("Location: admin_dashboard.php");
    exit;
} else {
    header("Location: student_dashboard.php");
    exit;
}
