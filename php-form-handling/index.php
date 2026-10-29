<?php
/**
 * ==============================================================================
 * Project: PHP Form Handling (College Assignment Mini Project)
 * File: index.php (Form + Processing Logic + Storage + Display in single file)
 *
 * Requirements Covered:
 * - Form with Full Name, Email, Phone, Age, Gender, Course, Message
 * - POST method, action pointing to itself
 * - Sticky form values on validation failure
 * - Server-side validation with specific field errors + red summary box
 * - Sanitization using trim, stripslashes, htmlspecialchars, filter_var, intval
 * - CSV file storage with headers & timestamp (fputcsv)
 * - JSON file storage with LOCK_EX & JSON_PRETTY_PRINT
 * - Auto-creates 'data' directory if missing
 * - Post/Redirect/Get (PRG) pattern with session flash message to prevent duplicate refresh submissions
 * - View Submitted Records table reading from CSV
 * ==============================================================================
 */

// Start session for PRG (Post/Redirect/Get) flash messages
session_start();

// Allowed whitelist values for validation
$allowedGenders = ['Male', 'Female', 'Other'];
$allowedCourses = [
    'Computer Engineering',
    'Information Technology',
    'Artificial Intelligence & ML',
    'Electronics & Communication',
    'Mechanical Engineering'
];

// Initialize sticky field variables
$fullName = '';
$email    = '';
$phone    = '';
$age      = '';
$gender   = '';
$course   = '';
$message  = '';

// Error tracking array
$errors = [];

// Flash message retrieval (from session redirect)
$flashSuccess = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);

// ==============================================================================
// 1. FORM PROCESSING & POST METHOD HANDLING
// ==============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ==========================================================================
    // 2. SANITIZATION
    // ==========================================================================
    // Helper function for sanitizing general string inputs
    function sanitize($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }

    $fullName = isset($_POST['fullName']) ? sanitize($_POST['fullName']) : '';
    $email    = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL) : '';
    $phone    = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';
    $ageRaw   = isset($_POST['age']) ? trim($_POST['age']) : '';
    $age      = filter_var($ageRaw, FILTER_VALIDATE_INT) !== false ? intval($ageRaw) : '';
    $gender   = isset($_POST['gender']) ? sanitize($_POST['gender']) : '';
    $course   = isset($_POST['course']) ? sanitize($_POST['course']) : '';
    $message  = isset($_POST['message']) ? sanitize($_POST['message']) : '';

    // ==========================================================================
    // 3. SERVER-SIDE VALIDATION
    // ==========================================================================

    // Full Name: non-empty, letters + spaces only, 3-50 chars
    if (empty($fullName)) {
        $errors['fullName'] = 'Full Name is required.';
    } elseif (!preg_match("/^[a-zA-Z\s]{3,50}$/", $fullName)) {
        $errors['fullName'] = 'Name must be 3 to 50 characters long and contain only letters and spaces.';
    }

    // Email: non-empty, valid format
    if (empty($email)) {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address (e.g., student@charusat.edu.in).';
    }

    // Phone: non-empty, exactly 10 digits
    if (empty($phone)) {
        $errors['phone'] = 'Phone number is required.';
    } elseif (!preg_match("/^[0-9]{10}$/", $phone)) {
        $errors['phone'] = 'Phone number must be exactly 10 digits.';
    }

    // Age: non-empty integer between 16 and 60
    if ($ageRaw === '') {
        $errors['age'] = 'Age is required.';
    } elseif ($age === '' || $age < 16 || $age > 60) {
        $errors['age'] = 'Age must be a number between 16 and 60.';
    }

    // Gender: must match allowed list
    if (empty($gender)) {
        $errors['gender'] = 'Please select a gender.';
    } elseif (!in_array($gender, $allowedGenders, true)) {
        $errors['gender'] = 'Selected gender is invalid.';
    }

    // Course: must match allowed list
    if (empty($course)) {
        $errors['course'] = 'Please select a course.';
    } elseif (!in_array($course, $allowedCourses, true)) {
        $errors['course'] = 'Selected course is invalid.';
    }

    // ==========================================================================
    // 4. FILE STORAGE (CSV & JSON) ON VALIDATION SUCCESS
    // ==========================================================================
    if (empty($errors)) {
        $dataDir = __DIR__ . '/data';

        // Auto-create data directory if missing
        if (!is_dir($dataDir)) {
            mkdir($dataDir, 0777, true);
        }

        $timestamp = date('Y-m-d H:i:s');
        $regId     = 'REG-' . strtoupper(substr(md5(uniqid((string)rand(), true)), 0, 6));

        // ----------------------------------------------------------------------
        // A. CSV Storage (data/registrations.csv) using fputcsv
        // ----------------------------------------------------------------------
        $csvPath = $dataDir . '/registrations.csv';
        $isNewFile = !file_exists($csvPath) || filesize($csvPath) === 0;

        $csvHandle = fopen($csvPath, 'a');
        if ($csvHandle) {
            // Write header row if new file
            if ($isNewFile) {
                fputcsv($csvHandle, ['Registration ID', 'Timestamp', 'Full Name', 'Email', 'Phone', 'Age', 'Gender', 'Course', 'Message']);
            }
            fputcsv($csvHandle, [$regId, $timestamp, $fullName, $email, $phone, $age, $gender, $course, $message]);
            fclose($csvHandle);
        }

        // ----------------------------------------------------------------------
        // B. JSON Storage (data/registrations.json) with LOCK_EX & JSON_PRETTY_PRINT
        // ----------------------------------------------------------------------
        $jsonPath = $dataDir . '/registrations.json';
        $records = [];

        if (file_exists($jsonPath)) {
            $rawJson = file_get_contents($jsonPath);
            $records = json_decode($rawJson, true) ?: [];
        }

        $records[] = [
            'registrationId' => $regId,
            'timestamp'      => $timestamp,
            'fullName'       => $fullName,
            'email'          => $email,
            'phone'          => $phone,
            'age'            => $age,
            'gender'         => $gender,
            'course'         => $course,
            'message'        => $message
        ];

        file_put_contents(
            $jsonPath,
            json_encode($records, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
            LOCK_EX
        );

        // ----------------------------------------------------------------------
        // 5. POST / REDIRECT / GET (PRG) PATTERN
        // ----------------------------------------------------------------------
        $_SESSION['flash_success'] = "Registration submitted successfully! Your ID is {$regId}.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Read submitted records from CSV for the records table display
$submittedRecords = [];
$csvPath = __DIR__ . '/data/registrations.csv';
if (file_exists($csvPath) && ($h = fopen($csvPath, 'r')) !== false) {
    $rowIndex = 0;
    while (($row = fgetcsv($h, 1000, ',')) !== false) {
        if ($rowIndex > 0) { // Skip header row
            $submittedRecords[] = $row;
        }
        $rowIndex++;
    }
    fclose($h);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Form Handling | Student Registration</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

    <!-- Header Section -->
    <header class="header">
        <h1>PHP Form Handling</h1>
        <p>Assignment Mini Project &bull; POST Method, Sanitization, Validation &amp; CSV/JSON File Storage</p>
    </header>

    <!-- Success Flash Message (PRG Pattern) -->
    <?php if (!empty($flashSuccess)): ?>
        <div class="alert alert-success" role="alert">
            <strong>✅ Success!</strong>
            <span><?= htmlspecialchars($flashSuccess, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
    <?php endif; ?>

    <!-- Error Summary Box (Shown when validation fails) -->
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" role="alert">
            <strong>⚠️ Please fix the following errors:</strong>
            <ul>
                <?php foreach ($errors as $field => $err): ?>
                    <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Main Registration Form Card -->
    <div class="card">
        <h2>Student Registration Form</h2>

        <!-- Form submitted to itself using POST -->
        <form action="<?= htmlspecialchars($_SERVER['PHP_SELF'], ENT_QUOTES, 'UTF-8') ?>" method="POST" novalidate>

            <!-- Row 1: Full Name & Email -->
            <div class="form-row">
                <div class="form-group <?= isset($errors['fullName']) ? 'has-error' : '' ?>">
                    <label for="fullName">Full Name <span class="required">*</span></label>
                    <input type="text" id="fullName" name="fullName"
                           value="<?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="e.g. Shubham Mendpara">
                    <?php if (isset($errors['fullName'])): ?>
                        <div class="error-text"><?= htmlspecialchars($errors['fullName'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group <?= isset($errors['email']) ? 'has-error' : '' ?>">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="e.g. shubham@charusat.edu.in">
                    <?php if (isset($errors['email'])): ?>
                        <div class="error-text"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Row 2: Phone & Age -->
            <div class="form-row">
                <div class="form-group <?= isset($errors['phone']) ? 'has-error' : '' ?>">
                    <label for="phone">Phone Number (10 Digits) <span class="required">*</span></label>
                    <input type="tel" id="phone" name="phone" maxlength="10"
                           value="<?= htmlspecialchars($phone, ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="e.g. 9876543210">
                    <?php if (isset($errors['phone'])): ?>
                        <div class="error-text"><?= htmlspecialchars($errors['phone'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group <?= isset($errors['age']) ? 'has-error' : '' ?>">
                    <label for="age">Age (16 - 60) <span class="required">*</span></label>
                    <input type="number" id="age" name="age" min="16" max="60"
                           value="<?= htmlspecialchars((string)$age, ENT_QUOTES, 'UTF-8') ?>"
                           placeholder="e.g. 20">
                    <?php if (isset($errors['age'])): ?>
                        <div class="error-text"><?= htmlspecialchars($errors['age'], ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Row 3: Gender (Radio buttons) -->
            <div class="form-group <?= isset($errors['gender']) ? 'has-error' : '' ?>">
                <label>Gender <span class="required">*</span></label>
                <div class="radio-group">
                    <?php foreach ($allowedGenders as $g): ?>
                        <label class="radio-item">
                            <input type="radio" name="gender" value="<?= $g ?>" <?= $gender === $g ? 'checked' : '' ?>>
                            <?= $g ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                <?php if (isset($errors['gender'])): ?>
                    <div class="error-text"><?= htmlspecialchars($errors['gender'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </div>

            <!-- Row 4: Course (Dropdown) -->
            <div class="form-group <?= isset($errors['course']) ? 'has-error' : '' ?>">
                <label for="course">Course <span class="required">*</span></label>
                <select id="course" name="course">
                    <option value="">-- Select Course --</option>
                    <?php foreach ($allowedCourses as $c): ?>
                        <option value="<?= htmlspecialchars($c, ENT_QUOTES, 'UTF-8') ?>" <?= $course === $c ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c, ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['course'])): ?>
                    <div class="error-text"><?= htmlspecialchars($errors['course'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </div>

            <!-- Row 5: Message (Optional) -->
            <div class="form-group">
                <label for="message">Message / Notes (Optional)</label>
                <textarea id="message" name="message" rows="3" placeholder="Additional notes or comments..."><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn-submit">Submit Registration (POST)</button>
        </form>
    </div>

    <!-- Extra Section: View Submitted Records Table -->
    <div class="card">
        <h2>Submitted Records (Read from data/registrations.csv)</h2>
        <?php if (!empty($submittedRecords)): ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date &amp; Time</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Age</th>
                            <th>Gender</th>
                            <th>Course</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_reverse($submittedRecords) as $row): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row[0] ?? '', ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td><small><?= htmlspecialchars($row[1] ?? '', ENT_QUOTES, 'UTF-8') ?></small></td>
                                <td><?= htmlspecialchars($row[2] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row[3] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row[4] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row[5] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row[6] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><span class="badge"><?= htmlspecialchars($row[7] ?? '', ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td><?= htmlspecialchars($row[8] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p style="color:var(--text-muted); text-align:center; padding:18px;">No submitted records yet. Complete the form above to add a record.</p>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
