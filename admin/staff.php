<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: ../login.php');
    exit;
}

$currentRole   = (string) $_SESSION['role'];
$currentUserId = (int) $_SESSION['user_id'];

// Only Admins can manage staff
if ($currentRole !== 'admin') {
    header('Location: ' . ($currentRole === 'gate_keeper' ? 'scanner.php' : 'index.php'));
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/include/staff_functions.php';

$message = '';
$error   = '';

if (isset($_GET['ajax_action'])) {
    handleStaffAjaxRequests($pdo, $currentRole, $currentUserId);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create_staff') {
        $result = handleCreateStaff($pdo, $currentRole);
    } elseif ($_POST['action'] === 'update_staff') {
        $result = handleUpdateStaff($pdo, $currentRole, $currentUserId);
    } else {
        $result = [];
    }
    $message = $result['message'] ?? '';
    $error   = $result['error'] ?? '';
}

$staffList   = fetchAllStaff($pdo);
$staffCounts = fetchStaffCounts($staffList);
$rolesMap    = rnGetAllowedStaffRoles();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management | Rave Nations</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="adminstyle/admin.css?v=14.1">
    <link rel="stylesheet" href="adminstyle/navbar.css?v=14.1">
    <link rel="stylesheet" href="adminstyle/header.css?v=14.1">
    <link rel="stylesheet" href="adminstyle/wizard.css?v=9.1">
    <link rel="stylesheet" href="adminstyle/events.css?v=4.1">
    <script src="js/staff-manager.js?v=1.2" defer></script>
    <style>
        .role-pill {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .role-admin         { background: rgba(124, 58, 237, 0.14); color: #6d28d9; }
        .role-support_agent { background: rgba(59, 130, 246, 0.14); color: #2563eb; }
        .role-gate_keeper   { background: rgba(16, 185, 129, 0.14); color: #059669; }
        .status-active      { background: rgba(16, 185, 129, 0.14); color: #059669; }
        .status-inactive    { background: rgba(239, 68, 68, 0.14); color: #dc2626; }
        .staff-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }
        .staff-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            flex: 1;
        }
        .staff-filters input,
        .staff-filters select {
            max-width: 260px;
        }
        @media (max-width: 600px) {
            .staff-filters input,
            .staff-filters select {
                max-width: 100%;
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <div class="rn-admin-layout">
        <?php include __DIR__ . '/include/navbar.php'; ?>

        <main class="rn-admin-main">
            <?php include __DIR__ . '/include/header.php'; ?>

            <div class="header-title-row">
                <div><h1>Staff Management</h1></div>
                <button type="button" id="openCreateStaffBtn" class="rn-btn-primary">+ Register Staff</button>
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

            <!-- STAFF STATS -->
            <section class="rn-stats-grid">
                <div class="rn-stat-card">
                    <div class="stat-top">
                        <span class="stat-label">TOTAL STAFF</span>
                        <div class="stat-icon-badge">&#8599;</div>
                    </div>
                    <h3 class="stat-value"><?= $staffCounts['total'] ?></h3>
                </div>
                <div class="rn-stat-card">
                    <div class="stat-top">
                        <span class="stat-label">ADMINS</span>
                        <div class="stat-icon-badge" style="color: var(--admin-purple);">&#8599;</div>
                    </div>
                    <h3 class="stat-value" style="color: var(--admin-purple);"><?= $staffCounts['admin'] ?></h3>
                </div>
                <div class="rn-stat-card">
                    <div class="stat-top">
                        <span class="stat-label">SUPPORT AGENTS</span>
                        <div class="stat-icon-badge" style="color: #2563eb; background: rgba(59, 130, 246, 0.1);">&#8599;</div>
                    </div>
                    <h3 class="stat-value" style="color: #2563eb;"><?= $staffCounts['support_agent'] ?></h3>
                </div>
                <div class="rn-stat-card">
                    <div class="stat-top">
                        <span class="stat-label">GATE KEEPERS</span>
                        <div class="stat-icon-badge" style="color: #10b981; background: rgba(16, 185, 129, 0.1);">&#8599;</div>
                    </div>
                    <h3 class="stat-value" style="color: #10b981;"><?= $staffCounts['gate_keeper'] ?></h3>
                </div>
            </section>

            <!-- STAFF LIST CARD -->
            <section class="rn-admin-card">
                <div class="card-header staff-toolbar">
                    <h2>Registered Team Members</h2>
                    <div class="staff-filters">
                        <input type="text" id="staffSearchInput" class="form-control" placeholder="Search name, username, email...">
                        <select id="staffRoleFilter" class="form-control">
                            <option value="all">All Roles</option>
                            <option value="admin">Admin</option>
                            <option value="support_agent">Customer Support Agent</option>
                            <option value="gate_keeper">Gate Keeper</option>
                        </select>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="rn-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($staffList)): ?>
                                <?php foreach ($staffList as $staff):
                                    $sId    = (int) $staff['id'];
                                    $sRole  = (string) $staff['role'];
                                    $sStat  = (string) $staff['status'];
                                    $isSelf = ($sId === $currentUserId);
                                    $searchBlob = $staff['full_name'] . ' ' . $staff['username'] . ' ' . $staff['email'];
                                ?>
                                    <tr
                                        id="staff-row-<?= $sId ?>"
                                        class="staff-table-row"
                                        data-role="<?= htmlspecialchars($sRole) ?>"
                                        data-search="<?= htmlspecialchars($searchBlob) ?>"
                                    >
                                        <td>#<?= $sId ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($staff['full_name']) ?></strong>
                                            <?php if ($isSelf): ?>
                                                <span style="font-size:10px; color:var(--admin-purple); font-weight:800;">(YOU)</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($staff['username']) ?></td>
                                        <td><?= htmlspecialchars($staff['email']) ?></td>
                                        <td>
                                            <span class="role-pill role-<?= htmlspecialchars($sRole) ?>">
                                                <?= htmlspecialchars($rolesMap[$sRole] ?? $sRole) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="status-pill status-<?= htmlspecialchars($sStat) ?>">
                                                <?= strtoupper(htmlspecialchars($sStat)) ?>
                                            </span>
                                        </td>
                                        <td><?= htmlspecialchars(date('d M Y', strtotime((string) $staff['created_at']))) ?></td>
                                        <td>
                                            <div style="display:flex; gap:6px; align-items:center;">
                                                <button type="button" class="edit-staff-btn rn-btn-secondary" data-id="<?= $sId ?>">Edit</button>
                                                <?php if (!$isSelf): ?>
                                                    <button type="button" class="toggle-staff-btn rn-btn-secondary" data-id="<?= $sId ?>">
                                                        <?= $sStat === 'active' ? 'Disable' : 'Activate' ?>
                                                    </button>
                                                    <button type="button" class="delete-staff-btn rn-btn-secondary" data-id="<?= $sId ?>" data-name="<?= htmlspecialchars($staff['full_name']) ?>" style="color:#ef4444;">
                                                        Delete
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="8" style="text-align:center; padding:24px; color:var(--admin-text-muted);">No staff members registered yet.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    <!-- 1. REGISTER NEW STAFF MODAL -->
    <div id="createStaffModal" class="rn-modal-overlay">
        <div class="rn-modal-card" style="max-width: 580px;">
            <div class="modal-header">
                <h3>Register New Staff Member</h3>
                <button type="button" id="closeCreateStaffBtn" class="modal-close-btn">&times;</button>
            </div>
            <form action="staff.php" method="POST">
                <input type="hidden" name="action" value="create_staff">

                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="full_name" class="form-control" placeholder="e.g., Pramudi" required>
                    </div>
                    <div class="form-group">
                        <label>Username *</label>
                        <input type="text" name="username" class="form-control" placeholder="e.g., kasun_gate" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Email Address *</label>
                    <input type="email" name="email" class="form-control" placeholder="e.g., gate@ravenations.lk" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Role *</label>
                        <select name="role" class="form-control" required>
                            <option value="admin">Admin (Full Access)</option>
                            <option value="support_agent" selected>Customer Support Agent</option>
                            <option value="gate_keeper">Gate Keeper (QR Scanner)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Account Status</label>
                        <select name="status" class="form-control">
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Password (Min 6 characters) *</label>
                    <input type="password" name="password" class="form-control" minlength="6" placeholder="Create strong password" required>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="rn-btn-primary">Register Staff Account</button>
                </div>
            </form>
        </div>
    </div>

    <!-- 2. EDIT STAFF MODAL -->
    <div id="editStaffModal" class="rn-modal-overlay">
        <div class="rn-modal-card" style="max-width: 580px;">
            <div class="modal-header">
                <h3>Edit Staff Account</h3>
                <button type="button" id="closeEditStaffBtn" class="modal-close-btn">&times;</button>
            </div>
            <form action="staff.php" method="POST">
                <input type="hidden" name="action" value="update_staff">
                <input type="hidden" name="staff_id" id="edit_staff_id">

                <div class="form-row">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" id="edit_full_name" name="full_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Username *</label>
                        <input type="text" id="edit_username" name="username" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Email Address *</label>
                    <input type="email" id="edit_email" name="email" class="form-control" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Role *</label>
                        <select id="edit_role" name="role" class="form-control" required>
                            <option value="admin">Admin (Full Access)</option>
                            <option value="support_agent">Customer Support Agent</option>
                            <option value="gate_keeper">Gate Keeper (QR Scanner)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select id="edit_status" name="status" class="form-control">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Reset Password (Leave blank to keep current password)</label>
                    <input type="password" id="edit_new_password" name="new_password" class="form-control" minlength="6" placeholder="Optional new password">
                </div>

                <div class="modal-footer">
                    <button type="submit" class="rn-btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>