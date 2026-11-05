<?php
declare(strict_types=1);

// Include centralized database connection from config folder
require_once __DIR__ . '/config/database.php';

$pageTitle = "Rave Nations | Premier Electronic Music Festival Management Company Sri Lanka";
$metaDescription = "Rave Nations is the world-class music festival management company in Sri Lanka, pioneering live electronic music spectacles, coastal arena events, and immersive audio-visual entertainment.";

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
    
    <!-- Open Graph SEO Meta Tags -->
    <meta property="og:title" content="<?= rnEscape($pageTitle) ?>">
    <meta property="og:description" content="<?= rnEscape($metaDescription) ?>">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="en_US">
    <meta property="og:site_name" content="Rave Nations Sri Lanka">
    
    <title><?= rnEscape($pageTitle) ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- CSS Stylesheets -->
    <link rel="stylesheet" href="style/navbar.css?v=75">
    <link rel="stylesheet" href="style/style.css?v=75">
    <link rel="stylesheet" href="style/brand-ribbon.css?v=2.7">
    <link rel="stylesheet" href="style/footer.css?v=75">

    <!-- JavaScript Files -->
    <script src="assets/js/navbar.js?v=75" defer></script>
    <script src="assets/js/analytics.js" defer></script>
</head>

<body>

    <!-- Navigation Bar Component -->
    <?php
    if (file_exists(__DIR__ . '/includes/navbar.php')) {
        require_once __DIR__ . '/includes/navbar.php';
    }
    ?>

    <main id="main-content">

        <!-- Hero Section -->
        <section class="rn-featured-section rn-fade-in" id="home">
            <div class="rn-container">
                <div class="rn-hero-grid">

                    <!-- Hero Left Content -->
                    <div class="rn-hero-text">
                        <span class="rn-section-label">SRI LANKA'S PREMIER MUSIC FESTIVAL MANAGEMENT</span>
                        <h1 class="rn-section-title">
                            EXPERIENCE THE RAVE, <span>BORN UNDER THE MIDNIGHT SKY.</span>
                        </h1>
                        <p class="rn-section-description">
                            Rave Nations is Sri Lanka's leading world-class music festival management company. We redefine live electronic music spectacles across South Asia, merging cutting-edge audio-visual production, immersive coastal stages, and unmatched festival energy.
                        </p>
                        <div class="rn-hero-actions">
                            <a href="#events" class="rn-featured-explore-btn">Explore Events ↗</a>
                            <a href="https://www.ticketsministry.com/concerts/blazing-abyss/6ac7895c772b1" target="_blank" rel="noopener noreferrer" class="rn-dark-button phase2-btn-override">
                                Phase 2 Tickets ↗
                            </a>
                        </div>
                    </div>

                    <!-- Hero Right Dynamic Poster Loop Display -->
                    <div class="rn-hero-poster-wrapper">
                        <?php
                        $posterDir = __DIR__ . '/assets/images/events/';
                        $posterWebPath = 'assets/images/events/';
                        $posters = [];

                        if (is_dir($posterDir)) {
                            $files = scandir($posterDir);
                            foreach ($files as $file) {
                                if ($file !== '.' && $file !== '..') {
                                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                                        $posters[] = $file;
                                    }
                                }
                            }
                        }

                        if (empty($posters)) {
                            $posters[] = 'blazing-abyss-soldout.jpg';
                        }

                        foreach ($posters as $index => $posterFile):
                            $activeClass = ($index === 0) ? 'rn-poster-active' : '';
                        ?>
                            <div class="rn-poster-card rn-hero-poster-item <?= $activeClass ?>">
                                <img src="<?= $posterWebPath ?><?= rnEscape($posterFile) ?>" alt="Rave Nations Sri Lanka Live Electronic Music Event Poster" loading="eager" fetchpriority="high">
                            </div>
                        <?php endforeach; ?>
                    </div>

                </div>
            </div>
        </section>

        <!-- Brand Ribbon Component -->
        <?php
        if (file_exists(__DIR__ . '/includes/brand-ribbon.php')) {
            require_once __DIR__ . '/includes/brand-ribbon.php';
        }
        ?>

        <!-- Introduction Section -->
        <section class="rn-section rn-intro rn-fade-in" id="introduction">
            <div class="rn-container">
                <div class="rn-section-center">
                    <span class="rn-section-label">WELCOME TO RAVE NATIONS SRI LANKA</span>
                    <h2 class="rn-section-title">ONE NATION. <span>ENDLESS ENERGY.</span></h2>
                    <p class="rn-section-description">
                        Music is more than sound. It's a connection, a feeling and an experience. As Sri Lanka's premier music festival management enterprise, Rave Nations brings people together through world-class entertainment, creativity and unforgettable live spectacles.
                    </p>
                </div>

                <div class="rn-intro-grid">
                    <article class="rn-intro-card">
                        <div class="rn-intro-number">01</div>
                        <h3>LIVE ENTERTAINMENT</h3>
                        <p>Exciting performances and world-class festival music experiences across Sri Lanka.</p>
                    </article>
                    <article class="rn-intro-card">
                        <div class="rn-intro-number">02</div>
                        <h3>CREATIVE EXPERIENCES</h3>
                        <p>Immersive coastal stages, atmosphere, and visual productions designed for lasting memories.</p>
                    </article>
                    <article class="rn-intro-card">
                        <div class="rn-intro-number">03</div>
                        <h3>OUR COMMUNITY</h3>
                        <p>Connecting local and international music lovers who share a passion for electronic music culture.</p>
                    </article>
                </div>
            </div>
        </section>

        <!-- Upcoming Events Section -->
        <section class="rn-section rn-events rn-fade-in" id="events">
            <div class="rn-container">
                <div class="rn-section-header">
                    <div>
                        <span class="rn-section-label">EXPLORE THE EXPERIENCE</span>
                        <h2 class="rn-section-title">DISCOVER <span>EVENTS.</span></h2>
                    </div>
                    <p>Discover the electronic music events, energy and spectacles produced by Sri Lanka's leading management company.</p>
                </div>

                <div class="rn-event-grid">
                    <?php
                    $events = [];
                    if (isset($pdo)) {
                        try {
                            $stmt = $pdo->query("SELECT * FROM events ORDER BY event_date ASC");
                            $events = $stmt->fetchAll();
                        } catch (Exception $e) {
                            $events = [];
                        }
                    }

                    if (!empty($events)):
                        foreach ($events as $event):
                    ?>
                        <article class="rn-event-card">
                            <div class="rn-event-image">
                                <a href="event.php?id=<?= (int)$event['id'] ?>">
                                    <img src="<?= rnEscape($event['poster_path']) ?>" alt="<?= rnEscape($event['event_name']) ?>" loading="lazy">
                                </a>
                                <span class="rn-event-badge"><?= strtoupper(rnEscape($event['status'])) ?></span>
                            </div>
                            <div class="rn-event-body">
                                <div class="rn-event-content-wrap">
                                    <h3><?= rnEscape($event['event_name']) ?></h3>
                                    <p><strong><?= date('d F Y', strtotime($event['event_date'])) ?></strong></p>
                                    <p><?= rnEscape($event['location']) ?></p>
                                    <p><?= rnEscape(substr($event['description'], 0, 100)) ?>...</p>
                                </div>
                               <a href="ticket.php" class="rn-featured-explore-btn rn-event-btn">
                                    Get Tickets ↗ 
                                 <!--<a href="event.php?id=<?= (int)$event['id'] ?>" class="rn-featured-explore-btn rn-event-btn">
                                    Get Tickets ↗ -->
                                </a>
                            </div>
                        </article>
                    <?php 
                        endforeach;
                    else:
                    ?>
                        <p style="text-align: center; color: var(--home-text); grid-column: 1 / -1;">No upcoming events available at the moment.</p>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Vision & Mission Section -->
        <section class="rn-section rn-about rn-fade-in" id="about">
            <div class="rn-container rn-about-container">
                <span class="rn-section-label">CORE DIRECTIVE • VISION & MISSION</span>
                <h2 class="rn-section-title" style="margin-bottom: 25px;">INNOVATING <span>THE INDUSTRY.</span></h2>

                <div style="margin-bottom: 40px;">
                    <h3 style="font-size: 22px; font-weight: 800; color: #111114; margin-bottom: 10px;">OUR VISION</h3>
                    <p style="color: var(--home-text); font-size: 18px; line-height: 1.8;">
                        To become the premier world-class music festival management company in Sri Lanka, setting global benchmarks for electronic music culture and live entertainment.
                    </p>
                </div>

                <div>
                    <h3 style="font-size: 22px; font-weight: 800; color: #111114; margin-bottom: 10px;">OUR MISSION</h3>
                    <p style="color: var(--home-text); font-size: 18px; line-height: 1.8;">
                        To innovate the Sri Lankan entertainment and festival industry by curating world-class musical experiences that blend the vibrant energy of electronic music with the distinctive spirit and cultural flavors of the southern coast, delivering extraordinary spectacles for local and international music lovers.
                    </p>
                </div>

                <div style="margin-top: 35px;">
                    <a href="#experience" class="rn-dark-button">Explore Our Experience ↗</a>
                </div>
            </div>
        </section>

        <!-- Experience Section -->
        <section class="rn-section rn-experience rn-fade-in" id="experience">
            <div class="rn-container">
                <div class="rn-section-center">
                    <span class="rn-section-label">THE RAVE NATIONS EXPERIENCE</span>
                    <h2 class="rn-section-title">MORE THAN <span>A FESTIVAL.</span></h2>
                    <p class="rn-section-description">A unique combination of music, coastal atmosphere and community.</p>
                </div>

                <div class="rn-experience-grid">
                    <article class="rn-experience-card">
                        <span>01 / MUSIC</span>
                        <h3>THE SOUND</h3>
                        <p>Powerful audio engineering, international performances, and the unmatched energy of electronic music in Sri Lanka.</p>
                    </article>
                    <article class="rn-experience-card">
                        <span>02 / ATMOSPHERE</span>
                        <h3>THE MOMENT</h3>
                        <p>Immersive coastal stage designs, cutting-edge lighting, and unforgettable festival productions.</p>
                    </article>
                    <article class="rn-experience-card">
                        <span>03 / COMMUNITY</span>
                        <h3>THE PEOPLE</h3>
                        <p>Uniting music enthusiasts across Sri Lanka and beyond through a shared passion for world-class entertainment.</p>
                    </article>
                </div>
            </div>
        </section>

        <!-- Ticketing Preview Section -->
        <section class="rn-section rn-ticketing rn-fade-in" id="platform">
            <div class="rn-container rn-ticket-grid">
                <div class="rn-ticket-content">
                    <span class="rn-section-label">EVENT TICKETING</span>
                    <h2 class="rn-section-title">YOUR NEXT <span>EXPERIENCE.</span></h2>
                    <p>Secure your passes for Sri Lanka's most anticipated electronic music festivals and live coastal events.</p>

                    <div class="rn-ticket-steps">
                        <div><span>01</span><strong>Discover Your Event</strong></div>
                        <div><span>02</span><strong>Explore Ticket Packages</strong></div>
                        <div><span>03</span><strong>Get Ready For The Experience</strong></div>
                    </div>

                    <a href="#events" class="rn-dark-button">Explore Events ↗</a>
                </div>

                <div class="rn-ticket-visual-container">
                    <?php
                    if (file_exists(__DIR__ . '/includes/ticket-3d.php')) {
                        require_once __DIR__ . '/includes/ticket-3d.php';
                    }
                    ?>
                </div>
            </div>
        </section>

      <!-- Contact Section -->
<section class="rn-section rn-contact rn-fade-in" id="contact">
    <div class="rn-container rn-section-center">
        <span class="rn-section-label">CONNECT WITH RAVE NATIONS</span>
        <h2 class="rn-section-title">LET'S CREATE <span>MEMORIES.</span></h2>
        <p class="rn-section-description">Contact Sri Lanka's premier music festival management company for event enquiries, brand collaborations, and upcoming experiences.</p>
        
        <!-- Updated mailto link with pre-filled subject line -->
        <a href="mailto:info@ravenations.lk?subject=Event%20Enquiry%20-%20Rave%20Nations" class="rn-contact-button">
            Contact Rave Nations ↗
        </a>
    </div>
</section>

    </main>

    <!-- Footer Component -->
    <?php
    if (file_exists(__DIR__ . '/includes/footer.php')) {
        require_once __DIR__ . '/includes/footer.php';
    }
    ?>

    <!-- Poster Carousel Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const posterItems = document.querySelectorAll('.rn-hero-poster-item');
            if (posterItems.length > 1) {
                let currentIndex = 0;
                setInterval(() => {
                    posterItems[currentIndex].classList.remove('rn-poster-active');
                    currentIndex = (currentIndex + 1) % posterItems.length;
                    posterItems[currentIndex].classList.add('rn-poster-active');
                }, 4000);
            }
        });
    </script>

</body>
</html>