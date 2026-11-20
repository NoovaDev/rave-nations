<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

function rnEscape(mixed $value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

function rnIcon(string $name, string$class = 'rn-icon'): string
{
    $icons = [
        'calendar'   => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
        'clock'      => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
        'pin'        => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>',
        'ticket'     => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/></svg>',
        'arrow-left' => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>',
        'arrow-down' => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/></svg>',
        'arrow-up-r' => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/></svg>',
    ];

    return $icons[$name] ?? '';
}

$eventId = isset($_GET['id']) ? (int)$_GET['id'] : 1;

$stmt =$pdo->prepare("
    SELECT id, event_name, event_date, event_time, location, poster_path, description
    FROM events
    WHERE id = ?
    LIMIT 1
");
$stmt->execute([$eventId]);
$event =$stmt->fetch(PDO::FETCH_ASSOC);

if (!$event) {
    http_response_code(404);
    exit('Event not found.');
}

$pkgStmt =$pdo->prepare("
    SELECT id, package_name, benefits, ticket_price
    FROM event_packages
    WHERE event_id = ?
    ORDER BY ticket_price ASC
");
$pkgStmt->execute([$eventId]);
$packages =$pkgStmt->fetchAll(PDO::FETCH_ASSOC);

$rawTime = (string) ($event['event_time'] ?? '20:00:00');$dateTimestamp = strtotime(trim((string) $event['event_date'] . ' ' .$rawTime));

$eventDate =$dateTimestamp !== false
    ? date('D, d M Y', $dateTimestamp)
    : 'Date to be announced';

$eventTime =$dateTimestamp !== false
    ? date('h:i A', $dateTimestamp)
    : '';

$heroBg = !empty($event['poster_path'])
    ? (string) $event['poster_path']
    : 'assets/design/hero.jpg';

$pageTitle = rnEscape($event['event_name']) . ' | Rave Nations';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0e0b18">

    <title><?= $pageTitle ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="style/event-details.css?v=160">
    <link rel="stylesheet" href="style/footer.css?v=76">
</head>

<body>

<main class="rn-event-page">

    <!-- HERO SECTION -->
    <section class="rn-event-hero">
        <div class="rn-hero-background" style="background-image: url('<?= rnEscape($heroBg) ?>');"></div>
        <div class="rn-hero-overlay"></div>

        <div class="rn-hero-container">

            <div class="rn-hero-topbar">
                <a href="index.php#events" class="rn-back-link">
                    <?= rnIcon('arrow-left', 'rn-icon rn-icon-sm') ?>
                    <span>Back to Events</span>
                </a>
            </div>

            <div class="rn-hero-content">
                <span class="rn-hero-eyebrow">RAVE NATIONS PRESENTS</span>

                <h1><?= rnEscape($event['event_name']) ?></h1>

                <div class="rn-hero-meta-row">
                    <span class="rn-hero-chip">
                        <?= rnIcon('calendar', 'rn-icon rn-icon-sm') ?>
                        <span><?= rnEscape($eventDate) ?></span>
                    </span>

                    <?php if ($eventTime !== ''): ?>
                        <span class="rn-hero-chip">
                            <?= rnIcon('clock', 'rn-icon rn-icon-sm') ?>
                            <span><?= rnEscape($eventTime) ?></span>
                        </span>
                    <?php endif; ?>

                    <?php if (!empty($event['location'])): ?>
                        <span class="rn-hero-chip">
                            <?= rnIcon('pin', 'rn-icon rn-icon-sm') ?>
                            <span><?= rnEscape($event['location']) ?></span>
                        </span>
                    <?php endif; ?>
                </div>

                <?php if (!empty($event['description'])): ?>
                    <p class="rn-hero-description">
                        <?= nl2br(rnEscape($event['description'])) ?>
                    </p>
                <?php endif; ?>
            </div>

            <a href="#tickets" class="rn-hero-scroll">
                <span>EXPLORE TICKETS</span>
                <?= rnIcon('arrow-down', 'rn-icon rn-icon-sm') ?>
            </a>

        </div>
    </section>

    <!-- TICKET LISTING -->
    <section class="rn-ticket-section" id="tickets">

        <div class="rn-ticket-container">

            <div class="rn-ticket-heading">
                <div>
                    <span class="rn-ticket-eyebrow">BOOK YOUR EXPERIENCE</span>
                    <h2>Available <span>Tickets</span></h2>
                </div>

                <span class="rn-ticket-count">
                    <?= count($packages) ?>
                    <?= count($packages) === 1 ? 'TICKET OPTION' : 'TICKET OPTIONS' ?>
                </span>
            </div>

            <?php if (!empty($packages)): ?>

                <div class="rn-ticket-list">

                    <?php foreach ($packages as $index =>$pkg): ?>

                        <article class="rn-ticket-card">

                            <div class="rn-ticket-index">
                                <?= str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) ?>
                            </div>

                            <div class="rn-ticket-details">
                                <span class="rn-ticket-type">OFFICIAL EVENT PASS</span>

                                <h3><?= rnEscape($pkg['package_name']) ?></h3>

                                <?php if (!empty($pkg['benefits'])): ?>
                                    <p><?= nl2br(rnEscape($pkg['benefits'])) ?></p>
                                <?php endif; ?>
                            </div>

                            <div class="rn-ticket-price">
                                <span>PRICE</span>
                                <strong>
                                    <small>LKR</small>
                                    <?= number_format((float) $pkg['ticket_price'], 2) ?>
                                </strong>
                            </div>

                            <div class="rn-ticket-action">
                                <a
                                    href="checkout.php?package_id=<?= (int) $pkg['id'] ?>"
                                    class="rn-buy-button"
                                >
                                    <?= rnIcon('ticket', 'rn-icon rn-icon-sm') ?>
                                    <span>Buy Ticket</span>
                                    <?= rnIcon('arrow-up-r', 'rn-icon rn-icon-sm') ?>
                                </a>
                            </div>

                        </article>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="rn-no-tickets">
                    <h3>Tickets Coming Soon</h3>
                    <p>Ticket packages for this event will be announced soon.</p>
                </div>

            <?php endif; ?>

        </div>

    </section>

</main>

<?php
if (file_exists(__DIR__ . '/includes/footer.php')) {
    require_once __DIR__ . '/includes/footer.php';
}
?>

</body>
</html>