<?php
/**
 * Practical 8: Test Connection & Prepared Statements Demo
 * File: test_connection.php
 *
 * Requirements Covered:
 * - Requires db.php
 * - Displays green "Database connected successfully" status
 * - Prepared Statements Only (no string concatenation):
 *   * Table 1: All Students
 *   * Table 2: All Events
 *   * Table 3: Registrations with a JOIN (student name, event name, date)
 *   * Table 4: Bound Parameter Example (students filtered by department via placeholder)
 * - Escapes all output with htmlspecialchars()
 * - Clean HTML tables using style.css
 * - Calls Stored Procedure `RegisterStudent` through PDO as a demo
 */

// Include secure database connection
require_once __DIR__ . '/db.php';

// Initialize variables for stored procedure demo
$spOutput = null;
$demoStudentId = 1;
$demoEventId = 3;

try {
    // --------------------------------------------------------------------------
    // DEMO: Call Stored Procedure `RegisterStudent` with IN and OUT parameters
    // --------------------------------------------------------------------------
    $stmtSp = $pdo->prepare("CALL RegisterStudent(:student_id, :event_id, @status_code, @message)");
    $stmtSp->bindParam(':student_id', $demoStudentId, PDO::PARAM_INT);
    $stmtSp->bindParam(':event_id', $demoEventId, PDO::PARAM_INT);
    $stmtSp->execute();
    $stmtSp->closeCursor(); // Free connection for the next query

    // Retrieve OUT parameters from MySQL session
    $spResult = $pdo->query("SELECT @status_code AS status_code, @message AS message")->fetch();
    $spOutput = $spResult;

    // --------------------------------------------------------------------------
    // QUERY 1: Prepared Statement for Table 1 - All Students
    // --------------------------------------------------------------------------
    $stmtStudents = $pdo->prepare("SELECT student_id, full_name, email, phone, department, year_of_study FROM students ORDER BY student_id ASC");
    $stmtStudents->execute();
    $allStudents = $stmtStudents->fetchAll();

    // --------------------------------------------------------------------------
    // QUERY 2: Prepared Statement for Table 2 - All Events
    // --------------------------------------------------------------------------
    $stmtEvents = $pdo->prepare("SELECT event_id, event_name, event_date, venue, organizer_club, max_seats, description FROM events ORDER BY event_date ASC");
    $stmtEvents->execute();
    $allEvents = $stmtEvents->fetchAll();

    // --------------------------------------------------------------------------
    // QUERY 3: Prepared Statement for Table 3 - Registrations with JOIN
    // --------------------------------------------------------------------------
    $sqlJoin = "SELECT 
                    r.registration_id,
                    s.full_name AS student_name,
                    s.department,
                    e.event_name,
                    e.event_date,
                    e.organizer_club,
                    e.venue,
                    r.registered_at,
                    r.status
                FROM registrations r
                INNER JOIN students s ON r.student_id = s.student_id
                INNER JOIN events e ON r.event_id = e.event_id
                ORDER BY r.registered_at DESC";
    $stmtRegistrations = $pdo->prepare($sqlJoin);
    $stmtRegistrations->execute();
    $allRegistrations = $stmtRegistrations->fetchAll();

    // --------------------------------------------------------------------------
    // QUERY 4: Prepared Statement with Bound Parameter (?) - Filter by Department
    // --------------------------------------------------------------------------
    $selectedDept = isset($_GET['dept']) ? trim($_GET['dept']) : 'Computer Engineering';
    $sqlDept = "SELECT student_id, full_name, email, phone, department, year_of_study 
                FROM students 
                WHERE department = ? 
                ORDER BY student_id ASC";
    $stmtFilter = $pdo->prepare($sqlDept);
    $stmtFilter->execute([$selectedDept]);
    $filteredStudents = $stmtFilter->fetchAll();

} catch (PDOException $e) {
    error_log("[Query Execution Error] " . $e->getMessage());
    die("An error occurred while fetching records.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Practical 8: StudentHub Database</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">

    <!-- Header Section -->
    <header class="header">
        <h1>Practical 8: StudentHub Database</h1>
        <p>PHP PDO Prepared Statements &bull; MySQL 3NF Tables &bull; Stored Procedure Demo</p>
    </header>

    <!-- Green Successful Connection Banner -->
    <div class="alert alert-success" role="alert">
        <span style="font-size: 1.6rem;">✅</span>
        <div>
            <strong>Database connected successfully!</strong>
            <div style="font-size: 0.88rem; margin-top: 2px;">
                Connected to MySQL database <code><?= htmlspecialchars($dbname, ENT_QUOTES, 'UTF-8') ?></code> on <code><?= htmlspecialchars($host, ENT_QUOTES, 'UTF-8') ?>:<?= htmlspecialchars($port, ENT_QUOTES, 'UTF-8') ?></code> using PHP PDO.
            </div>
        </div>
    </div>

    <!-- Stored Procedure Execution Demo -->
    <?php if ($spOutput): ?>
        <div class="alert alert-info" role="alert">
            <span style="font-size: 1.6rem;">⚡</span>
            <div>
                <strong>Stored Procedure Execution: <code>RegisterStudent(<?= $demoStudentId ?>, <?= $demoEventId ?>, @code, @msg)</code></strong>
                <div style="font-size: 0.88rem; margin-top: 2px;">
                    Result: <strong><?= htmlspecialchars($spOutput['message'], ENT_QUOTES, 'UTF-8') ?></strong> (Status Code: <?= htmlspecialchars((string)$spOutput['status_code'], ENT_QUOTES, 'UTF-8') ?>)
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Table 1: All Students -->
    <div class="card">
        <div class="card-title">
            <h2>Table 1: All Students (<code>SELECT * FROM students</code>)</h2>
            <small>Prepared Statement without parameters</small>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email Address</th>
                        <th>Phone</th>
                        <th>Department</th>
                        <th>Year</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allStudents as $row): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars((string)$row['student_id'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                            <td><?= htmlspecialchars($row['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($row['phone'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><span class="badge"><?= htmlspecialchars($row['department'], ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td><?= htmlspecialchars($row['year_of_study'], ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Table 2: All Events -->
    <div class="card">
        <div class="card-title">
            <h2>Table 2: All Campus Events (<code>SELECT * FROM events</code>)</h2>
            <small>Prepared Statement without parameters</small>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Event Name</th>
                        <th>Organizer Club</th>
                        <th>Date</th>
                        <th>Venue</th>
                        <th>Max Seats</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allEvents as $row): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars((string)$row['event_id'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                            <td><strong><?= htmlspecialchars($row['event_name'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                            <td><span class="badge badge-purple"><?= htmlspecialchars($row['organizer_club'], ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td><?= htmlspecialchars($row['event_date'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($row['venue'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars((string)$row['max_seats'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($row['description'] ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Table 3: Registrations with JOIN -->
    <div class="card">
        <div class="card-title">
            <h2>Table 3: Event Registrations with JOIN (Students &bull; Events)</h2>
            <small>Many-to-Many Decomposition via Junction Table</small>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Reg ID</th>
                        <th>Student Name</th>
                        <th>Department</th>
                        <th>Event Name</th>
                        <th>Organizer Club</th>
                        <th>Event Date</th>
                        <th>Venue</th>
                        <th>Registered At</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allRegistrations as $row): ?>
                        <tr>
                            <td><strong>#<?= htmlspecialchars((string)$row['registration_id'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                            <td><?= htmlspecialchars($row['student_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($row['department'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><strong><?= htmlspecialchars($row['event_name'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                            <td><span class="badge badge-purple"><?= htmlspecialchars($row['organizer_club'], ENT_QUOTES, 'UTF-8') ?></span></td>
                            <td><?= htmlspecialchars($row['event_date'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($row['venue'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><small><?= htmlspecialchars($row['registered_at'], ENT_QUOTES, 'UTF-8') ?></small></td>
                            <td><span class="badge badge-green"><?= htmlspecialchars(ucfirst($row['status']), ENT_QUOTES, 'UTF-8') ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Table 4: Bound Parameter Example (Filter by Department) -->
    <div class="card">
        <div class="card-title">
            <h2>Table 4: Bound Parameter Filter Query (<code>WHERE department = ?</code>)</h2>
            <small>Security Demo: Parameterized placeholder prevents SQL Injection</small>
        </div>

        <form method="GET" action="test_connection.php" class="filter-form">
            <label for="dept"><strong>Select Department:</strong></label>
            <select name="dept" id="dept">
                <option value="Computer Engineering" <?= $selectedDept === 'Computer Engineering' ? 'selected' : '' ?>>Computer Engineering</option>
                <option value="Information Technology" <?= $selectedDept === 'Information Technology' ? 'selected' : '' ?>>Information Technology</option>
                <option value="Electronics & Communication" <?= $selectedDept === 'Electronics & Communication' ? 'selected' : '' ?>>Electronics & Communication</option>
                <option value="Mechanical Engineering" <?= $selectedDept === 'Mechanical Engineering' ? 'selected' : '' ?>>Mechanical Engineering</option>
            </select>
            <button type="submit">Filter Students</button>
        </form>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Student Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Department</th>
                        <th>Year</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($filteredStudents)): ?>
                        <?php foreach ($filteredStudents as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars((string)$row['student_id'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><strong><?= htmlspecialchars($row['full_name'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td><?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row['phone'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><span class="badge badge-green"><?= htmlspecialchars($row['department'], ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td><?= htmlspecialchars($row['year_of_study'], ENT_QUOTES, 'UTF-8') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center; padding:18px; color:#64748b;">No students found in this department.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="code-snippet">
            <strong>Executed PHP PDO Code:</strong><br>
            $stmt = $pdo-&gt;prepare("SELECT * FROM students WHERE department = ?");<br>
            $stmt-&gt;execute([$selectedDept]); // Bound parameter safely passed to MySQL driver
        </div>
    </div>

</div>

</body>
</html>
