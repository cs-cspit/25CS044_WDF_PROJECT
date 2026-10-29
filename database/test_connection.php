<?php
/**
 * Practical 8: Test Database Connection & Prepared Statements
 * File: test_connection.php
 *
 * Requirements:
 * - Includes secure db.php
 * - Executes parameterized SQL queries using Prepared Statements
 * - Joins 3NF tables (students, events, registrations)
 * - Renders styled HTML output showing successful connection and records
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$connectionStatus = false;
$errorMessage = "";
$records = [];
$totalStudents = 0;
$totalEvents = 0;
$totalRegistrations = 0;

try {
    require_once __DIR__ . '/db.php';
    $connectionStatus = true;

    // 1. Prepared Statement: Count summary statistics
    $stmtCount = $pdo->prepare("SELECT 
        (SELECT COUNT(*) FROM students) AS student_count,
        (SELECT COUNT(*) FROM events) AS event_count,
        (SELECT COUNT(*) FROM registrations) AS reg_count");
    $stmtCount->execute();
    $counts = $stmtCount->fetch();

    $totalStudents = $counts['student_count'] ?? 0;
    $totalEvents = $counts['event_count'] ?? 0;
    $totalRegistrations = $counts['reg_count'] ?? 0;

    // 2. Prepared Statement: Fetch Joined 3NF Tables with parameter binding
    $filterStatus = isset($_GET['status']) ? $_GET['status'] : 'Confirmed';

    $sql = "SELECT 
                r.registration_id,
                r.ticket_no,
                r.registration_date,
                r.status,
                s.roll_no,
                s.full_name AS student_name,
                s.department,
                s.email AS student_email,
                e.event_code,
                e.title AS event_title,
                e.category AS event_category,
                e.event_date,
                e.venue
            FROM registrations r
            INNER JOIN students s ON r.student_id = s.student_id
            INNER JOIN events e ON r.event_id = e.event_id
            WHERE r.status = :status
            ORDER BY r.registration_date DESC";

    $stmt = $pdo->prepare($sql);
    // Secure Parameter Binding
    $stmt->bindParam(':status', $filterStatus, PDO::PARAM_STR);
    $stmt->execute();
    $records = $stmt->fetchAll();

} catch (Exception $e) {
    $connectionStatus = false;
    $errorMessage = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Practical 8 | Database Connection & Prepared Statements</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0a66ff;
            --success-bg: #dcfce7;
            --success-text: #166534;
            --danger-bg: #fee2e2;
            --danger-text: #991b1b;
            --card-bg: #ffffff;
            --text-dark: #0f172a;
            --border: #cbd5e1;
        }

        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8fafc;
            color: var(--text-dark);
            padding: 40px 20px;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        header {
            text-align: center;
            margin-bottom: 28px;
        }

        header h1 {
            color: var(--primary);
            font-size: 2.2rem;
            margin-bottom: 6px;
        }

        header p {
            color: #64748b;
            font-size: 1rem;
        }

        /* Status Banner */
        .status-box {
            padding: 16px 20px;
            border-radius: 10px;
            margin-bottom: 24px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .status-success {
            background: var(--success-bg);
            color: var(--success-text);
            border: 1px solid #bbf7d0;
        }

        .status-error {
            background: var(--danger-bg);
            color: var(--danger-text);
            border: 1px solid #fecaca;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 26px;
        }

        .stat-card {
            background: var(--card-bg);
            padding: 20px;
            border-radius: 10px;
            border: 1px solid var(--border);
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        }

        .stat-card h3 {
            font-size: 2rem;
            color: var(--primary);
            margin-bottom: 4px;
        }

        .stat-card p {
            font-size: 0.88rem;
            color: #64748b;
            font-weight: 500;
        }

        /* Table Card */
        .table-card {
            background: var(--card-bg);
            padding: 26px;
            border-radius: 12px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }

        .table-card h2 {
            font-size: 1.3rem;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        th, td {
            padding: 12px 14px;
            border-bottom: 1px solid #e2e8f0;
            text-align: left;
        }

        th {
            background: #f1f5f9;
            color: #334155;
            font-weight: 600;
        }

        tr:hover td {
            background: #f8fafc;
        }

        .pill {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            background: #dcfce7;
            color: #15803d;
        }

        .pill-wait {
            background: #fef3c7;
            color: #b45309;
        }

        .badge-cat {
            background: #e0f2fe;
            color: #0369a1;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 0.78rem;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="container">

    <header>
        <h1>Practical 8: Database Connection & Prepared Statements</h1>
        <p>PHP PDO Secure Connection &bull; 3NF Table Normalization &bull; SQL Prepared Statements</p>
    </header>

    <!-- Connection Status Banner -->
    <?php if ($connectionStatus): ?>
        <div class="status-box status-success">
            <span style="font-size:1.4rem;">✅</span>
            <div>
                <strong>Database Connected Successfully via PDO!</strong>
                <div style="font-size:0.86rem; color:#15803d;">Host: localhost:3306 &bull; Database: studenthub_db &bull; Driver: PDO_MYSQL</div>
            </div>
        </div>
    <?php else: ?>
        <div class="status-box status-error">
            <span style="font-size:1.4rem;">❌</span>
            <div>
                <strong>Database Connection Failed:</strong>
                <div><?= htmlspecialchars($errorMessage) ?></div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Database 3NF Tables Summary -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3><?= $totalStudents ?></h3>
            <p>Students Table (3NF)</p>
        </div>
        <div class="stat-card">
            <h3><?= $totalEvents ?></h3>
            <p>Events Table (3NF)</p>
        </div>
        <div class="stat-card">
            <h3><?= $totalRegistrations ?></h3>
            <p>Registrations Table (PK/FK)</p>
        </div>
    </div>

    <!-- Output Table (Joined Query using Prepared Statements) -->
    <div class="table-card">
        <h2>
            <span>Event Registration Roster (SQL Prepared Statement)</span>
            <small style="font-size:0.85rem; color:#64748b; font-weight:normal;">Query: <code>SELECT ... WHERE status = :status</code></small>
        </h2>

        <?php if (!empty($records)): ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Ticket No</th>
                            <th>Student Name</th>
                            <th>Roll No</th>
                            <th>Department</th>
                            <th>Event Title</th>
                            <th>Category</th>
                            <th>Venue</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $row): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($row['ticket_no']) ?></strong></td>
                                <td><?= htmlspecialchars($row['student_name']) ?></td>
                                <td><?= htmlspecialchars($row['roll_no']) ?></td>
                                <td><?= htmlspecialchars($row['department']) ?></td>
                                <td><?= htmlspecialchars($row['event_title']) ?></td>
                                <td><span class="badge-cat"><?= htmlspecialchars($row['event_category']) ?></span></td>
                                <td><?= htmlspecialchars($row['venue']) ?></td>
                                <td>
                                    <span class="pill <?= $row['status'] === 'Waitlisted' ? 'pill-wait' : '' ?>">
                                        <?= htmlspecialchars($row['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p style="text-align:center; padding:20px; color:#64748b;">No registration records retrieved.</p>
        <?php endif; ?>
    </div>

</div>

</body>
</html>
