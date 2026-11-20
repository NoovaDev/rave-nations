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
$pageTitle   = "Admin Portal | Rave Nations";

$stats  = fetchDashboardStats($pdo);
$events = fetchAllEventsWithCounts($pdo);
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
    <link rel="stylesheet" href="adminstyle/admin.css?v=14.1">
    <link rel="stylesheet" href="adminstyle/navbar.css?v=14.1">
    <link rel="stylesheet" href="adminstyle/header.css?v=14.1">
</head>
<body>

    <div class="rn-admin-layout">

        <?php include __DIR__ . '/include/navbar.php'; ?>

        <main class="rn-admin-main">

            <?php include __DIR__ . '/include/header.php'; ?>

            <div class="header-title-row">
                <div>
                    <h1>Dashboard Overview</h1>
                </div>
                <?php if ($currentRole === 'admin'): ?>
                    <a href="events.php?open_wizard=1" class="rn-btn-primary" style="text-decoration:none;">
                        + Add New Event
                    </a>
                <?php endif; ?>
            </div>

            <!-- STATS CARDS -->
            <section class="rn-stats-grid">
                <div class="rn-stat-card">
                    <div class="stat-top">
                        <span class="stat-label">TOTAL EVENTS</span>
                        <div class="stat-icon-badge">&#8599;</div>
                    </div>
                    <h3 class="stat-value"><?= $stats['total_events'] ?></h3>
                </div>
                <div class="rn-stat-card">
                    <div class="stat-top">
                        <span class="stat-label">ACTIVE / PUBLISHED</span>
                        <div class="stat-icon-badge" style="color: #10b981; background: rgba(16, 185, 129, 0.1);">&#8599;</div>
                    </div>
                    <h3 class="stat-value" style="color: #10b981;"><?= $stats['published_events'] ?></h3>
                </div>
                <div class="rn-stat-card">
                    <div class="stat-top">
                        <span class="stat-label">REGISTERED STAFF</span>
                        <div class="stat-icon-badge" style="color: var(--admin-purple);">&#8599;</div>
                    </div>
                    <h3 class="stat-value" style="color: var(--admin-purple);"><?= $stats['total_users'] ?></h3>
                </div>
            </section>

            <!-- EVENTS DATA TABLE -->
            <section class="rn-admin-card">
                <div class="card-header">
                    <h2>Recent Festival &amp; Event Listings</h2>
                </div>

                <div class="table-responsive">
                    <table class="rn-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Event Name</th>
                                <th>Location</th>
                                <th>Date &amp; Time</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($events)): ?>
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
                                                <a href="events.php?edit_id=<?= (int)$event['id'] ?>" class="rn-btn-secondary" style="text-decoration:none; font-size:12px; padding:5px 10px;">
                                                    Edit / Manage
                                                </a>
                                            <?php else: ?>
                                                <span style="font-size: 12px; color: var(--admin-text-muted);">View Only</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align:center; padding: 24px; color: var(--admin-text-muted);">
                                        No events found yet. Click "+ Add New Event" to create one.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

        </main>
    </div>

</body>
</html>