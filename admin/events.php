<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/_layout.php';

$admin = require_admin($pdo);

$events = $pdo->query(
    'SELECT
        e.*,
        (
            SELECT COUNT(*)
            FROM event_packages p
            WHERE p.event_id = e.id
        ) AS package_count
     FROM events e
     ORDER BY e.event_date DESC, e.id DESC'
)->fetchAll();

page_top('Events', $admin);
?>

<div class="heading">
    <div>
        <p class="eyebrow">EVENT MANAGEMENT</p>
        <h1>All Events</h1>
        <p class="muted">
            Create, update and manage your events
            and ticket packages.
        </p>
    </div>

    <a class="btn"
       href="<?= h(admin_url('event_form.php')) ?>">
        + New Event
    </a>
</div>

<?php if (!$events): ?>

    <div class="panel">
        <h2>No Events Yet</h2>
        <p class="muted">
            Create your first Rave Nations event
            using the guided wizard.
        </p>

        <a class="btn"
           href="<?= h(admin_url('event_form.php')) ?>">
            Create Event
        </a>
    </div>

<?php else: ?>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Event</th>
                    <th>Date</th>
                    <th>Packages</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($events as $event): ?>
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
                            <?= (int)$event['package_count'] ?>
                        </td>

                        <td>
                            <span class="pill <?= $event['status'] === 'published' ? 'live' : '' ?>">
                                <?= h(ucfirst($event['status'])) ?>
                            </span>
                        </td>

                        <td class="actions">
                            <a class="btn ghost"
                               href="<?= h(admin_url(
                                   'event_form.php?id=' .
                                   (int)$event['id']
                               )) ?>">
                                Edit
                            </a>

                            <form method="POST"
                                  action="<?= h(admin_url('event_delete.php')) ?>"
                                  onsubmit="return confirm('Delete this event and ALL its packages? This cannot be undone.');">

                                <input
                                    type="hidden"
                                    name="csrf_token"
                                    value="<?= h(csrf_token()) ?>"
                                >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= (int)$event['id'] ?>"
                                >

                                <button
                                    type="submit"
                                    class="btn danger">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php endif; ?>


