<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/config/database.php';

$eventId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($eventId <= 0) {
    header('Location: index.php');
    exit;
}

// Fetch event details from database
try {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE id = ?");
    $stmt->execute([$eventId]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        header('Location: index.php');
        exit;
    }

    // Fetch starting price from event_packages
    $pkgStmt = $pdo->prepare("SELECT MIN(ticket_price) AS min_price FROM event_packages WHERE event_id = ?");
    $pkgStmt->execute([$eventId]);
    $minPriceRow = $pkgStmt->fetch(PDO::FETCH_ASSOC);
    $startingPrice = $minPriceRow['min_price'] ?? 0.00;

    // Fetch registered event partners from event_partners table
    $partnerStmt = $pdo->prepare("SELECT * FROM event_partners WHERE event_id = ? ORDER BY id ASC");
    $partnerStmt->execute([$eventId]);
    $eventPartners = $partnerStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    header('Location: index.php');
    exit;
}

function rnEscape(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Reusable crisp inline SVG icon helper (replaces emojis).
 * Available icons: 'calendar', 'clock', 'pin', 'ticket', 'arrow'
 */
function rnIcon(string $name, string $class = 'rn-icon'): string
{
    $icons = [
        'calendar' => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
        'clock'    => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>',
        'pin'      => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>',
        'ticket'   => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/></svg>',
        'arrow'    => '<svg class="' . $class . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/></svg>',
    ];

    return $icons[$name] ?? '';
}

// Combine date and time for active countdown target
$rawTime = (string)($event['event_time'] ?? '20:00:00');
$eventDateTimeStr = trim((string)$event['event_date'] . ' ' . $rawTime);
$eventTimestamp = strtotime($eventDateTimeStr) ?: time();
$formattedDate = date('d M Y', $eventTimestamp);
$formattedTime = date('h:i A', $eventTimestamp);

$eventName = (string)$event['event_name'];
$eventLocation = (string)$event['location'];
$eventPoster = (string)$event['poster_path'];
$eventStatus = strtoupper((string)($event['status'] ?? 'UPCOMING'));
$pageTitle = rnEscape($eventName) . " | Rave Nations Events";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0e0b18">
    <title><?= $pageTitle ?></title>

    <!-- Google Fonts & Styles -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style/navbar.css?v=102">
    <link rel="stylesheet" href="style/eventinfor.css?v=2.1">
    <link rel="stylesheet" href="style/footer.css?v=76">

    <!-- Navbar & Analytics JS -->
    <script src="assets/js/navbar.js?v=76" defer></script>
    <script src="assets/js/analytics.js" defer></script>
</head>
<body>

    <!-- Navigation Bar -->
    <?php
    if (file_exists(__DIR__ . '/includes/navbar.php')) {
        require_once __DIR__ . '/includes/navbar.php';
    }
    ?>

    <main class="rn-event-detail-main">

        <!-- Top Cinematic Banner -->
        <section class="rn-event-banner">
            <div class="banner-bg-img" style="background-image: url('<?= rnEscape($eventPoster) ?>');"></div>
            <div class="banner-gradient-overlay"></div>

            <div class="rn-container banner-content-wrapper">
                <div class="breadcrumb-nav">
                    <a href="index.php">Home</a> <span>/</span> <a href="index.php#events">Events</a> <span>/</span> <strong><?= rnEscape($eventName) ?></strong>
                </div>

                <div class="banner-hero-row">
                    <div class="banner-poster-preview">
                        <img src="<?= rnEscape($eventPoster) ?>" alt="<?= rnEscape($eventName) ?>" loading="eager" decoding="async">
                    </div>

                    <div class="banner-title-area">
                        <span class="banner-status-pill"><?= rnEscape($eventStatus) ?></span>
                        <h1 class="event-main-title"><?= rnEscape($eventName) ?></h1>

                        <div class="banner-quick-meta">
                            <span class="banner-meta-chip">
                                <?= rnIcon('calendar', 'rn-icon rn-icon-sm') ?>
                                <?= rnEscape($formattedDate) ?>
                            </span>
                            <span class="banner-meta-chip">
                                <?= rnIcon('clock', 'rn-icon rn-icon-sm') ?>
                                <?= rnEscape($formattedTime) ?>
                            </span>
                            <span class="banner-meta-chip">
                                <?= rnIcon('pin', 'rn-icon rn-icon-sm') ?>
                                <?= rnEscape($eventLocation) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Grid Container -->
        <div class="rn-container rn-event-grid-layout">

            <!-- Left Info Column -->
            <div class="rn-event-left-col">

                <!-- About Event Card -->
                <div class="event-info-card" data-rn-reveal>
                    <span class="event-card-label">EVENT OVERVIEW</span>
                    <h3>About This Experience</h3>
                    <p class="event-full-desc">
                        <?= nl2br(rnEscape($event['description'] ?? '')) ?>
                    </p>
                </div>

                <!-- Database Event Partners Section -->
                <?php if (!empty($eventPartners)): ?>
                <div class="event-info-card rn-partners-section" data-rn-reveal>
                    <span class="event-card-label">OFFICIAL COLLABORATORS</span>
                    <h3>Event Partners</h3>
                    <div class="rn-partners-grid">
                        <?php foreach ($eventPartners as $partner): ?>
                            <article class="rn-partner-card">
                                <?php if (!empty($partner['poster_path'])): ?>
                                    <div class="rn-partner-poster-box">
                                        <img src="<?= rnEscape($partner['poster_path']) ?>" alt="<?= rnEscape($partner['partner_name']) ?>" loading="lazy" decoding="async">
                                    </div>
                                <?php endif; ?>
                                <div class="rn-partner-info">
                                    <h4 class="rn-partner-name"><?= rnEscape($partner['partner_name']) ?></h4>
                                    <?php if (!empty($partner['description'])): ?>
                                        <p class="rn-partner-desc"><?= rnEscape($partner['description']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Location Map Section -->
                <div class="event-info-card" data-rn-reveal>
                    <div class="location-header-row">
                        <h3>Venue &amp; Location</h3>
                        <a href="https://maps.google.com/?q=<?= urlencode($eventLocation) ?>" target="_blank" rel="noopener noreferrer" class="navigate-link">
                            Navigate <?= rnIcon('arrow', 'rn-icon rn-icon-sm') ?>
                        </a>
                    </div>
                    <div class="location-meta-text">
                        <?= rnIcon('pin') ?>
                        <span><?= rnEscape($eventLocation) ?></span>
                    </div>

                    <div class="map-embed-container">
                        <iframe
                            title="Venue Map for <?= rnEscape($eventName) ?>"
                            allowfullscreen
                            loading="lazy"
                            src="https://maps.google.com/maps?q=<?= urlencode($eventLocation . ' Sri Lanka') ?>&t=&z=13&ie=UTF8&iwloc=&output=embed">
                        </iframe>
                    </div>
                </div>

            </div>

            <!-- Right Sticky Booking Sidebar (Moves above details on mobile) -->
            <aside class="rn-event-right-col">
                <div class="booking-sidebar-card" data-rn-reveal>

                    <!-- Countdown Timer -->
                    <div class="countdown-title">Event Starts In</div>
                    <div class="countdown-grid" id="countdownTimer">
                        <div class="count-box"><strong id="days">00</strong><span>Days</span></div>
                        <div class="count-box"><strong id="hours">00</strong><span>Hours</span></div>
                        <div class="count-box"><strong id="mins">00</strong><span>Mins</span></div>
                        <div class="count-box"><strong id="secs">00</strong><span>Secs</span></div>
                    </div>

                    <!-- Date & Time Badges -->
                    <div class="sidebar-datetime-row">
                        <div class="date-badge">
                            <?= rnIcon('calendar', 'rn-icon rn-icon-sm') ?>
                            <span><?= rnEscape($formattedDate) ?></span>
                        </div>
                        <div class="time-badge">
                            <?= rnIcon('clock', 'rn-icon rn-icon-sm') ?>
                            <span><?= rnEscape($formattedTime) ?></span>
                        </div>
                    </div>

                    <!-- Pricing & Get Tickets Button -->
                    <div class="sidebar-pricing-box">
                        <span class="price-label">Starting From</span>
                        <div class="price-value">
                            LKR <?= number_format((float)$startingPrice, 2) ?> <small>Upwards</small>
                        </div>
                        <a href="event.php?id=<?= $eventId ?>" class="rn-btn-get-tickets">
                            <?= rnIcon('ticket', 'rn-icon rn-icon-lg') ?>
                            <span>Get Tickets</span>
                        </a>
                    </div>

                    <!-- QR Code -->
                    <div class="sidebar-actions-row">
                        <div class="qr-code-box">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=https://ravenations.lk/eventinfor.php?id=<?= $eventId ?>" alt="Event QR Code" loading="lazy" decoding="async">
                        </div>
                        <span class="qr-caption">Scan to share event</span>
                    </div>

                </div>

                <!-- Organizer Stamp -->
                <div class="organizer-badge-card" data-rn-reveal>
                    <span class="org-label">MANAGED BY</span>
                    <div class="org-brand">
                        <img src="assets/design/logo.png" alt="Rave Nations">
                        <strong>Rave Nations.</strong>
                    </div>
                </div>

            </aside>

        </div>

    </main>

    <!-- Footer Component -->
    <?php
    if (file_exists(__DIR__ . '/includes/footer.php')) {
        require_once __DIR__ . '/includes/footer.php';
    }
    ?>

    <!-- Live Countdown & Scroll Reveal Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            /* 1. Smooth Scroll Reveal (Matches Homepage) */
            const revealEls = document.querySelectorAll('[data-rn-reveal]');
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries, obs) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-visible');
                            obs.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.08, rootMargin: '0px 0px -20px 0px' });

                revealEls.forEach(el => observer.observe(el));
            } else {
                revealEls.forEach(el => el.classList.add('is-visible'));
            }

            /* 2. Active Event Countdown */
            const eventDateStr = "<?= date('Y-m-d H:i:s', $eventTimestamp) ?>";
            const eventTime = new Date(eventDateStr.replace(/-/g, "/")).getTime();

            const daysEl = document.getElementById("days");
            const hoursEl = document.getElementById("hours");
            const minsEl = document.getElementById("mins");
            const secsEl = document.getElementById("secs");

            const updateCountdown = () => {
                const now = Date.now();
                const distance = eventTime - now;

                if (distance > 0) {
                    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const mins = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const secs = Math.floor((distance % (1000 * 60)) / 1000);

                    daysEl.textContent = String(days).padStart(2, '0');
                    hoursEl.textContent = String(hours).padStart(2, '0');
                    minsEl.textContent = String(mins).padStart(2, '0');
                    secsEl.textContent = String(secs).padStart(2, '0');
                } else {
                    daysEl.textContent = "00";
                    hoursEl.textContent = "00";
                    minsEl.textContent = "00";
                    secsEl.textContent = "00";
                }
            };

            updateCountdown();
            setInterval(updateCountdown, 1000);
        });
    </script>
</body>
</html>