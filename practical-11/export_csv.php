<?php
/**
 * Practical 11: Export Active & Filtered Student Records to CSV (Excel compatible)
 * File: practical-11/export_csv.php
 */

require_once __DIR__ . '/db.php';

$search = trim($_GET['search'] ?? '');
$course = trim($_GET['course'] ?? '');
$status = trim($_GET['status'] ?? 'all'); // 'all', 'active', 'inactive'

$sql = "SELECT student_id, roll_no, full_name, email, phone, department, course, year_of_study, status, created_at 
        FROM `students` 
        WHERE 1=1";
$params = [];
$types = "";

if ($search !== '') {
    $sql .= " AND (full_name LIKE ? OR roll_no LIKE ? OR email LIKE ?)";
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= "sss";
}

if ($course !== '' && $course !== 'all') {
    $sql .= " AND course = ?";
    $params[] = $course;
    $types .= "s";
}

if ($status !== 'all') {
    $sql .= " AND status = ?";
    $params[] = $status;
    $types .= "s";
}

$sql .= " ORDER BY student_id ASC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$filename = "StudentHub_Students_Export_" . date('Y-m-d_His') . ".csv";

// Output headers to trigger download and Excel compatibility
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// UTF-8 BOM for Microsoft Excel to correctly display characters
fputs($output, "\xEF\xBB\xBF");

// Column Headers
fputcsv($output, [
    'Student ID',
    'Roll Number',
    'Full Name',
    'College Email',
    'Phone Number',
    'Department',
    'Course',
    'Year of Study',
    'Status',
    'Registration Date'
]);

// Rows
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['student_id'],
        $row['roll_no'],
        $row['full_name'],
        $row['email'],
        $row['phone'],
        $row['department'],
        $row['course'],
        $row['year_of_study'],
        ucfirst($row['status']),
        $row['created_at']
    ]);
}

fclose($output);
exit;
