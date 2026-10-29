<?php
/**
 * Practical 9: Secure User Registration with Database Insert, Duplicate Email Check and Password Hashing
 * File: register.php
 *
 * Requirements Covered:
 * - Plain PHP, MySQLi, HTML5, CSS3, JavaScript.
 * - CSRF token generation and strict verification using hash_equals().
 * - Backend duplicate checking (email AND username) via Prepared Statements.
 * - Password hashing with password_hash(..., PASSWORD_DEFAULT) before insert.
 * - Server-enforced role ('student').
 * - Audit logging via log_action() for REGISTRATION_SUCCESS, REGISTRATION_FAILED_DUPLICATE, and CSRF_FAILED.
 * - Post/Redirect/Get pattern with flash session message to prevent re-submissions.
 * - Sticky form inputs with htmlspecialchars() escaping.
 * - Error 1062 fallback handling.
 */

// Start session securely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection and audit logger
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/audit.php';

// Generate or retrieve CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Initialize state
$errors = [];
$fieldErrors = [
    'full_name' => '',
    'username' => '',
    'email' => '',
    'password' => '',
    'confirm_password' => ''
];

$form_data = [
    'full_name' => '',
    'username' => '',
    'email' => ''
];

// Handle Flash Messages (Post/Redirect/Get)
$flash_success = $_SESSION['flash_success'] ?? null;
unset($_SESSION['flash_success']);

// Process Form Submission (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. ADVANCED: Verify CSRF Token with hash_equals()
    $submitted_token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $submitted_token)) {
        log_action($conn, 'CSRF_FAILED', 'Invalid or missing CSRF token during registration attempt');
        $errors[] = 'Security token mismatch. Please refresh the page and try again.';
    } else {

        // 2. Retrieve & Trim Input Data
        $full_name = trim($_POST['full_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Keep sticky data for text fields
        $form_data['full_name'] = $full_name;
        $form_data['username'] = $username;
        $form_data['email'] = $email;

        // 3. BACKEND VALIDATION (Repeat all rules)

        // Check empty fields
        if ($full_name === '' || $username === '' || $email === '' || $password === '' || $confirm_password === '') {
            $errors[] = 'All fields are required.';
        }

        // Full Name Validation
        if ($full_name === '') {
            $fieldErrors['full_name'] = 'Full Name is required.';
        } elseif (mb_strlen($full_name) < 2 || mb_strlen($full_name) > 100) {
            $fieldErrors['full_name'] = 'Full Name must be between 2 and 100 characters.';
            $errors[] = $fieldErrors['full_name'];
        }

        // Username Validation (3-30 chars, alphanumeric + underscores)
        if ($username === '') {
            $fieldErrors['username'] = 'Username is required.';
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username)) {
            $fieldErrors['username'] = 'Username must be 3-30 characters (letters, numbers, underscore only).';
            $errors[] = $fieldErrors['username'];
        }

        // Email Validation
        if ($email === '') {
            $fieldErrors['email'] = 'Email address is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $fieldErrors['email'] = 'Invalid email address.';
            $errors[] = 'Invalid email address.';
        }

        // Password Validation (min 8 chars, letter + number)
        if ($password === '') {
            $fieldErrors['password'] = 'Password is required.';
        } elseif (strlen($password) < 8) {
            $fieldErrors['password'] = 'Password must be at least 8 characters long.';
            $errors[] = $fieldErrors['password'];
        } elseif (!preg_match('/[a-zA-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $fieldErrors['password'] = 'Password must contain at least one letter and one number.';
            $errors[] = $fieldErrors['password'];
        }

        // Password & Confirm Password Match
        if ($confirm_password === '') {
            $fieldErrors['confirm_password'] = 'Please confirm your password.';
        } elseif ($password !== $confirm_password) {
            $fieldErrors['confirm_password'] = 'Passwords do not match.';
            $errors[] = 'Passwords do not match.';
        }

        // Deduplicate general error list for clean presentation
        $errors = array_values(array_unique($errors));

        // 4. DUPLICATE CHECK & DATABASE INSERT (If no validation errors)
        if (empty($errors)) {
            try {
                // Prepared Statement: Check if email OR username is already registered
                $check_sql = "SELECT user_id, username, email FROM `users` WHERE `email` = ? OR `username` = ? LIMIT 2";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("ss", $email, $username);
                $check_stmt->execute();
                $result = $check_stmt->get_result();

                $duplicate_email = false;
                $duplicate_username = false;

                while ($row = $result->fetch_assoc()) {
                    if (strcasecmp($row['email'], $email) === 0) {
                        $duplicate_email = true;
                    }
                    if (strcasecmp($row['username'], $username) === 0) {
                        $duplicate_username = true;
                    }
                }
                $check_stmt->close();

                // Differentiate error messages for email and username
                if ($duplicate_email && $duplicate_username) {
                    $errors[] = 'Both email and username are already registered. Please choose different ones.';
                    $fieldErrors['email'] = 'Email already registered.';
                    $fieldErrors['username'] = 'Username already taken.';
                    log_action($conn, 'REGISTRATION_FAILED_DUPLICATE', "Duplicate email ($email) & username ($username)");
                } elseif ($duplicate_email) {
                    $errors[] = 'Email already registered.';
                    $fieldErrors['email'] = 'Email already registered.';
                    log_action($conn, 'REGISTRATION_FAILED_DUPLICATE', "Duplicate email: $email");
                } elseif ($duplicate_username) {
                    $errors[] = 'Username already taken.';
                    $fieldErrors['username'] = 'Username already taken.';
                    log_action($conn, 'REGISTRATION_FAILED_DUPLICATE', "Duplicate username: $username");
                } else {
                    // 5. SECURE PASSWORD HASHING (BCRYPT / PASSWORD_DEFAULT)
                    // Never log or store plain text passwords!
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);

                    // 6. PREPARED STATEMENT INSERT
                    // Enforce role = 'student' and status = 'active' on server-side
                    $insert_sql = "INSERT INTO `users` (`full_name`, `username`, `email`, `password_hash`, `role`, `status`) 
                                   VALUES (?, ?, ?, ?, 'student', 'active')";
                    $insert_stmt = $conn->prepare($insert_sql);
                    $insert_stmt->bind_param("ssss", $full_name, $username, $email, $password_hash);
                    $insert_stmt->execute();

                    $new_user_id = $insert_stmt->insert_id;
                    $insert_stmt->close();

                    // 7. RECORD AUDIT LOG & REGENERATE CSRF TOKEN
                    log_action($conn, 'REGISTRATION_SUCCESS', "User registered successfully with username: $username", $new_user_id);
                    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Rotate token

                    // 8. POST/REDIRECT/GET (PRG) PATTERN
                    $_SESSION['flash_success'] = 'Registration successful! Your StudentHub account is now active.';
                    header('Location: register.php');
                    exit;
                }

            } catch (mysqli_sql_exception $e) {
                // Fallback for race condition: MySQL error 1062 (Duplicate entry)
                if ($e->getCode() === 1062) {
                    if (strpos($e->getMessage(), 'email') !== false) {
                        $errors[] = 'Email already registered.';
                        $fieldErrors['email'] = 'Email already registered.';
                    } elseif (strpos($e->getMessage(), 'username') !== false) {
                        $errors[] = 'Username already taken.';
                        $fieldErrors['username'] = 'Username already taken.';
                    } else {
                        $errors[] = 'A user with this email or username already exists.';
                    }
                    log_action($conn, 'REGISTRATION_FAILED_DUPLICATE', 'Race condition 1062 duplicate caught');
                } else {
                    error_log("[Database Insert Exception] " . $e->getMessage());
                    $errors[] = 'A server error occurred during registration. Please try again.';
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | StudentHub College Event Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="auth-container">
    <div class="auth-card">

        <!-- Header -->
        <header class="auth-header">
            <a href="#" class="auth-brand">🎓 StudentHub</a>
            <h1>Create Student Account</h1>
            <p>Join campus workshops, hackathons, and technical symposiums</p>
        </header>

        <!-- Success Flash Message -->
        <?php if (!empty($flash_success)): ?>
            <div class="alert alert-success" role="alert">
                <span style="font-size: 1.4rem;">🎉</span>
                <div>
                    <strong>Registration successful!</strong>
                    <div><?= htmlspecialchars($flash_success, ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Error Summary Box -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" role="alert">
                <span style="font-size: 1.4rem;">⚠️</span>
                <div>
                    <strong>Please correct the following errors:</strong>
                    <ul>
                        <?php foreach ($errors as $errorMsg): ?>
                            <li><?= htmlspecialchars($errorMsg, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <!-- Registration Form -->
        <form action="register.php" method="POST" id="registration-form" novalidate autocomplete="off">

            <!-- CSRF Protection Token -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">

            <!-- Field 1: Full Name -->
            <div class="form-group">
                <label for="full_name" class="form-label">Full Name <span class="required">*</span></label>
                <div class="input-wrapper">
                    <input 
                        type="text" 
                        class="form-control <?= !empty($fieldErrors['full_name']) ? 'is-invalid' : '' ?>" 
                        id="full_name" 
                        name="full_name" 
                        placeholder="e.g. Shubham Mendpara" 
                        value="<?= htmlspecialchars($form_data['full_name'], ENT_QUOTES, 'UTF-8') ?>" 
                        required
                    >
                    <div class="invalid-feedback <?= !empty($fieldErrors['full_name']) ? 'active' : '' ?>">
                        <?= htmlspecialchars($fieldErrors['full_name'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                </div>
            </div>

            <!-- Field 2: Username -->
            <div class="form-group">
                <label for="username" class="form-label">Username <span class="required">*</span></label>
                <div class="input-wrapper">
                    <input 
                        type="text" 
                        class="form-control <?= !empty($fieldErrors['username']) ? 'is-invalid' : '' ?>" 
                        id="username" 
                        name="username" 
                        placeholder="e.g. shubham_25" 
                        value="<?= htmlspecialchars($form_data['username'], ENT_QUOTES, 'UTF-8') ?>" 
                        required
                    >
                    <div class="form-text">3-30 characters (letters, numbers, underscore only)</div>
                    <div class="invalid-feedback <?= !empty($fieldErrors['username']) ? 'active' : '' ?>">
                        <?= htmlspecialchars($fieldErrors['username'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                </div>
            </div>

            <!-- Field 3: Email Address -->
            <div class="form-group">
                <label for="email" class="form-label">College Email Address <span class="required">*</span></label>
                <div class="input-wrapper">
                    <input 
                        type="email" 
                        class="form-control <?= !empty($fieldErrors['email']) ? 'is-invalid' : '' ?>" 
                        id="email" 
                        name="email" 
                        placeholder="e.g. 25cs044@charusat.edu.in" 
                        value="<?= htmlspecialchars($form_data['email'], ENT_QUOTES, 'UTF-8') ?>" 
                        required
                    >
                    <div class="invalid-feedback <?= !empty($fieldErrors['email']) ? 'active' : '' ?>">
                        <?= htmlspecialchars($fieldErrors['email'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                </div>
            </div>

            <!-- Field 4: Password -->
            <div class="form-group">
                <label for="password" class="form-label">Password <span class="required">*</span></label>
                <div class="input-wrapper">
                    <input 
                        type="password" 
                        class="form-control <?= !empty($fieldErrors['password']) ? 'is-invalid' : '' ?>" 
                        id="password" 
                        name="password" 
                        placeholder="Minimum 8 characters with letter &amp; number" 
                        required
                    >
                    <!-- Password Strength Meter -->
                    <div class="strength-meter">
                        <div id="strength-bar" class="strength-bar"></div>
                    </div>
                    <span id="strength-text" class="strength-text"></span>

                    <div class="invalid-feedback <?= !empty($fieldErrors['password']) ? 'active' : '' ?>">
                        <?= htmlspecialchars($fieldErrors['password'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                </div>
            </div>

            <!-- Field 5: Confirm Password -->
            <div class="form-group">
                <label for="confirm_password" class="form-label">Confirm Password <span class="required">*</span></label>
                <div class="input-wrapper">
                    <input 
                        type="password" 
                        class="form-control <?= !empty($fieldErrors['confirm_password']) ? 'is-invalid' : '' ?>" 
                        id="confirm_password" 
                        name="confirm_password" 
                        placeholder="Re-enter your password" 
                        required
                    >
                    <div class="invalid-feedback <?= !empty($fieldErrors['confirm_password']) ? 'active' : '' ?>">
                        <?= htmlspecialchars($fieldErrors['confirm_password'], ENT_QUOTES, 'UTF-8') ?>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn-submit" id="submit-btn">Create Account</button>
        </form>

        <!-- Footer -->
        <footer class="auth-footer">
            Already have an account? <a href="#">Sign in here</a> &bull; <a href="../pages/test_connection.html">Practical 8 Output</a>
            <div class="security-badge">
                🔒 Protected by CSRF Token &bull; BCRYPT Password Hashing &bull; Prepared Statements
            </div>
        </footer>

    </div>
</div>

<script src="assets/js/validate.js"></script>
</body>
</html>
