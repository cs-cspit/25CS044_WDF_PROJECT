<?php
/**
 * Practical 10: Login Page
 * File: login.php
 *
 * Requirements:
 * - Login form with email and password fields.
 * - Displays error or status notifications.
 * - Posts to authenticate.php.
 */

require_once __DIR__ . '/timeout.php';

// If already authenticated, redirect straight to main dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Generate CSRF token for secure login
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Handle notification messages from GET params or session flash
$error = $_SESSION['auth_error'] ?? null;
unset($_SESSION['auth_error']);

$info = '';
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'logged_out') {
        $info = 'You have logged out successfully.';
    } elseif ($_GET['msg'] === 'timeout') {
        $error = 'Your session has timed out due to inactivity. Please log in again.';
    } elseif ($_GET['msg'] === 'auth_required') {
        $error = 'Access denied. Please log in first.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Practical 10: StudentHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-card">

        <header class="auth-header">
            <h1 style="color:var(--primary);">🎓 StudentHub Portal</h1>
            <p>Practical 10: User Login &amp; Role-Based Access Control</p>
        </header>

        <!-- Informational Alert -->
        <?php if (!empty($info)): ?>
            <div class="alert alert-info">
                <span>ℹ️</span>
                <div><?= htmlspecialchars($info, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        <?php endif; ?>

        <!-- Error Alert -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <span>⚠️</span>
                <div><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form action="authenticate.php" method="POST" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

            <!-- Email Field -->
            <div class="form-group">
                <label for="email" class="form-label">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-control" 
                    placeholder="e.g. 25cs044@charusat.edu.in or admin@charusat.edu.in" 
                    required 
                    autofocus
                >
            </div>

            <!-- Password Field -->
            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-control" 
                    placeholder="Enter your account password" 
                    required
                >
            </div>

            <button type="submit" class="btn-primary">Sign In</button>
        </form>

        <!-- Test Credentials Box -->
        <div class="credentials-box">
            <strong>🔑 Seed Test Credentials (user_registration.sql):</strong><br>
            &bull; <strong>Admin:</strong> <span class="code-pill">admin@charusat.edu.in</span> / <span class="code-pill">StudentHub@2026</span><br>
            &bull; <strong>Student:</strong> <span class="code-pill">25cs044@charusat.edu.in</span> / <span class="code-pill">StudentHub@2026</span>
        </div>

    </div>
</div>

</body>
</html>
