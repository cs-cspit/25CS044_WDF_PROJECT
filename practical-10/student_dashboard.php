<?php
/**
 * Practical 10: Student Dashboard
 * File: student_dashboard.php
 *
 * Requirements:
 * - Accessible ONLY to Student users.
 * - Session validation: if unauthenticated, redirect to login.php.
 * - Role validation: if logged in as admin, display "Unauthorized Access" error and block page.
 */

require_once __DIR__ . '/timeout.php';
require_once __DIR__ . '/db.php';

// 1. Session Validation
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?msg=auth_required");
    exit;
}

// 2. Strict Role Validation: Student only
if ($_SESSION['role'] !== 'student') {
    http_response_code(403);
    die('<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Unauthorized Access | StudentHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body { display: flex; justify-content: center; align-items: center; min-height: 100vh; background: #f8fafc; }
        .error-card { background: #ffffff; border: 1px solid #fecaca; border-radius: 12px; padding: 36px 30px; max-width: 500px; text-align: center; box-shadow: 0 4px 20px rgba(220, 38, 38, 0.08); }
        .icon { font-size: 48px; margin-bottom: 12px; }
        h1 { color: #dc2626; font-size: 1.6rem; margin-bottom: 10px; }
        p { color: #64748b; font-size: 0.92rem; margin-bottom: 22px; }
        .btn-return { display: inline-block; padding: 10px 22px; background: #dc2626; color: #fff; text-decoration: none; border-radius: 7px; font-weight: 600; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="icon">🚫</div>
        <h1>Unauthorized Access</h1>
        <p>Access Denied! The Student Dashboard is designated exclusively for enrolled students. Your current authenticated role is <strong>admin</strong>.</p>
        <a href="admin_dashboard.php" class="btn-return">Go to Admin Dashboard</a>
    </div>
</body>
</html>');
}

$student_name = $_SESSION['full_name'];
$student_email = $_SESSION['email'];
$user_id = $_SESSION['user_id'];

// Query campus events / registrations
$events = [];
try {
    $res = $conn->query("SELECT event_id, event_name, event_date, venue, organizer_club FROM `events` ORDER BY event_date ASC LIMIT 5");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $events[] = $row;
        }
    }
} catch (Throwable $e) {
    error_log("[Student Dashboard Query] " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | Practical 10: StudentHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <a href="#" class="navbar-brand">🎓 StudentHub Student Portal</a>
    <div class="navbar-user">
        <span>Logged in as: <strong><?= htmlspecialchars($student_name, ENT_QUOTES, 'UTF-8') ?></strong></span>
        <span class="badge badge-student">STUDENT</span>
        <a href="admin_dashboard.php" style="color:#dc2626; font-weight:600; text-decoration:none; font-size:0.86rem;">Test Admin Access &rarr;</a>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>
</nav>

<div class="container">

    <div class="alert alert-success">
        <span>✅</span>
        <div>
            <strong>Student Authentication Verified!</strong>
            <div>Welcome back, <?= htmlspecialchars($student_name, ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($student_email, ENT_QUOTES, 'UTF-8') ?>). Session ID: <code><?= htmlspecialchars(session_id(), ENT_QUOTES, 'UTF-8') ?></code></div>
        </div>
    </div>

    <!-- Student Events Table -->
    <div class="card">
        <div class="card-title">
            <h2>Campus Events Available for Registration</h2>
            <small>Active College Event Feed</small>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Event ID</th>
                        <th>Event Title</th>
                        <th>Organizer Club</th>
                        <th>Event Date</th>
                        <th>Venue</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($events)): ?>
                        <?php foreach ($events as $ev): ?>
                            <tr>
                                <td><strong>#<?= htmlspecialchars((string)$ev['event_id'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td><strong><?= htmlspecialchars($ev['event_name'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td><span class="badge badge-purple"><?= htmlspecialchars($ev['organizer_club'], ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td>📅 <?= htmlspecialchars($ev['event_date'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>📍 <?= htmlspecialchars($ev['venue'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><span class="badge badge-student">Open</span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center; color:var(--text-muted); padding:20px;">No events listed at the moment.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Session & Security Details -->
    <div class="card">
        <div class="card-title">
            <h2>Session &amp; Security Validation Details</h2>
            <small>Practical 10 Verification</small>
        </div>
        <p style="font-size:0.9rem; color:var(--text-muted); margin-bottom:12px;">
            &bull; <strong>Password Verification:</strong> Verified against salted BCRYPT hash using <code>password_verify()</code>.<br>
            &bull; <strong>Session ID Regeneration:</strong> Session ID rotated upon authentication to defeat Session Fixation.<br>
            &bull; <strong>Inactivity Timeout:</strong> Protected by <code>timeout.php</code> (15 min window).<br>
            &bull; <strong>RBAC Middleware:</strong> Strictly enforces <code>$_SESSION['role'] === 'student'</code>.
        </p>
    </div>

</div>

</body>
</html>
