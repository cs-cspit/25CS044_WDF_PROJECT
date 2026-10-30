<?php
/**
 * Practical 12: Admin Dashboard - Event Creation, Poster Upload, CRUD & RBAC
 * File: practical-12/admin_dashboard.php
 */

require_once __DIR__ . '/auth_admin.php';
require_once __DIR__ . '/db.php';

// Strict RBAC Enforcement
check_admin_access();

$admin_name = $_SESSION['full_name'];
$admin_email = $_SESSION['email'];

$success_msg = $_SESSION['success_msg'] ?? null;
$error_msg = $_SESSION['error_msg'] ?? null;
unset($_SESSION['success_msg'], $_SESSION['error_msg']);

// UPLOAD CONFIGURATION
$upload_dir = __DIR__ . '/uploads/';
$allowed_mime_types = ['image/jpeg', 'image/png', 'image/webp'];
$allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
$max_file_size = 2 * 1024 * 1024; // 2 MB limit

// -----------------------------------------------------------------------------
// POST HANDLER: CREATE / UPDATE / DELETE EVENT
// -----------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. CREATE EVENT WITH FILE UPLOAD
    if ($action === 'create_event') {
        $event_name = trim($_POST['event_name'] ?? '');
        $category = trim($_POST['category'] ?? 'Technical');
        $event_date = trim($_POST['event_date'] ?? '');
        $venue = trim($_POST['venue'] ?? '');
        $organizer_club = trim($_POST['organizer_club'] ?? '');
        $max_seats = (int)($_POST['max_seats'] ?? 100);
        $description = trim($_POST['description'] ?? '');

        // Validation: Empty check
        if ($event_name === '' || $event_date === '' || $venue === '' || $organizer_club === '') {
            $_SESSION['error_msg'] = "Please fill in all required event details.";
            header("Location: admin_dashboard.php");
            exit;
        }

        $poster_rel_path = 'uploads/default_poster.png';

        // FILE UPLOAD VALIDATION
        if (isset($_FILES['poster']) && $_FILES['poster']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['poster'];

            // Check for upload error
            if ($file['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['error_msg'] = "File upload failed with error code: " . $file['error'];
                header("Location: admin_dashboard.php");
                exit;
            }

            // A. File Size Limit Validation (2 MB)
            if ($file['size'] > $max_file_size) {
                $_SESSION['error_msg'] = "File size limit exceeded: The poster must not exceed 2 MB. Uploaded file was " . round($file['size'] / 1024 / 1024, 2) . " MB.";
                header("Location: admin_dashboard.php");
                exit;
            }

            // B. Invalid File Type Validation (Extension & MIME Check)
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($ext, $allowed_extensions, true) || !in_array($mime, $allowed_mime_types, true)) {
                $_SESSION['error_msg'] = "Invalid file type validation: Only JPG, PNG, and WebP image files are allowed. Detected: '$ext' ($mime).";
                header("Location: admin_dashboard.php");
                exit;
            }

            // C. Valid Poster Upload: Generate cryptographically unique filename
            $unique_name = 'poster_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $dest_path = $upload_dir . $unique_name;

            if (move_uploaded_file($file['tmp_name'], $dest_path)) {
                $poster_rel_path = 'uploads/' . $unique_name;
            } else {
                $_SESSION['error_msg'] = "Failed to store uploaded poster in filesystem.";
                header("Location: admin_dashboard.php");
                exit;
            }
        }

        // Insert into database via Prepared Statement
        try {
            $sql = "INSERT INTO `events` (`event_name`, `category`, `event_date`, `venue`, `organizer_club`, `max_seats`, `poster_path`, `description`, `status`) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssiss", $event_name, $category, $event_date, $venue, $organizer_club, $max_seats, $poster_rel_path, $description);
            $stmt->execute();
            $stmt->close();

            $_SESSION['success_msg'] = "Successful event creation: '$event_name' scheduled for $event_date with poster uploaded.";
        } catch (Throwable $e) {
            $_SESSION['error_msg'] = "Database error while creating event: " . $e->getMessage();
        }

        header("Location: admin_dashboard.php");
        exit;
    }

    // 2. UPDATE EVENT
    if ($action === 'update_event') {
        $event_id = (int)($_POST['event_id'] ?? 0);
        $event_name = trim($_POST['event_name'] ?? '');
        $category = trim($_POST['category'] ?? 'Technical');
        $event_date = trim($_POST['event_date'] ?? '');
        $venue = trim($_POST['venue'] ?? '');
        $organizer_club = trim($_POST['organizer_club'] ?? '');
        $max_seats = (int)($_POST['max_seats'] ?? 100);
        $description = trim($_POST['description'] ?? '');

        if ($event_id <= 0 || $event_name === '') {
            $_SESSION['error_msg'] = "Invalid event ID or title for update.";
            header("Location: admin_dashboard.php");
            exit;
        }

        // Check if an updated poster file was supplied
        $new_poster = null;
        if (isset($_FILES['poster']) && $_FILES['poster']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['poster'];
            if ($file['size'] > $max_file_size) {
                $_SESSION['error_msg'] = "File size limit exceeded: Poster must be under 2 MB.";
                header("Location: admin_dashboard.php");
                exit;
            }
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowed_extensions, true)) {
                $_SESSION['error_msg'] = "Invalid file type: Only JPG, PNG, and WebP are allowed.";
                header("Location: admin_dashboard.php");
                exit;
            }
            $unique_name = 'poster_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            if (move_uploaded_file($file['tmp_name'], $upload_dir . $unique_name)) {
                $new_poster = 'uploads/' . $unique_name;
            }
        }

        try {
            if ($new_poster) {
                $sql = "UPDATE `events` 
                        SET `event_name` = ?, `category` = ?, `event_date` = ?, `venue` = ?, `organizer_club` = ?, `max_seats` = ?, `poster_path` = ?, `description` = ? 
                        WHERE `event_id` = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssissi", $event_name, $category, $event_date, $venue, $organizer_club, $max_seats, $new_poster, $description, $event_id);
            } else {
                $sql = "UPDATE `events` 
                        SET `event_name` = ?, `category` = ?, `event_date` = ?, `venue` = ?, `organizer_club` = ?, `max_seats` = ?, `description` = ? 
                        WHERE `event_id` = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssssisi", $event_name, $category, $event_date, $venue, $organizer_club, $max_seats, $description, $event_id);
            }
            $stmt->execute();
            $stmt->close();

            $_SESSION['success_msg'] = "Successful event update: '$event_name' details refreshed successfully.";
        } catch (Throwable $e) {
            $_SESSION['error_msg'] = "Update failed: " . $e->getMessage();
        }

        header("Location: admin_dashboard.php");
        exit;
    }

    // 3. DELETE EVENT
    if ($action === 'delete_event') {
        $event_id = (int)($_POST['event_id'] ?? 0);
        if ($event_id > 0) {
            try {
                // Fetch poster to unlink if not default
                $s = $conn->prepare("SELECT poster_path FROM `events` WHERE `event_id` = ?");
                $s->bind_param("i", $event_id);
                $s->execute();
                $p_row = $s->get_result()->fetch_assoc();
                $s->close();

                $del = $conn->prepare("DELETE FROM `events` WHERE `event_id` = ?");
                $del->bind_param("i", $event_id);
                $del->execute();
                $del->close();

                $_SESSION['success_msg'] = "Successful event deletion: Event #$event_id was permanently removed from database.";
            } catch (Throwable $e) {
                $_SESSION['error_msg'] = "Deletion failed: " . $e->getMessage();
            }
        }
        header("Location: admin_dashboard.php");
        exit;
    }
}

// FETCH ALL EVENTS FOR LISTING
$events = [];
$res = $conn->query("SELECT * FROM `events` ORDER BY event_date ASC");
while ($row = $res->fetch_assoc()) {
    $events[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Practical 12: Event Poster Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../practical-10/style.css">
    <style>
        .page-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 12px;
        }

        .btn-create {
            background: #dc2626;
            color: #ffffff;
            padding: 9px 18px;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
        }
        .btn-create:hover { background: #b91c1c; }

        .poster-thumb {
            width: 72px;
            height: 48px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid var(--border);
            background: #f1f5f9;
            display: block;
        }

        .poster-placeholder {
            width: 72px;
            height: 48px;
            border-radius: 6px;
            background: #eff6ff;
            color: #1d4ed8;
            font-size: 0.72rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            border: 1px dashed #bfdbfe;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.6);
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal.active { display: flex; }

        .modal-content {
            background: #ffffff;
            border-radius: 12px;
            padding: 28px 32px;
            max-width: 580px;
            width: 100%;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--border);
        }

        .close-btn { font-size: 1.5rem; background: none; border: none; cursor: pointer; color: var(--text-muted); }

        .file-hint {
            font-size: 0.78rem;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .btn-action-sm {
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }
        .btn-edit { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .btn-del { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="admin_dashboard.php" class="navbar-brand">🛡️ StudentHub Admin Dashboard</a>
    <div class="navbar-user">
        <span>Logged in as: <strong><?= htmlspecialchars($admin_name, ENT_QUOTES, 'UTF-8') ?></strong></span>
        <span class="badge badge-admin">ADMIN</span>
        <a href="login.php?msg=logged_out" class="btn-logout">Logout</a>
    </div>
</nav>

<div class="container">

    <div class="page-bar">
        <div>
            <h1 style="font-size: 1.8rem; color: var(--text-main);">Campus Event Management</h1>
            <p style="color: var(--text-muted); font-size: 0.92rem;">Practical 12: Event Creation, Poster Uploads, Validation &amp; Access Control</p>
        </div>
        <button class="btn-create" onclick="openCreateModal()">
            ➕ Create New Event
        </button>
    </div>

    <!-- Success and Error Message Banners -->
    <?php if (!empty($success_msg)): ?>
        <div class="alert alert-success">
            <span style="font-size: 1.3rem;">✅</span>
            <div><?= htmlspecialchars($success_msg, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_msg)): ?>
        <div class="alert alert-danger">
            <span style="font-size: 1.3rem;">⚠️</span>
            <div><?= htmlspecialchars($error_msg, ENT_QUOTES, 'UTF-8') ?></div>
        </div>
    <?php endif; ?>

    <!-- Event List with Uploaded Posters -->
    <div class="card">
        <div class="card-title">
            <h2>Event List with Uploaded Posters</h2>
            <small>Total Events: <?= count($events) ?></small>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Poster</th>
                        <th>Event Name</th>
                        <th>Category</th>
                        <th>Date</th>
                        <th>Venue</th>
                        <th>Organizer Club</th>
                        <th>Capacity</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($events)): ?>
                        <?php foreach ($events as $ev): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($ev['poster_path']) && file_exists(__DIR__ . '/' . $ev['poster_path'])): ?>
                                        <img src="<?= htmlspecialchars($ev['poster_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Event Poster" class="poster-thumb">
                                    <?php else: ?>
                                        <div class="poster-placeholder">
                                            🖼️ <?= htmlspecialchars(substr($ev['event_name'], 0, 10)) ?>...
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><strong><?= htmlspecialchars($ev['event_name'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                                <td><span class="badge badge-purple"><?= htmlspecialchars($ev['category'], ENT_QUOTES, 'UTF-8') ?></span></td>
                                <td>📅 <?= htmlspecialchars($ev['event_date'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td>📍 <?= htmlspecialchars($ev['venue'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($ev['organizer_club'], ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars((string)$ev['max_seats'], ENT_QUOTES, 'UTF-8') ?> seats</td>
                                <td>
                                    <button 
                                        type="button" 
                                        class="btn-action-sm btn-edit"
                                        onclick='openEditModal(<?= json_encode($ev) ?>)'
                                    >
                                        Edit
                                    </button>
                                    <form method="POST" action="admin_dashboard.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to permanently delete event \'<?= addslashes($ev['event_name']) ?>\'?');">
                                        <input type="hidden" name="action" value="delete_event">
                                        <input type="hidden" name="event_id" value="<?= $ev['event_id'] ?>">
                                        <button type="submit" class="btn-action-sm btn-del">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="8" style="text-align:center; padding:24px; color:var(--text-muted);">No campus events found. Click "Create New Event" to publish one.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Modal 1: Create Event with Poster Upload -->
<div id="createModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Create New Event (with Poster Upload)</h3>
            <button class="close-btn" onclick="closeCreateModal()">&times;</button>
        </div>
        <form method="POST" action="admin_dashboard.php" enctype="multipart/form-data" autocomplete="off">
            <input type="hidden" name="action" value="create_event">

            <div class="form-group">
                <label class="form-label">Event Name *</label>
                <input type="text" name="event_name" class="form-control" placeholder="e.g. AI & Deep Learning Symposium" required>
            </div>

            <div class="form-group">
                <label class="form-label">Category *</label>
                <select name="category" class="form-control" required>
                    <option value="Technical">Technical</option>
                    <option value="Workshop">Workshop</option>
                    <option value="Seminar">Seminar</option>
                    <option value="Competition">Competition</option>
                    <option value="Cultural">Cultural</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Event Date *</label>
                <input type="date" name="event_date" class="form-control" value="2026-08-25" required>
            </div>

            <div class="form-group">
                <label class="form-label">Venue *</label>
                <input type="text" name="venue" class="form-control" placeholder="e.g. Seminar Hall B, IT Building" required>
            </div>

            <div class="form-group">
                <label class="form-label">Organizer Club *</label>
                <input type="text" name="organizer_club" class="form-control" placeholder="e.g. Google Developer Student Club" required>
            </div>

            <div class="form-group">
                <label class="form-label">Max Capacity (Seats) *</label>
                <input type="number" name="max_seats" class="form-control" value="120" min="10" max="1000" required>
            </div>

            <!-- Poster File Upload Field -->
            <div class="form-group">
                <label class="form-label">Event Poster Image (File Upload) *</label>
                <input type="file" name="poster" class="form-control" accept="image/jpeg,image/png,image/webp">
                <div class="file-hint">Allowed file formats: <strong>JPG, PNG, WebP</strong> &bull; Max file size: <strong>2 MB</strong></div>
            </div>

            <div class="form-group">
                <label class="form-label">Event Description</label>
                <textarea name="description" class="form-control" rows="2" placeholder="Brief outline of the event..."></textarea>
            </div>

            <button type="submit" class="btn-primary" style="background:#dc2626; margin-top:8px;">Publish Event</button>
        </form>
    </div>
</div>

<!-- Modal 2: Edit Event Details -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Update Event Details</h3>
            <button class="close-btn" onclick="closeEditModal()">&times;</button>
        </div>
        <form method="POST" action="admin_dashboard.php" enctype="multipart/form-data" autocomplete="off">
            <input type="hidden" name="action" value="update_event">
            <input type="hidden" id="edit_event_id" name="event_id">

            <div class="form-group">
                <label class="form-label">Event Name *</label>
                <input type="text" id="edit_event_name" name="event_name" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">Category *</label>
                <select id="edit_category" name="category" class="form-control" required>
                    <option value="Technical">Technical</option>
                    <option value="Workshop">Workshop</option>
                    <option value="Seminar">Seminar</option>
                    <option value="Competition">Competition</option>
                    <option value="Cultural">Cultural</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Event Date *</label>
                <input type="date" id="edit_event_date" name="event_date" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">Venue *</label>
                <input type="text" id="edit_venue" name="venue" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">Organizer Club *</label>
                <input type="text" id="edit_organizer_club" name="organizer_club" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">Max Capacity *</label>
                <input type="number" id="edit_max_seats" name="max_seats" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">Replace Poster (Optional)</label>
                <input type="file" name="poster" class="form-control" accept="image/jpeg,image/png,image/webp">
                <div class="file-hint">Leave blank to retain existing poster image.</div>
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea id="edit_description" name="description" class="form-control" rows="2"></textarea>
            </div>

            <button type="submit" class="btn-primary" style="background:#dc2626; margin-top:8px;">Save Changes</button>
        </form>
    </div>
</div>

<script>
function openCreateModal() { document.getElementById('createModal').classList.add('active'); }
function closeCreateModal() { document.getElementById('createModal').classList.remove('active'); }
function openEditModal(ev) {
    document.getElementById('edit_event_id').value = ev.event_id;
    document.getElementById('edit_event_name').value = ev.event_name;
    document.getElementById('edit_category').value = ev.category;
    document.getElementById('edit_event_date').value = ev.event_date;
    document.getElementById('edit_venue').value = ev.venue;
    document.getElementById('edit_organizer_club').value = ev.organizer_club;
    document.getElementById('edit_max_seats').value = ev.max_seats;
    document.getElementById('edit_description').value = ev.description || '';
    document.getElementById('editModal').classList.add('active');
}
function closeEditModal() { document.getElementById('editModal').classList.remove('active'); }
window.onclick = function(e) {
    if (e.target == document.getElementById('createModal')) closeCreateModal();
    if (e.target == document.getElementById('editModal')) closeEditModal();
}
</script>

</body>
</html>
