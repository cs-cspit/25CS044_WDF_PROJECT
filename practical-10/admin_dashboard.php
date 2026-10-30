<?php
/**
 * Practical 10: Admin Dashboard
 * File: admin_dashboard.php
 *
 * Requirements:
 * - Accessible ONLY to Admin users.
 * - Session validation: if unauthenticated, redirect to login.php.
 * - Role validation: if logged in as student, display "Unauthorized Access" error and block page.
 */

require_once __DIR__ . '/timeout.php';
require_once __DIR__ . '/db.php';

// 1. Session Validation
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?msg=auth_required");
    exit;
}

// 2. Strict Role Validation: Admin only
if ($_SESSION['role'] !== 'admin') {
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
        .btn-return { display: inline-block; padding: 10px 22px; background: #0a66ff; color: #fff; text-decoration: none; border-radius: 7px; font-weight: 600; font-size: 0.9rem; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="icon">🚫</div>
        <h1>Unauthorized Access</h1>
        <p>Access Denied! The Admin Dashboard is restricted exclusively to administrators. Your current authenticated role is <strong>student</strong>.</p>
        <a href="student_dashboard.php" class="btn-return">Go to Student Dashboard</a>
    </div>
</body>
</html>');
}

$admin_name = $_SESSION['full_name'];
$admin_email = $_SESSION['email'];

// Query registered users for admin overview
$users_list = [];
$audit_logs = [];
try {
    $resUsers = $conn->query("SELECT user_id, full_name, email, role, status, created_at FROM `users` ORDER BY user_id ASC");
    while ($row = $resUsers->fetch_assoc()) {
        $users_list[] = $row;
    }

    $resAudit = $conn->query("SELECT log_id, action, details, ip_address, created_at FROM `audit_logs` ORDER BY log_id DESC LIMIT 8");
    while ($row = $resAudit->fetch_assoc()) {
        $audit_logs[] = $row;
    }
} catch (Throwable $e) {
    error_log("[Admin Query Error] " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Practical 10: StudentHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <a href="#" class="navbar-brand">🛡️ StudentHub Admin Console</a>
    <div class="navbar-user">
        <span>Logged in as: <strong><?= htmlspecialchars($admin_name, ENT_QUOTES, 'UTF-8') ?></strong></span>
        <span class="badge badge-admin">ADMIN</span>
        <a href="student_dashboard.php" style="color:var(--primary); font-weight:600; text-decoration:none; font-size:0.86rem;">Test Student View &rarr;</a>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>
</nav>

<div class="container">

    <div class="alert alert-success">
        <span>✅</span>
        <div>
            <strong>Admin Authentication Verified!</strong>
            <div>Welcome back, <?= htmlspecialchars($admin_name, ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($admin_email, ENT_QUOTES, 'UTF-8') ?>). Session ID: <code><?= htmlspecialchars(session_id(), ENT_QUOTES, 'UTF-8') ?></code></div>
        </div>
    </div>

    <!-- Registered Users Table -->
    <div class="card">
        <div class="card-title">
            <h2>Registered Users &amp; Assigned Roles (<code>user_registration.sql</code>)</h2>
            <small>Active RBAC Directory</small>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Account Status</th>
                        <th>Registered At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users_list as $u): ?>
                        <tr>
                            <td><strong>#<?= htmlspecialchars((string)$u['user_id'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                            <td><?= htmlspecialchars($u['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td>
                                <span class="badge <?= $u['role'] === 'admin' ? 'badge-admin' : 'badge-student' ?>">
                                    <?= htmlspecialchars(strtoupper($u['role']), ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($u['status'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><small><?= htmlspecialchars($u['created_at'], ENT_QUOTES, 'UTF-8') ?></small></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Audit Logs Table -->
    <div class="card">
        <div class="card-title">
            <h2>Authentication &amp; Access Audit Trail</h2>
            <small>Live Security Tracking</small>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Log ID</th>
                        <th>Action</th>
                        <th>Details</th>
                        <th>IP Address</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($audit_logs)): ?>
                        <?php foreach ($audit_logs as $a): ?>
                            <tr>
                                <td>#<?= htmlspecialchars((string)$a['log_id'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><span class="badge badge-purple"><?= htmlspecialchars($a['action'], ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td><?= htmlspecialchars($a['details'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><code><?= htmlspecialchars($a['ip_address'], ENT_QUOTES, 'UTF-8') ?></code></td>
                                <td><small><?= htmlspecialchars($a['created_at'], ENT_QUOTES, 'UTF-8') ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center; color:var(--text-muted);">No audit logs recorded yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

</body>
</html>
