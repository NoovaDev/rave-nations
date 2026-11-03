<?php
declare(strict_types=1);

// Centralized PDO database connection from your config folder
require_once __DIR__ . '/config/database.php';

function rnEscape(mixed $value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

// Get event ID from URL query parameters (default to ID 1 if not passed)
$eventId = isset($_GET['id']) ? (int) $_GET['id'] : 1;

// Fetch Event Details using exact database column names
$stmt = $pdo->prepare("
    SELECT id, event_name, location, event_date, event_time, description, poster_path, status 
    FROM events 
    WHERE id = ?
");
$stmt->execute([$eventId]);
$event = $stmt->fetch();

if (!$event) {
    http_response_code(404);
    die("Event not found.");
}

// Fetch Ticket Packages linked to this event from event_packages table
$pkgStmt = $pdo->prepare("
    SELECT id, package_name, benefits, ticket_price 
    FROM event_packages 
    WHERE event_id = ? 
    ORDER BY ticket_price ASC
");
$pkgStmt->execute([$eventId]);
$packages = $pkgStmt->fetchAll();

$pageTitle = rnEscape($event['event_name']) . " | Rave Nations";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#ffffff">
    <title><?= $pageTitle ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- CSS Stylesheets -->
    <link rel="stylesheet" href="style/style.css?v=90">
    <link rel="stylesheet" href="style/event-details.css?v=90">
    <link rel="stylesheet" href="style/footer.css?v=90">
</head>
<body style="background-color: #ffffff; color: #111114;">

    <!-- Cinematic Event Details Main Wrapper -->
    <main class="rn-container rn-event-main-wrapper">
        
        <!-- Back Navigation Button -->
        <div class="rn-back-action-wrap">
            <a href="index.php#events" class="rn-featured-explore-btn">
                <span>←</span> Back to Overview
            </a>
        </div>

        <div class="rn-event-hero-grid">
            
            <!-- Left Column: Immersive Floating Poster Card -->
            <div class="rn-event-poster-container">
                <div class="rn-poster-card rn-detail-poster-glow">
                    <img src="<?= rnEscape($event['poster_path']) ?>" alt="<?= rnEscape($event['event_name']) ?>" loading="eager">
                    <span class="rn-event-badge"><?= strtoupper(rnEscape($event['status'])) ?></span>
                </div>
            </div>

            <!-- Right Column: Cinematic Details & Ticket Grid -->
            <div class="rn-event-content-container">
                <span class="rn-section-label">OFFICIAL EVENT SPECIFICATION</span>
                <h1 class="rn-section-title rn-detail-title" style="font-size: clamp(32px, 4vw, 48px); margin: 8px 0 20px;">
                    <?= rnEscape($event['event_name']) ?>
                </h1>
                
                <div class="rn-event-meta-banner">
                    <div class="rn-meta-info-block">
                        <span class="rn-meta-icon">📅</span>
                        <div>
                            <small style="color: var(--home-text); display: block;">DATE</small>
                            <strong><?= date('d F Y', strtotime($event['event_date'])) ?></strong>
                        </div>
                    </div>
                    <div class="rn-meta-info-block">
                        <span class="rn-meta-icon">🕒</span>
                        <div>
                            <small style="color: var(--home-text); display: block;">TIME</small>
                            <strong><?= rnEscape($event['event_time']) ?></strong>
                        </div>
                    </div>
                    <div class="rn-meta-info-block">
                        <span class="rn-meta-icon">📍</span>
                        <div>
                            <small style="color: var(--home-text); display: block;">LOCATION</small>
                            <strong><?= rnEscape($event['location']) ?></strong>
                        </div>
                    </div>
                </div>
                
                <div class="rn-event-synopsis">
                    <h3 style="font-size: 20px; font-weight: 800; color: #111114; margin-bottom: 10px;">Event Overview</h3>
                    <p style="color: var(--home-text); line-height: 1.8; font-size: 16px; margin: 0;">
                        <?= rnEscape($event['description']) ?>
                    </p>
                </div>

                <div class="rn-packages-header-group">
                    <h3 class="rn-section-title" style="font-size: 24px; color: #111114;">TICKET <span>PACKAGES.</span></h3>
                    <?php if (!empty($packages)): ?>
                        <span class="rn-package-badge-count"><?= count($packages) ?> Passes Active</span>
                    <?php endif; ?>
                </div>

                <!-- Scrollable Container for 1 to 10+ Packages (Clean Card Loop) -->
                <div class="rn-packages-scroll-viewport">
                    <div class="rn-packages-stack">
                        <?php if (!empty($packages)): ?>
                            <?php foreach ($packages as $idx => $pkg): ?>
                                <div class="rn-ticket-package-card">
                                    <div class="rn-ticket-package-info">
                                        <div class="rn-ticket-pkg-top">
                                            <span class="rn-pkg-number">TIER 0<?= $idx + 1 ?></span>
                                            <h4><?= rnEscape($pkg['package_name']) ?></h4>
                                        </div>
                                        <p><?= rnEscape($pkg['benefits']) ?></p>
                                    </div>
                                    <div class="rn-ticket-package-action">
                                        <div class="rn-ticket-price-tag">LKR <?= number_format((float)$pkg['ticket_price'], 2) ?></div>
                                        <a href="checkout.php?package_id=<?= (int)$pkg['id'] ?>" class="rn-featured-explore-btn" style="padding: 8px 22px; font-size: 14px;">
                                            Select ↗
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="rn-no-packages-box">
                                <p style="margin: 0; color: var(--home-text);">Ticket packages will be announced shortly. Stay tuned.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

        </div>
    </main>

    <!-- Footer Include -->
    <?php 
    if (file_exists(__DIR__ . '/includes/footer.php')) {
        require_once __DIR__ . '/includes/footer.php'; 
    }
    ?>

</body>
</html>