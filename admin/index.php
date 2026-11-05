<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: ../login.php');
    exit;
}

$currentRole = $_SESSION['role'];
$username = $_SESSION['username'] ?? 'User';
$fullName = $_SESSION['full_name'] ?? $username;

require_once __DIR__ . '/../config/database.php';

$pageTitle = "Admin Portal | Rave Nations";

$stats = [
    'total_events' => 0,
    'published_events' => 0,
    'total_users' => 0,
];

try {
    $stmtUsers = $pdo->query("SELECT COUNT(*) FROM user_registration");
    $stats['total_users'] = (int) $stmtUsers->fetchColumn();

    $tableCheck = $pdo->query("SHOW TABLES LIKE 'events'");
    if ($tableCheck->rowCount() > 0) {
        $stmtEvents = $pdo->query("SELECT COUNT(*) FROM events");
        $stats['total_events'] = (int) $stmtEvents->fetchColumn();

        $stmtPubEvents = $pdo->query("SELECT COUNT(*) FROM events WHERE status = 'published'");
        $stats['published_events'] = (int) $stmtPubEvents->fetchColumn();
    }
} catch (PDOException $e) {
    error_log('Admin stats query error: ' . $e->getMessage());
}

$events = [];
try {
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'events'");
    if ($tableCheck->rowCount() > 0) {
        $stmtList = $pdo->query("SELECT id, event_name, location, event_date, event_time, status FROM events ORDER BY event_date ASC");
        $events = $stmtList->fetchAll();
    }
} catch (PDOException $e) {
    error_log('Admin events query error: ' . $e->getMessage());
}

if (empty($events)) {
    $events = [
        [
            'id' => 1,
            'event_name' => 'BLAZING ABYSS - PHASE 2',
            'location' => 'Mirissa Coastal Arena, Sri Lanka',
            'event_date' => '2026-12-15',
            'event_time' => '20:00',
            'status' => 'published'
        ]
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@400;600;700;900&display=swap" rel="stylesheet">
    <!-- Load Stylesheets from adminstyle folder -->
    <link rel="stylesheet" href="adminstyle/admin.css?v=4.0">
    <link rel="stylesheet" href="adminstyle/navbar.css?v=4.0">
    <link rel="stylesheet" href="adminstyle/header.css?v=1.0">
</head>
<body>

    <div class="rn-admin-layout">
        
        <!-- MODULAR NAVBAR COMPONENT (Sidebar stays on the left) -->
        <?php include __DIR__ . '/include/navbar.php'; ?>

        <!-- MAIN CONTENT AREA -->
        <main class="rn-admin-main">

            <!-- MODULAR HEADER COMPONENT (Sits right at the top of the main area) -->
            <?php include __DIR__ . '/include/header.php'; ?>

            <div class="header-title-row">
                <div>
                    <h1>Dashboard Overview</h1>
                </div>
                <?php if ($currentRole === 'admin'): ?>
                    <button type="button" class="rn-btn-primary">
                        + Add New Event
                    </button>
                <?php endif; ?>
            </div>

            <!-- STATS CARDS -->
            <section class="rn-stats-grid">
                <div class="rn-stat-card">
                    <div class="stat-top">
                        <span class="stat-label">TOTAL EVENTS</span>
                        <div class="stat-icon-badge">↗</div>
                    </div>
                    <h3 class="stat-value"><?= $stats['total_events'] ?></h3>
                </div>
                <div class="rn-stat-card">
                    <div class="stat-top">
                        <span class="stat-label">ACTIVE / PUBLISHED</span>
                        <div class="stat-icon-badge" style="color: #10b981; background: rgba(16, 185, 129, 0.1);">↗</div>
                    </div>
                    <h3 class="stat-value" style="color: #10b981;"><?= $stats['published_events'] ?></h3>
                </div>
                <div class="rn-stat-card">
                    <div class="stat-top">
                        <span class="stat-label">REGISTERED STAFF</span>
                        <div class="stat-icon-badge" style="color: var(--admin-purple);">↗</div>
                    </div>
                    <h3 class="stat-value" style="color: var(--admin-purple);"><?= $stats['total_users'] ?></h3>
                </div>
            </section>

            <!-- EVENTS DATA TABLE -->
            <section class="rn-admin-card">
                <div class="card-header">
                    <h2>Recent Festival & Event Listings</h2>
                </div>

                <div class="table-responsive">
                    <table class="rn-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Event Name</th>
                                <th>Location</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $event): ?>
                                <tr>
                                    <td>#<?= (int)$event['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($event['event_name']) ?></strong></td>
                                    <td><?= htmlspecialchars($event['location']) ?></td>
                                    <td><?= htmlspecialchars($event['event_date']) ?> at <?= htmlspecialchars($event['event_time']) ?></td>
                                    <td>
                                        <span class="status-pill status-<?= htmlspecialchars($event['status']) ?>">
                                            <?= strtoupper(htmlspecialchars($event['status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($currentRole === 'admin'): ?>
                                            <button title="Edit" style="background:none; border:none; cursor:pointer; font-size:16px;">✏️</button>
                                            <button title="Delete" style="background:none; border:none; cursor:pointer; font-size:16px;">🗑️</button>
                                        <?php else: ?>
                                            <span style="font-size: 12px; color: var(--admin-text-muted);">View Only</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>

        </main>
    </div>

</body>
</html>