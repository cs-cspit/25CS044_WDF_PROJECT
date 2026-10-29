<?php
/**
 * contact.php
 * StudentHub Contact & Grievance Registration Form
 *
 * Requirements Covered:
 * 1. PHP Form - Full registration/contact form
 * 2. POST Method - Processed securely via method="POST"
 * 3. Validation - Checks for empty fields, invalid email format, phone digits
 * 4. Sanitization - Strips slashes, trims whitespace, htmlspecialchars, filter_var
 * 5. File Storage - Appends valid submissions into CSV file (data/contacts.csv) and JSON file (data/contacts.json)
 * 6. Success Message - Green alert banner with submission receipt
 * 7. Error Message - Field-specific and general error messages
 */

// Initialize variables
$name = $email = $mobile = $subject = $message = "";
$errors = [];
$successMessage = "";

// Sanitization Helper Function
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Check if form is submitted via POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // 1. Sanitize Inputs
    $name    = isset($_POST["name"]) ? sanitize_input($_POST["name"]) : "";
    $email   = isset($_POST["email"]) ? filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL) : "";
    $mobile  = isset($_POST["mobile"]) ? sanitize_input($_POST["mobile"]) : "";
    $subject = isset($_POST["subject"]) ? sanitize_input($_POST["subject"]) : "";
    $message = isset($_POST["message"]) ? sanitize_input($_POST["message"]) : "";

    // 2. Validation Checks

    // Validate Name
    if (empty($name)) {
        $errors["name"] = "Full name is required.";
    } elseif (!preg_match("/^[a-zA-Z\s]{3,50}$/", $name)) {
        $errors["name"] = "Name must contain only letters (at least 3 characters).";
    }

    // Validate Email
    if (empty($email)) {
        $errors["email"] = "Email address is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors["email"] = "Please enter a valid email address.";
    }

    // Validate Mobile
    if (empty($mobile)) {
        $errors["mobile"] = "Mobile number is required.";
    } elseif (!preg_match("/^[6-9][0-9]{9}$/", $mobile)) {
        $errors["mobile"] = "Enter a valid 10-digit mobile number starting with 6-9.";
    }

    // Validate Subject Dropdown
    if (empty($subject)) {
        $errors["subject"] = "Please select a subject topic.";
    }

    // Validate Message
    if (empty($message)) {
        $errors["message"] = "Message body cannot be empty.";
    } elseif (strlen($message) < 10) {
        $errors["message"] = "Message must be at least 10 characters long.";
    }

    // 3. File Storage If No Validation Errors
    if (empty($errors)) {
        $dataDir = __DIR__ . "/../data";
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0777, true);
        }

        $timestamp = date("Y-m-d H:i:s");
        $ticketId  = "TKT-" . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));

        // A. Store into CSV File (data/contacts.csv)
        $csvFile = $dataDir . "/contacts.csv";
        $csvExists = file_exists($csvFile);
        $fileHandle = fopen($csvFile, "a");

        if ($fileHandle) {
            // Write CSV Header if file created for the first time
            if (!$csvExists || filesize($csvFile) === 0) {
                fputcsv($fileHandle, ["Ticket ID", "Timestamp", "Name", "Email", "Mobile", "Subject", "Message"]);
            }
            fputcsv($fileHandle, [$ticketId, $timestamp, $name, $email, $mobile, $subject, $message]);
            fclose($fileHandle);
        }

        // B. Store into JSON File (data/contacts.json)
        $jsonFile = $dataDir . "/contacts.json";
        $submissions = [];
        if (file_exists($jsonFile)) {
            $jsonContent = file_get_contents($jsonFile);
            $submissions = json_decode($jsonContent, true) ?: [];
        }

        $submissions[] = [
            "ticketId"  => $ticketId,
            "timestamp" => $timestamp,
            "name"      => $name,
            "email"     => $email,
            "mobile"    => $mobile,
            "subject"   => $subject,
            "message"   => $message
        ];

        file_put_contents($jsonFile, json_encode($submissions, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        // Success Feedback
        $successMessage = "Your query has been submitted successfully! Ref Ticket ID: <strong>$ticketId</strong>. Stored in CSV & JSON.";

        // Clear Form fields on success
        $name = $email = $mobile = $subject = $message = "";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | StudentHub PHP Form</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/login.css">
    <link rel="stylesheet" href="../css/public-pages.css">
    <link rel="stylesheet" href="../css/responsive.css">
    <style>
        .msg-banner {
            padding: 16px 20px;
            border-radius: 10px;
            margin-bottom: 24px;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        .msg-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #bbf7d0;
        }
        .msg-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        .field-error {
            color: #dc2626;
            font-size: 0.84rem;
            margin-top: 4px;
            font-weight: 500;
        }
        .input-error {
            border-color: #dc2626 !important;
            background-color: rgba(220, 38, 38, 0.04) !important;
        }
        .contact-form {
            background: var(--surface, #ffffff);
            padding: 32px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            border: 1px solid var(--border, #e2e8f0);
        }
        .contact-form label {
            display: block;
            margin-top: 14px;
            font-weight: 500;
            color: var(--text, #1e293b);
        }
        .contact-form input,
        .contact-form select,
        .contact-form textarea {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid var(--border, #cbd5e1);
            border-radius: 8px;
            font-size: 0.95rem;
            margin-top: 6px;
            outline: none;
            background: var(--surface, #ffffff);
            color: var(--text, #1e293b);
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .contact-form input:focus,
        .contact-form select:focus,
        .contact-form textarea:focus {
            border-color: #0a66ff;
            box-shadow: 0 0 0 3px rgba(10, 102, 255, 0.15);
        }
        .stored-data-preview {
            margin-top: 36px;
            background: var(--surface, #ffffff);
            padding: 24px;
            border-radius: 12px;
            border: 1px solid var(--border, #e2e8f0);
        }
        .stored-data-preview table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }
        .stored-data-preview th,
        .stored-data-preview td {
            padding: 10px 14px;
            border: 1px solid var(--border, #cbd5e1);
            text-align: left;
            font-size: 0.88rem;
        }
        .stored-data-preview th {
            background: #f1f5f9;
            color: #1e293b;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <a class="skip-link" href="#main-content">Skip to main content</a>

    <header>
        <div class="logo">
            <img src="../images/logo.png" alt="StudentHub logo">
            <div>
                <h2>StudentHub</h2>
                <p>Academic Information System</p>
            </div>
        </div>
        <nav class="main-nav" aria-label="Main navigation">
            <a href="../index.html">Home</a>
            <a href="about.html">About</a>
            <a href="faq.html">FAQ</a>
            <a href="contact.php" aria-current="page">Contact (PHP)</a>
            <a href="registration.html">Register</a>
            <a href="../index.html#login">Login</a>
        </nav>
    </header>

    <main id="main-content">
        <section class="page-hero">
            <h1>Contact Us (PHP Form Processing)</h1>
            <p>POST method, Data Sanitization, Validation & CSV/JSON File Storage</p>
        </section>

        <nav class="breadcrumb container" aria-label="Breadcrumb">
            <a href="../index.html">Home</a> &rsaquo; Contact Us
        </nav>

        <section class="content-section">
            <div class="container">

                <!-- 6. Success Message Banner -->
                <?php if (!empty($successMessage)): ?>
                    <div class="msg-banner msg-success" role="alert">
                        ✅ <?= $successMessage ?>
                    </div>
                <?php endif; ?>

                <!-- 7. Error Message Banner -->
                <?php if (!empty($errors)): ?>
                    <div class="msg-banner msg-error" role="alert">
                        ⚠️ Please correct the highlighted errors below before submitting.
                    </div>
                <?php endif; ?>

                <div class="info-grid">
                    <aside class="info-card">
                        <h2>Campus Helpdesk</h2>
                        <p>CHARUSAT Campus, Off Nadiad-Petlad Highway<br>Changa - 388421, Gujarat, India</p>
                        <p><strong>Phone:</strong> +91 2697 265021</p>
                        <p><strong>Email:</strong> support@charusat.edu.in</p>
                        <p style="margin-top:16px; font-size:0.86rem; color:#64748b;">
                            Submissions are automatically stored on the server in:
                            <br><code>data/contacts.csv</code> and <code>data/contacts.json</code>
                        </p>
                    </aside>

                    <!-- 1. PHP Form with 2. POST Method -->
                    <form class="contact-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" novalidate>
                        <h2>Submit a Query / Grievance</h2>

                        <!-- Full Name -->
                        <label for="contact-name">Full Name <span style="color:#ef4444;">*</span></label>
                        <input id="contact-name" name="name" type="text"
                               class="<?= isset($errors['name']) ? 'input-error' : '' ?>"
                               value="<?= htmlspecialchars($name) ?>"
                               placeholder="e.g. John Doe" required>
                        <?php if (isset($errors['name'])): ?>
                            <div class="field-error"><?= $errors['name'] ?></div>
                        <?php endif; ?>

                        <!-- Email -->
                        <label for="contact-email">Email Address <span style="color:#ef4444;">*</span></label>
                        <input id="contact-email" name="email" type="email"
                               class="<?= isset($errors['email']) ? 'input-error' : '' ?>"
                               value="<?= htmlspecialchars($email) ?>"
                               placeholder="student@charusat.edu.in" required>
                        <?php if (isset($errors['email'])): ?>
                            <div class="field-error"><?= $errors['email'] ?></div>
                        <?php endif; ?>

                        <!-- Mobile Number -->
                        <label for="contact-mobile">Mobile Number <span style="color:#ef4444;">*</span></label>
                        <input id="contact-mobile" name="mobile" type="tel" maxlength="10"
                               class="<?= isset($errors['mobile']) ? 'input-error' : '' ?>"
                               value="<?= htmlspecialchars($mobile) ?>"
                               placeholder="10-digit mobile number" required>
                        <?php if (isset($errors['mobile'])): ?>
                            <div class="field-error"><?= $errors['mobile'] ?></div>
                        <?php endif; ?>

                        <!-- Subject Dropdown -->
                        <label for="contact-subject">Topic / Category <span style="color:#ef4444;">*</span></label>
                        <select id="contact-subject" name="subject" class="<?= isset($errors['subject']) ? 'input-error' : '' ?>" required>
                            <option value="">Select a topic</option>
                            <option value="Academic Support" <?= $subject === "Academic Support" ? "selected" : "" ?>>Academic Support</option>
                            <option value="Fees & Payments" <?= $subject === "Fees & Payments" ? "selected" : "" ?>>Fees & Payments</option>
                            <option value="Exam & Results" <?= $subject === "Exam & Results" ? "selected" : "" ?>>Exam & Results</option>
                            <option value="Hostel & Transport" <?= $subject === "Hostel & Transport" ? "selected" : "" ?>>Hostel & Transport</option>
                            <option value="Technical Glitch" <?= $subject === "Technical Glitch" ? "selected" : "" ?>>Technical Glitch</option>
                        </select>
                        <?php if (isset($errors['subject'])): ?>
                            <div class="field-error"><?= $errors['subject'] ?></div>
                        <?php endif; ?>

                        <!-- Message -->
                        <label for="contact-message">Detailed Query <span style="color:#ef4444;">*</span></label>
                        <textarea id="contact-message" name="message" rows="4"
                                  class="<?= isset($errors['message']) ? 'input-error' : '' ?>"
                                  placeholder="Describe your issue or query..." required><?= htmlspecialchars($message) ?></textarea>
                        <?php if (isset($errors['message'])): ?>
                            <div class="field-error"><?= $errors['message'] ?></div>
                        <?php endif; ?>

                        <button type="submit" style="margin-top:18px;">Send Query (POST)</button>
                    </form>
                </div>

                <!-- 5. Stored Data Preview Section -->
                <?php
                $csvPath = __DIR__ . "/../data/contacts.csv";
                if (file_exists($csvPath) && ($handle = fopen($csvPath, "r")) !== false):
                ?>
                <section class="stored-data-preview">
                    <h2>📁 Stored CSV Data Records (Live Server Log)</h2>
                    <p style="color:#64748b; font-size:0.9rem;">Entries stored inside <code>data/contacts.csv</code>:</p>
                    <div style="overflow-x:auto;">
                        <table>
                            <?php
                            $rowIndex = 0;
                            while (($data = fgetcsv($handle, 1000, ",")) !== false):
                                if ($rowIndex === 0):
                            ?>
                                <thead>
                                    <tr>
                                        <?php foreach ($data as $col): ?>
                                            <th><?= htmlspecialchars($col) ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                            <?php else: ?>
                                    <tr>
                                        <?php foreach ($data as $col): ?>
                                            <td><?= htmlspecialchars($col) ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                            <?php
                                endif;
                                $rowIndex++;
                            endwhile;
                            fclose($handle);
                            ?>
                                </tbody>
                        </table>
                    </div>
                </section>
                <?php endif; ?>

            </div>
        </section>
    </main>

    <footer>
        <div class="footer-content">
            <h3>StudentHub</h3>
            <p>Empowering Students Through Technology</p>
            <p>&copy; 2026 StudentHub</p>
        </div>
    </footer>
</body>
</html>
