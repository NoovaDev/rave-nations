<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';

$currentRole = $_SESSION['role'];
$message = '';
$error = '';
$basePath = '/rave';

// ==========================================
// AJAX ENDPOINTS FOR EVENTS & PACKAGES
// ==========================================
if (isset($_GET['ajax_action'])) {
    header('Content-Type: application/json');
    $action = $_GET['ajax_action'];

    // 1. Fetch Single Event Details for Edit Modal
    if ($action === 'get_event' && isset($_GET['event_id'])) {
        $eventId = (int)$_GET['event_id'];
        $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute([$eventId]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($event ?: []);
        exit;
    }

    // 2. Fetch Packages for Event
    if ($action === 'get_packages' && isset($_GET['event_id'])) {
        $eventId = (int)$_GET['event_id'];
        $stmt = $pdo->prepare("SELECT * FROM event_packages WHERE event_id = ? ORDER BY id ASC");
        $stmt->execute([$eventId]);
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        exit;
    }

    // 3. Delete Package
    if ($action === 'delete_package' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $pkgId = (int)($_POST['package_id'] ?? 0);
        if ($pkgId > 0 && $currentRole === 'admin') {
            $stmt = $pdo->prepare("DELETE FROM event_packages WHERE id = ?");
            $success = $stmt->execute([$pkgId]);
            echo json_encode(['success' => $success]);
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }

    // 4. Update Package via AJAX
    if ($action === 'update_package' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $pkgId = (int)($_POST['package_id'] ?? 0);
        $name = trim($_POST['package_name'] ?? '');
        $price = (float)($_POST['ticket_price'] ?? 0);
        $benefits = trim($_POST['benefits'] ?? '');

        if ($pkgId > 0 && !empty($name) && $currentRole === 'admin') {
            try {
                $stmt = $pdo->prepare("UPDATE event_packages SET package_name = ?, ticket_price = ?, benefits = ? WHERE id = ?");
                $success = $stmt->execute([$name, $price, $benefits, $pkgId]);
                echo json_encode(['success' => $success]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid data provided']);
        }
        exit;
    }

    // 5. Delete Event (Automatically deletes physical poster file from server)
    if ($action === 'delete_event' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $eventId = (int)($_POST['event_id'] ?? 0);
        if ($eventId > 0 && $currentRole === 'admin') {
            try {
                // Fetch poster path to unlink file
                $stmtPoster = $pdo->prepare("SELECT poster_path FROM events WHERE id = ?");
                $stmtPoster->execute([$eventId]);
                $poster = $stmtPoster->fetchColumn();

                if ($poster) {
                    $fullPosterPath = __DIR__ . '/../' . $poster;
                    if (file_exists($fullPosterPath) && is_file($fullPosterPath)) {
                        @unlink($fullPosterPath);
                    }
                }

                $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
                $success = $stmt->execute([$eventId]);
                echo json_encode(['success' => $success]);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false]);
        }
        exit;
    }
}

// ==========================================
// POST HANDLERS FOR FORMS
// ==========================================

// Handle Update Event Form Submission (Automatically deletes old poster if replaced)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_event') {
    if ($currentRole === 'admin') {
        $eventId = (int)($_POST['event_id'] ?? 0);
        $eventName = trim($_POST['event_name'] ?? '');
        $eventDate = $_POST['event_date'] ?? '';
        $eventTime = $_POST['event_time'] ?? '20:00';
        $location = trim($_POST['location'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = $_POST['status'] ?? 'published';

        $posterPath = null;
        if (isset($_FILES['event_poster']) && $_FILES['event_poster']['error'] === UPLOAD_ERR_OK) {
            $fileTmp = $_FILES['event_poster']['tmp_name'];
            $fileName = $_FILES['event_poster']['name'];
            $fileSize = $_FILES['event_poster']['size'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (!in_array($fileExt, ['png', 'jpg', 'jpeg'])) {
                $error = "Invalid poster format! Only .png and .jpg files are allowed.";
            } elseif ($fileSize > 5 * 1024 * 1024) {
                $error = "Poster file size must be under 5MB.";
            } else {
                $imageInfo = @getimagesize($fileTmp);
                if ($imageInfo !== false) {
                    $newFileName = 'poster_' . time() . '.' . $fileExt;
                    $uploadDir = __DIR__ . '/../assets/images/events/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    if (move_uploaded_file($fileTmp, $uploadDir . $newFileName)) {
                        $posterPath = 'assets/images/events/' . $newFileName;

                        // Automatically fetch and delete the old poster file from server
                        $stmtOld = $pdo->prepare("SELECT poster_path FROM events WHERE id = ?");
                        $stmtOld->execute([$eventId]);
                        $oldPoster = $stmtOld->fetchColumn();

                        if ($oldPoster) {
                            $oldFullPath = __DIR__ . '/../' . $oldPoster;
                            if (file_exists($oldFullPath) && is_file($oldFullPath)) {
                                @unlink($oldFullPath);
                            }
                        }
                    }
                }
            }
        }

        if (empty($error) && $eventId > 0 && !empty($eventName)) {
            try {
                if ($posterPath) {
                    $stmt = $pdo->prepare("UPDATE events SET event_name = ?, location = ?, event_date = ?, event_time = ?, description = ?, poster_path = ?, status = ? WHERE id = ?");
                    $stmt->execute([$eventName, $location, $eventDate, $eventTime, $description, $posterPath, $status, $eventId]);
                } else {
                    $stmt = $pdo->prepare("UPDATE events SET event_name = ?, location = ?, event_date = ?, event_time = ?, description = ?, status = ? WHERE id = ?");
                    $stmt->execute([$eventName, $location, $eventDate, $eventTime, $description, $status, $eventId]);
                }
                $message = "Event details updated successfully!";
            } catch (Exception $e) {
                $error = "Failed to update event: " . $e->getMessage();
            }
        }
    }
}

// Handle Adding New Package via Modal Form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_package') {
    if ($currentRole === 'admin') {
        $eventId = (int)($_POST['event_id'] ?? 0);
        $packageName = trim($_POST['package_name'] ?? '');
        $ticketPrice = (float)($_POST['ticket_price'] ?? 0.00);
        $benefits = trim($_POST['benefits'] ?? '');

        if ($eventId > 0 && !empty($packageName)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO event_packages (event_id, package_name, ticket_price, benefits) VALUES (?, ?, ?, ?)");
                $stmt->execute([$eventId, $packageName, $ticketPrice, $benefits]);
                $message = "Package tier added successfully!";
            } catch (Exception $e) {
                $error = "Failed to add package: " . $e->getMessage();
            }
        }
    }
}

// Handle Creating New Event Wizard
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_event') {
    if ($currentRole === 'admin') {
        $eventName = trim($_POST['event_name'] ?? '');
        $eventDate = $_POST['event_date'] ?? '';
        $eventTime = $_POST['event_time'] ?? '20:00';
        $location = trim($_POST['location'] ?? '');
        $description = trim($_POST['description'] ?? '');

        $packageName = trim($_POST['package_name'] ?? 'Standard Pass');
        $ticketPrice = (float)($_POST['ticket_price'] ?? 0.00);
        $benefits = trim($_POST['benefits'] ?? '');

        $posterPath = null;
        if (isset($_FILES['event_poster']) && $_FILES['event_poster']['error'] === UPLOAD_ERR_OK) {
            $fileTmp = $_FILES['event_poster']['tmp_name'];
            $fileName = $_FILES['event_poster']['name'];
            $fileSize = $_FILES['event_poster']['size'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (!in_array($fileExt, ['png', 'jpg', 'jpeg'])) {
                $error = "Invalid format! Only .png and .jpg poster files are allowed.";
            } elseif ($fileSize > 5 * 1024 * 1024) {
                $error = "Poster file size must be under 5MB.";
            } else {
                $imageInfo = @getimagesize($fileTmp);
                if ($imageInfo !== false) {
                    $newFileName = 'poster_' . time() . '.' . $fileExt;
                    $uploadDir = __DIR__ . '/../assets/images/events/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    if (move_uploaded_file($fileTmp, $uploadDir . $newFileName)) {
                        $posterPath = 'assets/images/events/' . $newFileName;
                    }
                }
            }
        } else {
            $error = "Please upload an event poster image.";
        }

        if (empty($error) && !empty($eventName)) {
            try {
                $pdo->beginTransaction();

                $stmt = $pdo->prepare("INSERT INTO events (event_name, location, event_date, event_time, description, poster_path, status) VALUES (?, ?, ?, ?, ?, ?, 'published')");
                $stmt->execute([$eventName, $location, $eventDate, $eventTime, $description, $posterPath]);
                $eventId = (int)$pdo->lastInsertId();

                $stmtPkg = $pdo->prepare("INSERT INTO event_packages (event_id, package_name, ticket_price, benefits) VALUES (?, ?, ?, ?)");
                $stmtPkg->execute([$eventId, $packageName, $ticketPrice, $benefits]);

                $pdo->commit();
                $message = "Event & initial package created successfully! (ID: #{$eventId})";
            } catch (Exception $e) {
                $pdo->rollBack();
                $error = "Database error: " . $e->getMessage();
            }
        }
    }
}

// Fetch Events & Package Counts
$events = [];
try {
    $stmt = $pdo->query("
        SELECT e.*, COUNT(p.id) AS package_count 
        FROM events e 
        LEFT JOIN event_packages p ON e.id = p.event_id 
        GROUP BY e.id 
        ORDER BY e.event_date DESC
    ");
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Fetch events error: " . $e->getMessage());
}

// Preview Next Auto ID
$nextAutoId = 1;
try {
    $stmtId = $pdo->query("SHOW TABLE STATUS LIKE 'events'");
    $rowId = $stmtId->fetch(PDO::FETCH_ASSOC);
    if ($rowId && isset($rowId['Auto_increment'])) {
        $nextAutoId = (int)$rowId['Auto_increment'];
    }
} catch (PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management | Rave Nations</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="adminstyle/admin.css?v=14.1">
    <link rel="stylesheet" href="adminstyle/navbar.css?v=14.1">
    <link rel="stylesheet" href="adminstyle/header.css?v=14.1">
    <link rel="stylesheet" href="adminstyle/wizard.css?v=9.1">
    <link rel="stylesheet" href="adminstyle/events.css?v=4.1">
</head>
<body>

    <div class="rn-admin-layout">
        
        <?php include __DIR__ . '/include/navbar.php'; ?>

        <main class="rn-admin-main">

            <?php include __DIR__ . '/include/header.php'; ?>

            <div class="header-title-row">
                <div>
                    <h1>Event Management</h1>
                </div>
                <?php if ($currentRole === 'admin'): ?>
                    <button type="button" id="openWizardBtn" class="rn-btn-primary">
                        + Create New Event
                    </button>
                <?php endif; ?>
            </div>

            <?php if ($message): ?>
                <div style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-weight: 700;">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 12px 18px; border-radius: 12px; margin-bottom: 20px; font-weight: 700;">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- EVENTS DATA TABLE -->
            <section class="rn-admin-card">
                <div class="card-header">
                    <h2>All Events List</h2>
                </div>

                <div class="table-responsive">
                    <table class="rn-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Poster</th>
                                <th>Event Name</th>
                                <th>Location</th>
                                <th>Date & Time</th>
                                <th>Packages</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($events)): ?>
                                <?php foreach ($events as $event): ?>
                                    <tr id="event-row-<?= (int)$event['id'] ?>">
                                        <td>#<?= (int)$event['id'] ?></td>
                                        <td>
                                            <?php if (!empty($event['poster_path'])): ?>
                                                <img src="../<?= htmlspecialchars($event['poster_path']) ?>" alt="Poster" style="width: 38px; height: 38px; object-fit: cover; border-radius: 8px;">
                                            <?php else: ?>
                                                <span style="font-size:11px; color:#94a3b8;">No Image</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><strong><?= htmlspecialchars($event['event_name']) ?></strong></td>
                                        <td><?= htmlspecialchars($event['location']) ?></td>
                                        <td><?= htmlspecialchars($event['event_date']) ?> at <?= htmlspecialchars($event['event_time']) ?></td>
                                        <td><span class="status-pill" style="background: rgba(124, 58, 237, 0.1); color: var(--admin-purple);"><?= (int)$event['package_count'] ?> Tier(s)</span></td>
                                        <td>
                                            <span class="status-pill status-<?= htmlspecialchars($event['status']) ?>">
                                                <?= strtoupper(htmlspecialchars($event['status'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($currentRole === 'admin'): ?>
                                                <div style="display:flex; gap:6px; align-items:center;">
                                                    <button type="button" class="manage-packages-btn rn-btn-secondary" data-event-id="<?= (int)$event['id'] ?>" data-event-name="<?= htmlspecialchars($event['event_name']) ?>" title="Manage Packages">
                                                        ⚙️ Packages
                                                    </button>
                                                    <button type="button" class="edit-event-btn" data-event-id="<?= (int)$event['id'] ?>" title="Edit Event" style="background:none; border:none; cursor:pointer; font-size:16px;">✏️</button>
                                                    <button type="button" class="delete-event-btn" data-event-id="<?= (int)$event['id'] ?>" data-event-name="<?= htmlspecialchars($event['event_name']) ?>" title="Delete Event" style="background:none; border:none; cursor:pointer; font-size:16px;">🗑️</button>
                                                </div>
                                            <?php else: ?>
                                                <span style="font-size: 12px; color: var(--admin-text-muted);">View Only</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align:center; padding: 24px; color: var(--admin-text-muted);">No events found. Click "+ Create New Event" to add one!</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

        </main>
    </div>

    <!-- 1. MULTI-STEP CREATE EVENT WIZARD MODAL -->
    <div id="eventWizardModal" class="rn-modal-overlay">
        <div class="rn-modal-card">
            <div class="modal-header">
                <h3>Create New Event Wizard</h3>
                <button type="button" id="closeWizardBtn" class="modal-close-btn">&times;</button>
            </div>

            <div class="wizard-steps-indicator">
                <div id="pillStep1" class="wizard-step-pill active"></div>
                <div id="pillStep2" class="wizard-step-pill"></div>
            </div>

            <form action="events.php" method="POST" enctype="multipart/form-data" id="eventWizardForm">
                <input type="hidden" name="action" value="create_event">

                <!-- STEP 1: BASIC INFO -->
                <div id="wizardStep1" class="wizard-step-content active">
                    <div class="form-group">
                        <label>Auto-Generated Event ID</label>
                        <input type="text" class="form-control" value="#<?= $nextAutoId ?> (Auto Generated)" disabled style="opacity: 0.7; font-weight: bold;">
                    </div>

                    <div class="form-group">
                        <label>Event Name *</label>
                        <input type="text" id="event_name" name="event_name" class="form-control" placeholder="e.g. Rave Nations Beach Fest" required>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Event Date *</label>
                            <input type="date" id="event_date" name="event_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Event Time *</label>
                            <input type="time" id="event_time" name="event_time" class="form-control" value="20:00" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Location *</label>
                        <input type="text" id="event_location" name="location" class="form-control" placeholder="e.g. Mirissa Coastal Arena" required>
                    </div>

                    <div class="form-group">
                        <label>Event Poster (.PNG or .JPG only, Max 5MB) *</label>
                        <input type="file" id="event_poster" name="event_poster" class="form-control" accept=".png, .jpg, .jpeg" required>
                    </div>

                    <div class="form-group">
                        <label>Short Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Brief event overview..."></textarea>
                    </div>

                    <div class="modal-footer">
                        <button type="button" id="nextStepBtn" class="rn-btn-primary">Next: Initial Package ➔</button>
                    </div>
                </div>

                <!-- STEP 2: INITIAL PACKAGE -->
                <div id="wizardStep2" class="wizard-step-content">
                    <div style="background: var(--admin-bg); padding: 16px; border-radius: 14px; border: 1px solid var(--admin-border); margin-bottom: 20px;">
                        <strong style="font-size:13px; color:var(--admin-purple); display:block; margin-bottom:12px;">Initial Ticket Package Tier</strong>
                        
                        <div class="form-group">
                            <label>Package Name *</label>
                            <input type="text" name="package_name" class="form-control" placeholder="e.g. Standard Pass" value="Standard Pass" required>
                        </div>

                        <div class="form-group">
                            <label>Ticket Price (LKR) *</label>
                            <input type="number" step="0.01" name="ticket_price" class="form-control" placeholder="3500.00" value="3500.00" required>
                        </div>

                        <div class="form-group" style="margin-bottom:0;">
                            <label>Package Benefits / Description</label>
                            <textarea name="benefits" class="form-control" rows="3" placeholder="e.g. Main arena access, hydration stations"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" id="prevStepBtn" class="rn-btn-secondary">⬅ Back</button>
                        <button type="submit" id="submitEventBtn" class="rn-btn-primary">Save & Publish Event</button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <!-- 2. EDIT EVENT DETAILS MODAL -->
    <div id="editEventModal" class="rn-modal-overlay">
        <div class="rn-modal-card" style="max-width: 650px;">
            <div class="modal-header">
                <h3>✏️ Edit Event Details</h3>
                <button type="button" id="closeEditEventModalBtn" class="modal-close-btn">&times;</button>
            </div>

            <form action="events.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_event">
                <input type="hidden" name="event_id" id="edit_event_id">

                <div class="form-group">
                    <label>Event Name *</label>
                    <input type="text" id="edit_event_name" name="event_name" class="form-control" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Event Date *</label>
                        <input type="date" id="edit_event_date" name="event_date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Event Time *</label>
                        <input type="time" id="edit_event_time" name="event_time" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Location *</label>
                    <input type="text" id="edit_event_location" name="location" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Publication Status</label>
                    <select id="edit_event_status" name="status" class="form-control">
                        <option value="published">PUBLISHED</option>
                        <option value="draft">DRAFT</option>
                        <option value="cancelled">CANCELLED</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Replace Poster (.PNG / .JPG optional) <span style="font-size: 11px; color: var(--admin-text-muted);">(Uploading a new poster will automatically delete the old one)</span></label>
                    <input type="file" name="event_poster" class="form-control" accept=".png, .jpg, .jpeg">
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea id="edit_event_description" name="description" class="form-control" rows="3"></textarea>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="rn-btn-primary">Update Event Details</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 3. PACKAGE MANAGEMENT MODAL -->
    <div id="managePackagesModal" class="rn-modal-overlay">
        <div class="rn-modal-card" style="max-width: 680px;">
            <div class="modal-header">
                <h3 id="manageModalTitle">Manage Event Packages</h3>
                <button type="button" id="closeManageModalBtn" class="modal-close-btn">&times;</button>
            </div>

            <!-- Package Card Grid -->
            <div style="margin-bottom: 20px;">
                <h4 style="font-size:12px; font-weight:800; color:var(--admin-text-muted); margin-bottom:12px; text-transform:uppercase; letter-spacing:0.5px;">Current Package Tiers</h4>
                <div id="existingPackagesList" class="pkg-card-grid">
                    <!-- Cards dynamically rendered via JS -->
                </div>
            </div>

            <!-- Add / Edit Package Form -->
            <form action="events.php" method="POST" id="packageForm" style="border-top: 1px solid var(--admin-border); padding-top: 16px;">
                <input type="hidden" name="action" id="packageFormAction" value="add_package">
                <input type="hidden" name="package_id" id="managePkgIdInput">
                <input type="hidden" name="event_id" id="manageEventIdInput">

                <h4 id="formSectionTitle" style="font-size:13px; font-weight:800; color:var(--admin-purple); margin-bottom:12px; text-transform:uppercase;">+ Add New Package Tier</h4>

                <div class="form-row">
                    <div class="form-group">
                        <label>Package Name *</label>
                        <input type="text" id="pkg_name_input" name="package_name" class="form-control" placeholder="e.g. VIP Backstage Pass" required>
                    </div>
                    <div class="form-group">
                        <label>Ticket Price (LKR) *</label>
                        <input type="number" step="0.01" id="pkg_price_input" name="ticket_price" class="form-control" placeholder="7500.00" value="0.00" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Package Benefits / Description</label>
                    <textarea id="pkg_benefits_input" name="benefits" class="form-control" rows="2" placeholder="e.g. Backstage access, complimentary drinks"></textarea>
                </div>

                <div class="modal-footer" style="margin-top:0; padding-top:14px;">
                    <button type="button" id="cancelEditBtn" class="rn-btn-secondary" style="display:none;">Cancel Edit</button>
                    <button type="submit" id="savePkgSubmitBtn" class="rn-btn-primary">Add Package Tier</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MAIN JAVASCRIPT LOGIC -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const wizardModal = document.getElementById('eventWizardModal');
            const openWizardBtn = document.getElementById('openWizardBtn');
            const closeWizardBtn = document.getElementById('closeWizardBtn');
            const nextBtn = document.getElementById('nextStepBtn');
            const prevBtn = document.getElementById('prevStepBtn');
            const step1 = document.getElementById('wizardStep1');
            const step2 = document.getElementById('wizardStep2');
            const pill1 = document.getElementById('pillStep1');
            const pill2 = document.getElementById('pillStep2');

            if (openWizardBtn) openWizardBtn.addEventListener('click', () => wizardModal.classList.add('active'));
            if (closeWizardBtn) closeWizardBtn.addEventListener('click', () => wizardModal.classList.remove('active'));

            if (nextBtn) {
                nextBtn.addEventListener('click', () => {
                    const eventName = document.getElementById('event_name').value.trim();
                    const eventDate = document.getElementById('event_date').value;
                    const eventLocation = document.getElementById('event_location').value.trim();
                    const posterInput = document.getElementById('event_poster');

                    if (!eventName || !eventDate || !eventLocation) {
                        alert('Please fill out all required fields in Step 1.');
                        return;
                    }

                    if (posterInput.files && posterInput.files[0]) {
                        const file = posterInput.files[0];
                        const ext = file.name.split('.').pop().toLowerCase();
                        if (ext !== 'png' && ext !== 'jpg' && ext !== 'jpeg') {
                            alert('Only .png and .jpg poster files are allowed.');
                            return;
                        }
                        if (file.size > 5 * 1024 * 1024) {
                            alert('Poster file size must be under 5MB.');
                            return;
                        }
                    } else {
                        alert('Please upload an event poster image.');
                        return;
                    }

                    step1.classList.remove('active');
                    step2.classList.add('active');
                    pill1.classList.remove('active');
                    pill2.classList.add('active');
                });
            }

            if (prevBtn) {
                prevBtn.addEventListener('click', () => {
                    step2.classList.remove('active');
                    step1.classList.add('active');
                    pill2.classList.remove('active');
                    pill1.classList.add('active');
                });
            }

            const editEventModal = document.getElementById('editEventModal');
            const closeEditEventModalBtn = document.getElementById('closeEditEventModalBtn');

            if (closeEditEventModalBtn) closeEditEventModalBtn.addEventListener('click', () => editEventModal.classList.remove('active'));

            document.querySelectorAll('.edit-event-btn').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const eventId = btn.getAttribute('data-event-id');
                    try {
                        const res = await fetch(`events.php?ajax_action=get_event&event_id=${eventId}`);
                        const event = await res.json();
                        if (event && event.id) {
                            document.getElementById('edit_event_id').value = event.id;
                            document.getElementById('edit_event_name').value = event.event_name;
                            document.getElementById('edit_event_date').value = event.event_date;
                            document.getElementById('edit_event_time').value = event.event_time;
                            document.getElementById('edit_event_location').value = event.location;
                            document.getElementById('edit_event_status').value = event.status;
                            document.getElementById('edit_event_description').value = event.description || '';
                            editEventModal.classList.add('active');
                        }
                    } catch (err) {
                        alert('Failed to load event details.');
                    }
                });
            });

            document.querySelectorAll('.delete-event-btn').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const eventId = btn.getAttribute('data-event-id');
                    const eventName = btn.getAttribute('data-event-name');

                    if (!confirm(`Are you sure you want to delete "${eventName}" (#${eventId})? This will also remove all associated packages and poster files.`)) {
                        return;
                    }

                    const formData = new FormData();
                    formData.append('event_id', eventId);

                    try {
                        const res = await fetch('events.php?ajax_action=delete_event', {
                            method: 'POST',
                            body: formData
                        });
                        const data = await res.json();
                        if (data.success) {
                            const row = document.getElementById(`event-row-${eventId}`);
                            if (row) row.remove();
                        } else {
                            alert('Failed to delete event: ' + (data.error || 'Unknown error'));
                        }
                    } catch (err) {
                        alert('Error communicating with server.');
                    }
                });
            });

            const manageModal = document.getElementById('managePackagesModal');
            const closeManageBtn = document.getElementById('closeManageModalBtn');
            const manageModalTitle = document.getElementById('manageModalTitle');
            const manageEventIdInput = document.getElementById('manageEventIdInput');
            const managePkgIdInput = document.getElementById('managePkgIdInput');
            const packageFormAction = document.getElementById('packageFormAction');
            const existingPackagesList = document.getElementById('existingPackagesList');

            const pkgForm = document.getElementById('packageForm');
            const formSectionTitle = document.getElementById('formSectionTitle');
            const nameInput = document.getElementById('pkg_name_input');
            const priceInput = document.getElementById('pkg_price_input');
            const benefitsInput = document.getElementById('pkg_benefits_input');
            const cancelEditBtn = document.getElementById('cancelEditBtn');
            const saveSubmitBtn = document.getElementById('savePkgSubmitBtn');

            let activeEventId = null;

            if (closeManageBtn) closeManageBtn.addEventListener('click', () => manageModal.classList.remove('active'));

            const resetForm = () => {
                packageFormAction.value = 'add_package';
                managePkgIdInput.value = '';
                formSectionTitle.textContent = '+ Add New Package Tier';
                saveSubmitBtn.textContent = 'Add Package Tier';
                cancelEditBtn.style.display = 'none';
                nameInput.value = '';
                priceInput.value = '0.00';
                benefitsInput.value = '';
            };

            cancelEditBtn.addEventListener('click', resetForm);

            const loadPackages = async (eventId) => {
                existingPackagesList.innerHTML = '<div style="color:var(--admin-text-muted); font-size:13px; text-align:center; padding:16px;">Loading packages...</div>';
                try {
                    const res = await fetch(`events.php?ajax_action=get_packages&event_id=${eventId}`);
                    const packages = await res.json();

                    if (packages.length > 0) {
                        existingPackagesList.innerHTML = packages.map(pkg => `
                            <div class="pkg-card" id="pkg-card-${pkg.id}">
                                <div class="pkg-card-header">
                                    <span class="pkg-card-title">${escapeHtml(pkg.package_name)}</span>
                                    <span class="pkg-card-price">LKR ${parseFloat(pkg.ticket_price).toLocaleString(undefined, {minimumFractionDigits: 2})}</span>
                                </div>
                                <div class="pkg-card-benefits">${escapeHtml(pkg.benefits || 'No specific benefits listed.')}</div>
                                <div class="pkg-card-actions">
                                    <button type="button" class="btn-pkg-action btn-pkg-edit" 
                                        data-id="${pkg.id}" 
                                        data-name="${escapeHtml(pkg.package_name)}" 
                                        data-price="${pkg.ticket_price}" 
                                        data-benefits="${escapeHtml(pkg.benefits || '')}"
                                        onclick="triggerEditPackage(this)">✏️ Edit</button>
                                    <button type="button" class="btn-pkg-action btn-pkg-delete" onclick="deletePackage(${pkg.id})">🗑️ Delete</button>
                                </div>
                            </div>
                        `).join('');
                    } else {
                        existingPackagesList.innerHTML = '<div style="color:var(--admin-text-muted); font-size:13px; text-align:center; padding:16px;">No package tiers created yet.</div>';
                    }
                } catch (err) {
                    existingPackagesList.innerHTML = '<div style="color:#ef4444; font-size:13px; text-align:center; padding:16px;">Failed to load packages.</div>';
                }
            };

            document.querySelectorAll('.manage-packages-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    activeEventId = btn.getAttribute('data-event-id');
                    const eventName = btn.getAttribute('data-event-name');

                    manageModalTitle.textContent = `Manage Packages: ${eventName} (#${activeEventId})`;
                    manageEventIdInput.value = activeEventId;
                    resetForm();
                    manageModal.classList.add('active');
                    loadPackages(activeEventId);
                });
            });

            window.triggerEditPackage = (btn) => {
                const id = btn.getAttribute('data-id');
                const name = btn.getAttribute('data-name');
                const price = btn.getAttribute('data-price');
                const benefits = btn.getAttribute('data-benefits');

                packageFormAction.value = 'update_package';
                managePkgIdInput.value = id;
                formSectionTitle.textContent = `✏️ Edit Package Tier (#${id})`;
                saveSubmitBtn.textContent = 'Update Package Tier';
                cancelEditBtn.style.display = 'inline-block';
                nameInput.value = name;
                priceInput.value = price;
                benefitsInput.value = benefits;
            };

            window.deletePackage = async (id) => {
                if (!confirm('Are you sure you want to delete this package tier?')) return;
                
                const formData = new FormData();
                formData.append('package_id', id);

                const res = await fetch('events.php?ajax_action=delete_package', {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();
                if (data.success) {
                    loadPackages(activeEventId);
                } else {
                    alert('Failed to delete package.');
                }
            };

            pkgForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(pkgForm);
                const isUpdate = packageFormAction.value === 'update_package';
                const endpoint = isUpdate ? 'events.php?ajax_action=update_package' : 'events.php';

                try {
                    const res = await fetch(endpoint, {
                        method: 'POST',
                        body: formData
                    });

                    if (isUpdate) {
                        const data = await res.json();
                        if (data.success) {
                            resetForm();
                            loadPackages(activeEventId);
                        } else {
                            alert('Failed to update package: ' + (data.error || 'Unknown error'));
                        }
                    } else {
                        pkgForm.submit();
                    }
                } catch (err) {
                    alert('Error communicating with server.');
                }
            });

            function escapeHtml(text) {
                if (!text) return '';
                return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
            }

            window.addEventListener('click', (e) => {
                if (e.target === wizardModal) wizardModal.classList.remove('active');
                if (e.target === editEventModal) editEventModal.classList.remove('active');
                if (e.target === manageModal) manageModal.classList.remove('active');
            });
        });
    </script>
</body>
</html>
```eof

### How this works:
1. **Replacing a Poster:** When you edit an event and upload a new poster file, the backend checks for the old poster path in the database, unlinks/deletes the old file from `assets/images/events/`, and replaces it with your new upload.
2. **Deleting an Event:** When you click the delete button for any event, the database record, any associated ticket package tiers, and the physical event poster file stored in your server folder are **all automatically deleted together**.