<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/_layout.php';

$admin = require_admin($pdo);

$total = (int)$pdo->query(
    'SELECT COUNT(*) FROM events'
)->fetchColumn();

$published = (int)$pdo->query(
    "SELECT COUNT(*) FROM events WHERE status = 'published'"
)->fetchColumn();

$packages = (int)$pdo->query(
    'SELECT COUNT(*) FROM event_packages'
)->fetchColumn();

$recent = $pdo->query(
    'SELECT
        e.id,
        e.event_name,
        e.event_date,
        e.location,
        e.status,
        COUNT(p.id) AS packages
     FROM events e
     LEFT JOIN event_packages p ON p.event_id = e.id
     GROUP BY
        e.id, e.event_name, e.event_date,
        e.location, e.status
     ORDER BY e.id DESC
     LIMIT 5'
)->fetchAll();

page_top('Dashboard', $admin);
?>

<section class="welcome">
    <div>
        <p class="eyebrow">EVENT CONTROL CENTER</p>
        <h1>Welcome back, <?= h($admin['name']) ?></h1>
        <p class="muted">
            Create events, manage ticket packages,
            and publish when you are ready.
        </p>
    </div>

    <a class="btn"
       href="<?= h(admin_url('event_form.php')) ?>">
        + Create Event Wizard
    </a>
</section>

<section class="stats" aria-label="Overview">
    <article>
        <p>Total Events</p>
        <strong><?= $total ?></strong>
        <span class="stat-foot">All created events</span>
    </article>

    <article>
        <p>Published Events</p>
        <strong><?= $published ?></strong>
        <span class="stat-foot">
            Events marked as published
        </span>
    </article>

    <article>
        <p>Ticket Packages</p>
        <strong><?= $packages ?></strong>
        <span class="stat-foot">Across all events</span>
    </article>
</section>

<section class="panel">
    <div class="heading">
        <div>
            <p class="eyebrow">QUICK ACTIONS</p>
            <h2>Manage Your Events</h2>
            <p class="muted">
                Start with the guided wizard or
                manage existing events.
            </p>
        </div>
    </div>

    <div class="quick-grid">
        <a class="quick-action"
           href="<?= h(admin_url('event_form.php')) ?>">
            <span class="quick-icon">+</span>
            <strong>Create New Event</strong>
            <span>Details → Packages → Review</span>
        </a>

        <a class="quick-action"
           href="<?= h(admin_url('events.php')) ?>">
            <span class="quick-icon">▤</span>
            <strong>View All Events</strong>
            <span>Edit events and ticket packages</span>
        </a>
    </div>
</section>

<section class="panel">
    <div class="heading">
        <div>
            <p class="eyebrow">RECENT ACTIVITY</p>
            <h2>Latest Events</h2>
        </div>

        <a class="btn ghost"
           href="<?= h(admin_url('events.php')) ?>">
            View All
        </a>
    </div>

    <?php if (!$recent): ?>
        <p class="muted">
            No events yet. Create your first event
            using the wizard.
        </p>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Date</th>
                        <th>Packages</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($recent as $event): ?>
                        <tr>
                            <td>
                                <strong>
                                    <?= h($event['event_name']) ?>
                                </strong>
                                <div class="muted small">
                                    <?= h($event['location']) ?>
                                </div>
                            </td>

                            <td>
                                <?= h($event['event_date']) ?>
                            </td>

                            <td>
                                <?= (int)$event['packages'] ?>
                            </td>

                            <td>
                                <span class="pill <?= $event['status'] === 'published' ? 'live' : '' ?>">
                                    <?= h(ucfirst($event['status'])) ?>
                                </span>
                            </td>

                            <td>
                                <a class="btn ghost"
                                   href="<?= h(admin_url(
                                       'event_form.php?id=' .
                                       (int)$event['id']
                                   )) ?>">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>


