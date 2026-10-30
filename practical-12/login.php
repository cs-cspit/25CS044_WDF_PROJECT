<?php
/**
 * Practical 12: Admin Login Page
 * File: practical-12/login.php
 */

session_start();
require_once __DIR__ . '/db.php';

$error = '';
$info = '';

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'auth_required') {
        $error = 'Admin authentication required. Please sign in with your admin credentials.';
    } elseif ($_GET['msg'] === 'logged_out') {
        $info = 'You have logged out successfully.';
    }
}

// Quick demo login switch
if (isset($_GET['demo_admin'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['full_name'] = 'System Administrator';
    $_SESSION['email'] = 'admin@charusat.edu.in';
    $_SESSION['role'] = 'admin';
    header("Location: admin_dashboard.php");
    exit;
}

if (isset($_GET['demo_student'])) {
    $_SESSION['user_id'] = 2;
    $_SESSION['full_name'] = 'Shubham Mendpara';
    $_SESSION['email'] = '25cs044@charusat.edu.in';
    $_SESSION['role'] = 'student';
    header("Location: admin_dashboard.php"); // Will trigger Unauthorized Restriction!
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Both email and password are required.';
    } else {
        $stmt = $conn->prepare("SELECT user_id, full_name, email, password_hash, role, status FROM `users` WHERE `email` = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];

            header("Location: admin_dashboard.php");
            exit;
        } else {
            $error = 'Invalid email address or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | Practical 12 - StudentHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../practical-10/style.css">
    <style>
        .demo-box {
            background: #f1f5f9;
            border: 1px dashed #94a3b8;
            border-radius: 8px;
            padding: 14px 18px;
            margin-top: 20px;
            font-size: 0.85rem;
        }
        .demo-btn {
            display: inline-block;
            margin-top: 6px;
            padding: 5px 12px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
        }
        .demo-btn-admin { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
        .demo-btn-student { background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; }
    </style>
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-card">

        <header class="auth-header">
            <h1 style="color:#dc2626;">🛡️ Admin Login</h1>
            <p>Practical 12: Event Creation, Poster Uploads &amp; Access Controls</p>
        </header>

        <?php if (!empty($info)): ?>
            <div class="alert alert-info">
                <span>ℹ️</span>
                <div><?= htmlspecialchars($info, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <span>⚠️</span>
                <div><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST" autocomplete="off">
            <div class="form-group">
                <label for="email" class="form-label">Admin Email</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    class="form-control" 
                    placeholder="e.g. admin@charusat.edu.in" 
                    required 
                    autofocus
                >
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-control" 
                    placeholder="Enter your password" 
                    required
                >
            </div>

            <button type="submit" class="btn-primary" style="background:#dc2626;">Sign In as Admin</button>
        </form>

        <div class="demo-box">
            <strong>⚡ Quick Viva One-Click Authentication:</strong><br>
            <a href="login.php?demo_admin=1" class="demo-btn demo-btn-admin">🔑 Login as Admin (Passes Access)</a>
            <a href="login.php?demo_student=1" class="demo-btn demo-btn-student">👤 Login as Student (Demonstrates Restriction)</a>
        </div>

    </div>
</div>

</body>
</html>
