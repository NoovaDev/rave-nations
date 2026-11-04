<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

$pageTitle = "Rave Nations | Event Ticketing Gateway";
$metaDescription = "Preview the Rave Nations interactive 3D event ticket design and proceed to our official ticketing partner to secure your passes.";

function rnEscape(mixed $value): string
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#101015">
    <meta name="description" content="<?= rnEscape($metaDescription) ?>">
    
    <title><?= rnEscape($pageTitle) ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- CSS Stylesheets -->
    <link rel="stylesheet" href="style/style.css?v=80">
    <link rel="stylesheet" href="style/ticket-page.css?v=2.0">
    <link rel="stylesheet" href="style/footer.css?v=80">

    <!-- JavaScript Files -->
    <script src="assets/js/analytics.js" defer></script>
</head>

<body>

    <main id="main-content">
        <section class="rn-ticket-page-section rn-fade-in">
            <div class="rn-container">
                <div class="rn-ticket-page-wrapper">
                    
                    <!-- Text Info Column -->
                    <div class="rn-ticket-page-info">
                        <span class="rn-section-label">EVENT TICKETING GATEWAY</span>
                        <h1 class="rn-section-title">
                            NATIVE CHECKOUT <span>UNDER DEVELOPMENT.</span>
                        </h1>
                        
                        <p class="rn-section-description">
                            Our native direct booking checkout system is currently under active development. Please note that the interactive 3D ticket displayed here is strictly an official visual design preview element customers are not issued digital 3D ticket files.
                        </p>
                        
                        <p class="rn-section-description" style="margin-top: 12px; font-weight: 600; color: #ffffff;">
                            All official live event passes and ticket orders are securely processed through our official ticketing partner:
                        </p>

                        <div class="rn-ticket-page-actions">
                            <a href="https://www.ticketsministry.com/concerts/blazing-abyss/6ac7895c772b1" 
                               target="_blank" 
                               rel="noopener noreferrer" 
                               class="rn-dark-button rn-ticketsministry-btn">
                                Continue to TicketsMinistry ↗
                            </a>
                            <a href="index.php" class="rn-featured-explore-btn">← Back to Home</a>
                        </div>
                    </div>

                    <!-- 3D Holographic Ticket Visual Component -->
                    <div class="rn-ticket-page-visual">
                        <div class="ticket-wrapper-container">
                            <div class="ticket-wrapper" id="ticketCard">
                                <div class="ticket">
                                    <!-- Main Content Stub -->
                                    <div class="ticket-main">
                                        <div class="ticket-header">
                                            <div class="brand-title">RAVE<span>NATIONS</span></div>
                                            <div class="vip-badge">DESIGN PREVIEW</div>
                                        </div>
                                        <div class="event-info">
                                            <h3>BLAZING ABYSS</h3>
                                            <div class="event-details">
                                                <span>STATUS: <span>PREVIEW</span></span>
                                                <span>GATE: <span>GATE 04</span></span>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Barcode Stub -->
                                    <div class="ticket-stub">
                                        <div class="ticket-id">#RN-3D</div>
                                        <div class="barcode"></div>
                                        <div class="stub-price">PARTNER</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </main>

    <!-- Footer Component -->
    <?php
    if (file_exists(__DIR__ . '/includes/footer.php')) {
        require_once __DIR__ . '/includes/footer.php';
    }
    ?>

    <!-- 3D Interactive Motion Script -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const card = document.getElementById('ticketCard');
            if (!card) return;

            function handleMove(clientX, clientY) {
                const rect = card.getBoundingClientRect();
                const x = clientX - rect.left - rect.width / 2;
                const y = clientY - rect.top - rect.height / 2;

                const rotateX = (-y / (rect.height / 2)) * 14;
                const rotateY = (x / (rect.width / 2)) * 14;

                card.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.03, 1.03, 1.03)`;
                card.style.setProperty('--glow-opacity', '0.8');
            }

            function resetCard() {
                card.style.transform = 'rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)';
                card.style.setProperty('--glow-opacity', '0.3');
            }

            document.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                if (e.clientX >= rect.left - 100 && e.clientX <= rect.right + 100 &&
                    e.clientY >= rect.top - 100 && e.clientY <= rect.bottom + 100) {
                    handleMove(e.clientX, e.clientY);
                } else {
                    resetCard();
                }
            });

            card.addEventListener('touchmove', (e) => {
                if (e.touches.length > 0) {
                    handleMove(e.touches[0].clientX, e.touches[0].clientY);
                }
            }, { passive: true });

            card.addEventListener('touchend', resetCard);
        });
    </script>

</body>
</html>