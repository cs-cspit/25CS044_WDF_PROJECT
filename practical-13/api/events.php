<?php
/**
 * Practical 13: Centralized RESTful JSON API Router for Events
 * File: practical-13/api/events.php
 *
 * RESTful Operations:
 * - GET    /api/events.php          -> List all events or search (?search=...&category=...)
 * - GET    /api/events.php?id={id}  -> Retrieve single event details
 * - POST   /api/events.php          -> Create a new event (expects JSON or Form-data)
 * - PUT    /api/events.php          -> Update existing event (expects JSON payload)
 * - DELETE /api/events.php          -> Delete event (expects JSON payload or ?id={id})
 *
 * Features:
 * - Always returns valid JSON with proper HTTP status codes (200, 201, 400, 404, 500).
 * - Centralized CORS & error handling.
 * - Input validation before database operations.
 */

// 1. Set JSON and CORS Response Headers
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle pre-flight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 2. Include Database Connection
require_once __DIR__ . '/../db.php';

// Helper function to send standard JSON response
function send_json_response(bool $success, int $status_code, string $message, $data = null, array $errors = []): void {
    http_response_code($status_code);
    echo json_encode([
        'success'     => $success,
        'status_code' => $status_code,
        'message'     => $message,
        'data'        => $data,
        'errors'      => $errors,
        'timestamp'   => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// Parse incoming request body for PUT / DELETE or JSON POST
$raw_input = file_get_contents('php://input');
$json_data = json_decode($raw_input, true) ?? [];

// -----------------------------------------------------------------------------
// GET: LIST ALL EVENTS OR SEARCH
// -----------------------------------------------------------------------------
if ($method === 'GET') {
    // Single event lookup: ?id={id}
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $event_id = (int)$_GET['id'];
        $stmt = $conn->prepare("SELECT * FROM `events` WHERE `event_id` = ? LIMIT 1");
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row) {
            send_json_response(true, 200, "Event fetched successfully.", $row);
        } else {
            send_json_response(false, 404, "Event #$event_id not found.");
        }
    }

    // List & Search filter: ?search=...&category=...
    $search = trim($_GET['search'] ?? '');
    $category = trim($_GET['category'] ?? 'all');

    $sql = "SELECT * FROM `events` WHERE 1=1";
    $params = [];
    $types = "";

    if ($search !== '') {
        $sql .= " AND (`event_name` LIKE ? OR `venue` LIKE ? OR `organizer_club` LIKE ?)";
        $like = "%$search%";
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $types .= "sss";
    }

    if ($category !== 'all' && $category !== '') {
        $sql .= " AND `category` = ?";
        $params[] = $category;
        $types .= "s";
    }

    $sql .= " ORDER BY `event_date` ASC";

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    $stmt->close();

    send_json_response(true, 200, "Events retrieved successfully.", [
        'count'  => count($events),
        'events' => $events
    ]);
}

// -----------------------------------------------------------------------------
// POST: CREATE NEW EVENT
// -----------------------------------------------------------------------------
if ($method === 'POST') {
    // Accept either JSON payload or Form data
    $payload = !empty($json_data) ? $json_data : $_POST;

    $event_name = trim($payload['event_name'] ?? '');
    $category = trim($payload['category'] ?? 'Technical');
    $event_date = trim($payload['event_date'] ?? '');
    $venue = trim($payload['venue'] ?? '');
    $organizer_club = trim($payload['organizer_club'] ?? '');
    $max_seats = (int)($payload['max_seats'] ?? 100);
    $poster_path = trim($payload['poster_path'] ?? 'uploads/default_poster.png');
    $description = trim($payload['description'] ?? '');

    // Validation: Required fields
    $errors = [];
    if ($event_name === '') $errors[] = "Event name is required.";
    if ($event_date === '') $errors[] = "Event date is required.";
    if ($venue === '') $errors[] = "Venue is required.";
    if ($organizer_club === '') $errors[] = "Organizer club is required.";
    if ($max_seats <= 0) $errors[] = "Max seats must be greater than zero.";

    if (!empty($errors)) {
        send_json_response(false, 400, "Validation failed.", null, $errors);
    }

    try {
        $sql = "INSERT INTO `events` (`event_name`, `category`, `event_date`, `venue`, `organizer_club`, `max_seats`, `poster_path`, `description`, `status`) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssiss", $event_name, $category, $event_date, $venue, $organizer_club, $max_seats, $poster_path, $description);
        $stmt->execute();
        $new_id = $stmt->insert_id;
        $stmt->close();

        send_json_response(true, 201, "Event created successfully without page reload.", [
            'event_id'   => $new_id,
            'event_name' => $event_name,
            'category'   => $category,
            'event_date' => $event_date,
            'venue'      => $venue
        ]);
    } catch (Throwable $e) {
        send_json_response(false, 500, "Database insertion error: " . $e->getMessage());
    }
}

// -----------------------------------------------------------------------------
// PUT: UPDATE EXISTING EVENT
// -----------------------------------------------------------------------------
if ($method === 'PUT') {
    $payload = $json_data;

    $event_id = (int)($payload['event_id'] ?? 0);
    $event_name = trim($payload['event_name'] ?? '');
    $category = trim($payload['category'] ?? 'Technical');
    $event_date = trim($payload['event_date'] ?? '');
    $venue = trim($payload['venue'] ?? '');
    $organizer_club = trim($payload['organizer_club'] ?? '');
    $max_seats = (int)($payload['max_seats'] ?? 100);
    $description = trim($payload['description'] ?? '');

    if ($event_id <= 0) {
        send_json_response(false, 400, "Valid event_id is required for update.");
    }

    $errors = [];
    if ($event_name === '') $errors[] = "Event name cannot be empty.";
    if ($event_date === '') $errors[] = "Event date cannot be empty.";
    if ($venue === '') $errors[] = "Venue cannot be empty.";

    if (!empty($errors)) {
        send_json_response(false, 400, "Validation failed.", null, $errors);
    }

    try {
        // Verify existence first
        $chk = $conn->prepare("SELECT `event_id` FROM `events` WHERE `event_id` = ?");
        $chk->bind_param("i", $event_id);
        $chk->execute();
        if ($chk->get_result()->num_rows === 0) {
            $chk->close();
            send_json_response(false, 404, "Event #$event_id does not exist.");
        }
        $chk->close();

        $sql = "UPDATE `events` 
                SET `event_name` = ?, `category` = ?, `event_date` = ?, `venue` = ?, `organizer_club` = ?, `max_seats` = ?, `description` = ? 
                WHERE `event_id` = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssisi", $event_name, $category, $event_date, $venue, $organizer_club, $max_seats, $description, $event_id);
        $stmt->execute();
        $stmt->close();

        send_json_response(true, 200, "Event #$event_id updated successfully via AJAX.", [
            'event_id'   => $event_id,
            'event_name' => $event_name,
            'venue'      => $venue,
            'max_seats'  => $max_seats
        ]);
    } catch (Throwable $e) {
        send_json_response(false, 500, "Update failed: " . $e->getMessage());
    }
}

// -----------------------------------------------------------------------------
// DELETE: REMOVE EVENT
// -----------------------------------------------------------------------------
if ($method === 'DELETE') {
    $event_id = (int)($json_data['event_id'] ?? $_GET['id'] ?? 0);

    if ($event_id <= 0) {
        send_json_response(false, 400, "Invalid event ID provided for deletion.");
    }

    try {
        $stmt = $conn->prepare("DELETE FROM `events` WHERE `event_id` = ?");
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();

        if ($affected > 0) {
            send_json_response(true, 200, "Event #$event_id deleted successfully.");
        } else {
            send_json_response(false, 404, "Event #$event_id not found or already deleted.");
        }
    } catch (Throwable $e) {
        send_json_response(false, 500, "Deletion failed: " . $e->getMessage());
    }
}

// Fallback for unsupported methods
send_json_response(false, 405, "HTTP Method $method not allowed.");
