<?php
/**
 * Practical 11: Student Records Management, CRUD Operations, Soft Delete & Excel Export
 * File: practical-11/index.php
 */

session_start();
require_once __DIR__ . '/db.php';

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Flash messages (PRG)
$success_msg = $_SESSION['success_msg'] ?? null;
$error_msg = $_SESSION['error_msg'] ?? null;
unset($_SESSION['success_msg'], $_SESSION['error_msg']);

// -----------------------------------------------------------------------------
// POST HANDLER: CREATE / UPDATE / SOFT DELETE
// -----------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted_token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $submitted_token)) {
        $_SESSION['error_msg'] = "Security validation failed. Please refresh.";
        header("Location: index.php");
        exit;
    }

    $action = $_POST['action'] ?? '';

    // 1. ADD NEW STUDENT (INSERT)
    if ($action === 'create') {
        $roll_no = trim($_POST['roll_no'] ?? '');
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $course = trim($_POST['course'] ?? '');
        $year_of_study = trim($_POST['year_of_study'] ?? '');

        if ($roll_no === '' || $full_name === '' || $email === '' || $phone === '' || $course === '' || $year_of_study === '') {
            $_SESSION['error_msg'] = "All fields are required to register a new student.";
        } else {
            try {
                $sql = "INSERT INTO `students` (`roll_no`, `full_name`, `email`, `phone`, `department`, `course`, `year_of_study`, `status`) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, 'active')";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssss", $roll_no, $full_name, $email, $phone, $department, $course, $year_of_study);
                $stmt->execute();
                $stmt->close();
                $_SESSION['success_msg'] = "Student '$full_name' ($roll_no) added successfully!";
            } catch (mysqli_sql_exception $e) {
                if ($e->getCode() === 1062) {
                    $_SESSION['error_msg'] = "Error: Roll number or email already exists in records.";
                } else {
                    $_SESSION['error_msg'] = "Database error: " . $e->getMessage();
                }
            }
        }
        header("Location: index.php");
        exit;
    }

    // 2. UPDATE STUDENT DETAILS
    if ($action === 'update') {
        $student_id = (int)($_POST['student_id'] ?? 0);
        $full_name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $course = trim($_POST['course'] ?? '');
        $year_of_study = trim($_POST['year_of_study'] ?? '');
        $status = in_array($_POST['status'] ?? '', ['active', 'inactive']) ? $_POST['status'] : 'active';

        if ($student_id <= 0 || $full_name === '' || $email === '') {
            $_SESSION['error_msg'] = "Invalid update parameters provided.";
        } else {
            try {
                $sql = "UPDATE `students` 
                        SET `full_name` = ?, `email` = ?, `phone` = ?, `department` = ?, `course` = ?, `year_of_study` = ?, `status` = ? 
                        WHERE `student_id` = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssssi", $full_name, $email, $phone, $department, $course, $year_of_study, $status, $student_id);
                $stmt->execute();
                $stmt->close();
                $_SESSION['success_msg'] = "Student details for '$full_name' updated successfully!";
            } catch (mysqli_sql_exception $e) {
                $_SESSION['error_msg'] = "Failed to update record: " . $e->getMessage();
            }
        }
        header("Location: index.php");
        exit;
    }

    // 3. SOFT DELETE (Change status to 'inactive')
    if ($action === 'soft_delete') {
        $student_id = (int)($_POST['student_id'] ?? 0);
        if ($student_id > 0) {
            $sql = "UPDATE `students` SET `status` = 'inactive' WHERE `student_id` = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $stmt->close();
            $_SESSION['success_msg'] = "Student record #$student_id successfully soft-deleted (status marked as inactive).";
        } else {
            $_SESSION['error_msg'] = "Invalid student ID for deletion.";
        }
        header("Location: index.php");
        exit;
    }

    // 4. RESTORE (Change status back to 'active')
    if ($action === 'restore') {
        $student_id = (int)($_POST['student_id'] ?? 0);
        if ($student_id > 0) {
            $sql = "UPDATE `students` SET `status` = 'active' WHERE `student_id` = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $stmt->close();
            $_SESSION['success_msg'] = "Student record #$student_id restored to active status!";
        }
        header("Location: index.php");
        exit;
    }
}

// -----------------------------------------------------------------------------
// GET FILTERS & SEARCH
// -----------------------------------------------------------------------------
$search = trim($_GET['search'] ?? '');
$filter_course = trim($_GET['course'] ?? 'all');
$filter_status = trim($_GET['status'] ?? 'all');

// Build query
$query = "SELECT student_id, roll_no, full_name, email, phone, department, course, year_of_study, status, created_at 
          FROM `students` 
          WHERE 1=1";
$params = [];
$types = "";

if ($search !== '') {
    $query .= " AND (full_name LIKE ? OR roll_no LIKE ? OR email LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= "sss";
}

if ($filter_course !== 'all' && $filter_course !== '') {
    $query .= " AND course = ?";
    $params[] = $filter_course;
    $types .= "s";
}

if ($filter_status !== 'all' && $filter_status !== '') {
    $query .= " AND status = ?";
    $params[] = $filter_status;
    $types .= "s";
}

$query .= " ORDER BY student_id ASC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get unique courses for filter dropdown
$courses_res = $conn->query("SELECT DISTINCT course FROM `students` ORDER BY course ASC");
$all_courses = [];
while ($c = $courses_res->fetch_assoc()) {
    $all_courses[] = $c['course'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Practical 11: Student Records Management (CRUD &amp; Soft Delete)</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../practical-10/style.css">
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .filter-card {
            background: #ffffff;
            border-radius: 10px;
            border: 1px solid var(--border);
            padding: 18px 24px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px;
            box-shadow: var(--shadow);
        }

        .filter-form {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .filter-form input,
        .filter-form select {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid var(--border);
            font-size: 0.9rem;
            outline: none;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.88rem;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-add { background: #0a66ff; color: #fff; }
        .btn-add:hover { background: #0052cc; }

        .btn-export { background: #166534; color: #fff; }
        .btn-export:hover { background: #14532d; }

        .btn-edit { background: #eff6ff; color: #1d4ed8; padding: 4px 10px; border: 1px solid #bfdbfe; border-radius: 4px; font-size: 0.8rem; }
        .btn-edit:hover { background: #dbeafe; }

        .btn-del { background: #fee2e2; color: #991b1b; padding: 4px 10px; border: 1px solid #fecaca; border-radius: 4px; font-size: 0.8rem; cursor: pointer; }
        .btn-del:hover { background: #fca5a5; }

        .btn-restore { background: #dcfce7; color: #15803d; padding: 4px 10px; border: 1px solid #bbf7d0; border-radius: 4px; font-size: 0.8rem; cursor: pointer; }

        .status-badge-active { background: #dcfce7; color: #166534; padding: 3px 8px; border-radius: 6px; font-weight: 600; font-size: 0.78rem; }
        .status-badge-inactive { background: #fee2e2; color: #991b1b; padding: 3px 8px; border-radius: 6px; font-weight: 600; font-size: 0.78rem; }

        /* Modal Overlay */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(15, 23, 42, 0.6);
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal.active { display: flex; }

        .modal-content {
            background: #ffffff;
            border-radius: 12px;
            padding: 28px 32px;
            max-width: 540px;
            width: 100%;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            animation: fadeIn 0.2s ease-in-out;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
        }

        .close-btn { font-size: 1.5rem; background: none; border: none; cursor: pointer; color: var(--text-muted); }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="navbar-brand">🎓 StudentHub Admin Portal</a>
    <div class="navbar-user">
        <span class="badge badge-student">PRACTICAL 11</span>
        <a href="export_csv.php?search=<?= urlencode($search) ?>&course=<?= urlencode($filter_course) ?>&status=<?= urlencode($filter_status) ?>" class="btn-action btn-export" id="export-excel-btn">
            📊 Export to Excel (CSV)
        </a>
    </div>
</nav>

<div class="container">

    <div class="page-header">
        <div>
            <h1 style="font-size:1.8rem; color:var(--text-main);">Student Records Management</h1>
            <p style="color:var(--text-muted); font-size:0.92rem;">CRUD Operations &bull; Search &amp; Course Filter &bull; Soft Delete &bull; Excel CSV Export</p>
        </div>
        <button class="btn-action btn-add" onclick="openAddModal()">
            ➕ Add New Student
        </button>
    </div>

    <!-- Success / Error Notifications -->
    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success" id="success-banner">
            <span>✅</span>
            <div><?= htmlspecialchars($success_msg, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger" id="error-banner">
            <span>⚠️</span>
            <div><?= htmlspecialchars($error_msg, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    <?php endif; ?>

    <!-- Filter & Search Bar -->
    <div class="filter-card">
        <form method="GET" action="index.php" class="filter-form">
            <input 
                type="text" 
                name="search" 
                placeholder="🔍 Search name, roll no, email..." 
                value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
                style="width: 260px;"
            >

            <!-- Course-wise Filtering -->
            <select name="course" aria-label="Course Filter">
                <option value="all">All Courses</option>
                <?php foreach ($all_courses as $c): ?>
                    <option value="<?= htmlspecialchars($c, ENT_QUOTES, 'UTF-8') ?>" <?= $filter_course === $c ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Status Filter -->
            <select name="status" aria-label="Status Filter">
                <option value="all" <?= $filter_status === 'all' ? 'selected' : '' ?>>All Status</option>
                <option value="active" <?= $filter_status === 'active' ? 'selected' : '' ?>>Active Only</option>
                <option value="inactive" <?= $filter_status === 'inactive' ? 'selected' : '' ?>>Inactive (Soft-Deleted)</option>
            </select>

            <button type="submit" class="btn-action btn-add" style="padding:8px 14px;">Apply Filter</button>
            <?php if ($search !== '' || $filter_course !== 'all' || $filter_status !== 'all'): ?>
                <a href="index.php" style="color:var(--text-muted); font-size:0.86rem; text-decoration:none;">Reset</a>
            <?php endif; ?>
        </form>

        <span style="font-size:0.86rem; color:var(--text-muted);">
            Showing <strong><?= count($students) ?></strong> student records
        </span>
    </div>

    <!-- Student Records Table -->
    <div class="card">
        <div class="card-title">
            <h2>Student Records Table</h2>
            <small>Active &bull; Soft Deleted</small>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Roll No</th>
                        <th>Full Name</th>
                        <th>Email Address</th>
                        <th>Phone</th>
                        <th>Department</th>
                        <th>Course</th>
                        <th>Year</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($students)): ?>
                        <?php foreach ($students as $s): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($s['roll_no'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td><?= htmlspecialchars($s['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($s['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($s['phone'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($s['department'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><span class="badge badge-purple"><?= htmlspecialchars($s['course'], ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td><?= htmlspecialchars($s['year_of_study'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>
                                    <?php if ($s['status'] === 'active'): ?>
                                        <span class="status-badge-active">Active</span>
                                    <?php else: ?>
                                        <span class="status-badge-inactive">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <!-- Edit Button -->
                                    <button 
                                        type="button" 
                                        class="btn-edit"
                                        onclick='openEditModal(<?= json_encode($s) ?>)'
                                    >
                                        Edit
                                    </button>

                                    <!-- Soft Delete / Restore Form with Confirmation -->
                                    <?php if ($s['status'] === 'active'): ?>
                                        <form method="POST" action="index.php" style="display:inline;" onsubmit="return confirm('Delete confirmation: Are you sure you want to soft delete student \'<?= addslashes($s['full_name']) ?>\'? Their status will be set to inactive.');">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="action" value="soft_delete">
                                            <input type="hidden" name="student_id" value="<?= $s['student_id'] ?>">
                                            <button type="submit" class="btn-del">Delete</button>
                                        </form>
                                    <?php else: ?>
                                        <form method="POST" action="index.php" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                                            <input type="hidden" name="action" value="restore">
                                            <input type="hidden" name="student_id" value="<?= $s['student_id'] ?>">
                                            <button type="submit" class="btn-restore">Restore</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="text-align:center; padding:24px; color:var(--text-muted);">
                                No student records found matching the current search and filter criteria.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal 1: Add Student -->
<div id="addModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Student Record</h3>
            <button class="close-btn" onclick="closeAddModal()">&times;</button>
        </div>
        <form method="POST" action="index.php" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="action" value="create">

            <div class="form-group">
                <label class="form-label">Roll Number *</label>
                <input type="text" name="roll_no" class="form-control" placeholder="e.g. 25CS099" required>
            </div>
            <div class="form-group">
                <label class="form-label">Full Name *</label>
                <input type="text" name="full_name" class="form-control" placeholder="e.g. Yash Patel" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email Address *</label>
                <input type="email" name="email" class="form-control" placeholder="e.g. yash.ce@charusat.edu.in" required>
            </div>
            <div class="form-group">
                <label class="form-label">Phone Number *</label>
                <input type="text" name="phone" class="form-control" placeholder="e.g. 9898012345" required>
            </div>
            <div class="form-group">
                <label class="form-label">Course *</label>
                <select name="course" class="form-control" required>
                    <option value="B.Tech CSE">B.Tech CSE</option>
                    <option value="B.Tech IT">B.Tech IT</option>
                    <option value="B.Tech AI-ML">B.Tech AI-ML</option>
                    <option value="B.Tech EC">B.Tech EC</option>
                    <option value="B.Tech ME">B.Tech ME</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Department *</label>
                <input type="text" name="department" class="form-control" value="Computer Engineering" required>
            </div>
            <div class="form-group">
                <label class="form-label">Year of Study *</label>
                <select name="year_of_study" class="form-control" required>
                    <option value="1st Year">1st Year</option>
                    <option value="2nd Year" selected>2nd Year</option>
                    <option value="3rd Year">3rd Year</option>
                    <option value="4th Year">4th Year</option>
                </select>
            </div>

            <button type="submit" class="btn-primary" style="margin-top:10px;">Save Student Record</button>
        </form>
    </div>
</div>

<!-- Modal 2: Edit Student -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Update Student Details</h3>
            <button class="close-btn" onclick="closeEditModal()">&times;</button>
        </div>
        <form method="POST" action="index.php" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="action" value="update">
            <input type="hidden" id="edit_student_id" name="student_id">

            <div class="form-group">
                <label class="form-label">Roll Number (Read-only)</label>
                <input type="text" id="edit_roll_no" class="form-control" readonly style="background:#f1f5f9;">
            </div>
            <div class="form-group">
                <label class="form-label">Full Name *</label>
                <input type="text" id="edit_full_name" name="full_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Email Address *</label>
                <input type="email" id="edit_email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Phone Number *</label>
                <input type="text" id="edit_phone" name="phone" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Course *</label>
                <select id="edit_course" name="course" class="form-control" required>
                    <option value="B.Tech CSE">B.Tech CSE</option>
                    <option value="B.Tech IT">B.Tech IT</option>
                    <option value="B.Tech AI-ML">B.Tech AI-ML</option>
                    <option value="B.Tech EC">B.Tech EC</option>
                    <option value="B.Tech ME">B.Tech ME</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Department *</label>
                <input type="text" id="edit_department" name="department" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Year of Study *</label>
                <select id="edit_year" name="year_of_study" class="form-control" required>
                    <option value="1st Year">1st Year</option>
                    <option value="2nd Year">2nd Year</option>
                    <option value="3rd Year">3rd Year</option>
                    <option value="4th Year">4th Year</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Account Status</label>
                <select id="edit_status" name="status" class="form-control">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive (Soft Deleted)</option>
                </select>
            </div>

            <button type="submit" class="btn-primary" style="margin-top:10px;">Update Student Record</button>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('addModal').classList.add('active');
}
function closeAddModal() {
    document.getElementById('addModal').classList.remove('active');
}
function openEditModal(student) {
    document.getElementById('edit_student_id').value = student.student_id;
    document.getElementById('edit_roll_no').value = student.roll_no;
    document.getElementById('edit_full_name').value = student.full_name;
    document.getElementById('edit_email').value = student.email;
    document.getElementById('edit_phone').value = student.phone;
    document.getElementById('edit_department').value = student.department;
    document.getElementById('edit_course').value = student.course;
    document.getElementById('edit_year').value = student.year_of_study;
    document.getElementById('edit_status').value = student.status;
    document.getElementById('editModal').classList.add('active');
}
function closeEditModal() {
    document.getElementById('editModal').classList.remove('active');
}
window.onclick = function(event) {
    if (event.target == document.getElementById('addModal')) closeAddModal();
    if (event.target == document.getElementById('editModal')) closeEditModal();
}
</script>

</body>
</html>
