<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/include/event_functions.php';

$currentRole = $_SESSION['role'];
$message = '';
$error = '';

if (isset($_GET['ajax_action'])) {
    handleEventAjaxRequests($pdo, $currentRole);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    if ($action === 'update_event') {
        $result = handleUpdateEvent($pdo, $currentRole);
    } elseif ($action === 'add_package') {
        $result = handleAddPackage($pdo, $currentRole);
    } elseif ($action === 'create_event') {
        $result = handleCreateEvent($pdo, $currentRole);
    } else {
        $result = [];
    }
    $message = $result['message'] ?? '';
    $error   = $result['error'] ?? '';
}

$events = fetchAllEventsWithCounts($pdo);
$nextAutoId = fetchNextAutoIncrementId($pdo);
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
    <script src="js/admin-wizard.js?v=2.0" defer></script>
    <script src="js/events-manager.js?v=2.0" defer></script>
</head>
<body>

    <div class="rn-admin-layout">
        <?php include __DIR__ . '/include/navbar.php'; ?>
        <main class="rn-admin-main">
            <?php include __DIR__ . '/include/header.php'; ?>

            <div class="header-title-row">
                <div><h1>Event Management</h1></div>
                <?php if ($currentRole === 'admin'): ?>
                    <button type="button" id="openWizardBtn" class="rn-btn-primary">+ Create New Event</button>
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

            <section class="rn-admin-card">
                <div class="card-header"><h2>All Events List</h2></div>
                <div class="table-responsive">
                    <table class="rn-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Poster</th>
                                <th>Event Name</th>
                                <th>Location</th>
                                <th>Date &amp; Time</th>
                                <th>Tickets</th>
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
                                        <td><span class="status-pill" style="background: rgba(124, 58, 237, 0.1); color: var(--admin-purple);"><?= (int)$event['package_count'] ?> Pass(es)</span></td>
                                        <td>
                                            <span class="status-pill status-<?= htmlspecialchars($event['status']) ?>">
                                                <?= strtoupper(htmlspecialchars($event['status'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($currentRole === 'admin'): ?>
                                                <div style="display:flex; gap:6px; align-items:center;">
                                                    <button type="button" class="manage-packages-btn rn-btn-secondary" data-event-id="<?= (int)$event['id'] ?>" data-event-name="<?= htmlspecialchars($event['event_name']) ?>" title="Manage Ticket Passes">Passes</button>
                                                    <button type="button" class="edit-event-btn rn-btn-secondary" data-event-id="<?= (int)$event['id'] ?>" title="Edit Event">Edit</button>
                                                    <button type="button" class="delete-event-btn rn-btn-secondary" data-event-id="<?= (int)$event['id'] ?>" data-event-name="<?= htmlspecialchars($event['event_name']) ?>" title="Delete Event" style="color:#ef4444;">Delete</button>
                                                </div>
                                            <?php else: ?>
                                                <span style="font-size: 12px; color: var(--admin-text-muted);">View Only</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="8" style="text-align:center; padding: 24px; color: var(--admin-text-muted);">No events found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <!-- 1. Create Event Wizard Modal -->
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
                <div id="wizardStep1" class="wizard-step-content active">
                    <div class="form-group">
                        <label>Auto-Generated Event ID</label>
                        <input type="text" class="form-control" value="#<?= $nextAutoId ?> (Auto Generated)" disabled style="opacity: 0.7; font-weight: bold;">
                    </div>
                    <div class="form-group">
                        <label>Event Name *</label>
                        <input type="text" id="event_name" name="event_name" class="form-control" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Event Date *</label><input type="date" id="event_date" name="event_date" class="form-control" required></div>
                        <div class="form-group"><label>Event Time *</label><input type="time" id="event_time" name="event_time" class="form-control" value="20:00" required></div>
                    </div>
                    <div class="form-group"><label>Location *</label><input type="text" id="event_location" name="location" class="form-control" required></div>
                    <div class="form-group"><label>Event Poster (.PNG/.JPG/.WEBP, Max 5MB) *</label><input type="file" id="event_poster" name="event_poster" class="form-control" accept=".png, .jpg, .jpeg, .webp" required></div>
                    <div class="form-group"><label>Short Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                    <div class="modal-footer"><button type="button" id="nextStepBtn" class="rn-btn-primary">Next: Main Ticket Pass</button></div>
                </div>
                <div id="wizardStep2" class="wizard-step-content">
                    <div style="background: var(--admin-bg); padding: 16px; border-radius: 14px; border: 1px solid var(--admin-border); margin-bottom: 20px;">
                        <strong style="font-size:13px; color:var(--admin-purple); display:block; margin-bottom:12px;">Initial Ticket Pass</strong>
                        <div class="form-group"><label>Package Name *</label><input type="text" name="package_name" class="form-control" value="Standard Pass" required></div>
                        <div class="form-group"><label>Ticket Price (LKR) *</label><input type="number" step="0.01" name="ticket_price" class="form-control" value="3500.00" required></div>
                        <div class="form-group" style="margin-bottom:0;"><label>Benefits</label><textarea name="benefits" class="form-control" rows="3"></textarea></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" id="prevStepBtn" class="rn-btn-secondary">Back</button>
                        <button type="submit" class="rn-btn-primary">Save &amp; Publish Event</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. Edit Event Modal -->
    <div id="editEventModal" class="rn-modal-overlay">
        <div class="rn-modal-card" style="max-width: 650px;">
            <div class="modal-header"><h3>Edit Event Details</h3><button type="button" id="closeEditEventModalBtn" class="modal-close-btn">&times;</button></div>
            <form action="events.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_event">
                <input type="hidden" name="event_id" id="edit_event_id">
                <div class="form-group"><label>Event Name *</label><input type="text" id="edit_event_name" name="event_name" class="form-control" required></div>
                <div class="form-row">
                    <div class="form-group"><label>Event Date *</label><input type="date" id="edit_event_date" name="event_date" class="form-control" required></div>
                    <div class="form-group"><label>Event Time *</label><input type="time" id="edit_event_time" name="event_time" class="form-control" required></div>
                </div>
                <div class="form-group"><label>Location *</label><input type="text" id="edit_event_location" name="location" class="form-control" required></div>
                <div class="form-group"><label>Status</label><select id="edit_event_status" name="status" class="form-control"><option value="published">PUBLISHED</option><option value="draft">DRAFT</option><option value="cancelled">CANCELLED</option></select></div>
                <div class="form-group"><label>Replace Poster</label><input type="file" name="event_poster" class="form-control" accept=".png, .jpg, .jpeg, .webp"></div>
                <div class="form-group"><label>Description</label><textarea id="edit_event_description" name="description" class="form-control" rows="3"></textarea></div>
                <div class="modal-footer"><button type="submit" class="rn-btn-primary">Update Event Details</button></div>
            </form>
        </div>
    </div>

    <!-- 3. Manage Main Ticket Packages Modal -->
    <div id="managePackagesModal" class="rn-modal-overlay">
        <div class="rn-modal-card" style="max-width: 680px;">
            <div class="modal-header"><h3 id="manageModalTitle">Manage Main Event Passes</h3><button type="button" id="closeManageModalBtn" class="modal-close-btn">&times;</button></div>
            <div style="margin-bottom: 20px;"><h4 style="font-size:12px; font-weight:800; color:var(--admin-text-muted); margin-bottom:12px; text-transform:uppercase;">Current Ticket Passes</h4><div id="existingPackagesList" class="pkg-card-grid"></div></div>
            <form action="events.php" method="POST" id="packageForm" style="border-top: 1px solid var(--admin-border); padding-top: 16px;">
                <input type="hidden" name="action" id="packageFormAction" value="add_package">
                <input type="hidden" name="package_id" id="managePkgIdInput">
                <input type="hidden" name="event_id" id="manageEventIdInput">
                <h4 id="formSectionTitle" style="font-size:13px; font-weight:800; color:var(--admin-purple); margin-bottom:12px; text-transform:uppercase;">+ Add New Pass Tier</h4>
                <div class="form-row">
                    <div class="form-group"><label>Package Name *</label><input type="text" id="pkg_name_input" name="package_name" class="form-control" required></div>
                    <div class="form-group"><label>Ticket Price (LKR) *</label><input type="number" step="0.01" id="pkg_price_input" name="ticket_price" class="form-control" value="0.00" required></div>
                </div>
                <div class="form-group"><label>Benefits</label><textarea id="pkg_benefits_input" name="benefits" class="form-control" rows="2"></textarea></div>
                <div class="modal-footer" style="margin-top:0; padding-top:14px;">
                    <button type="button" id="cancelEditBtn" class="rn-btn-secondary" style="display:none;">Cancel Edit</button>
                    <button type="submit" id="savePkgSubmitBtn" class="rn-btn-primary">Add Ticket Pass</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>