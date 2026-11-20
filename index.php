<?php
declare(strict_types=1);

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
    <meta name="theme-color" content="#0e0b18">
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
    <link rel="stylesheet" href="style/navbar.css?v=102">
    <link rel="stylesheet" href="style/style.css?v=102">
    <link rel="stylesheet" href="style/brand-ribbon.css?v=102">
    <link rel="stylesheet" href="style/footer.css?v=76">

    <!-- JavaScript Files -->
    <script src="assets/js/navbar.js?v=76" defer></script>
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

        <!-- Hero Section (3D Layered Stage Design) -->
        <section class="rn-featured-section" id="home">
            <div class="rn-hero-ambient-glow" aria-hidden="true"></div>

            <div class="rn-container">
                <div class="rn-hero-split">

                    <!-- Left Editorial Content -->
                    <div class="rn-hero-copy" data-rn-reveal>
                        <span class="rn-section-label">
                            <i class="rn-hero-pulse" aria-hidden="true"></i>
                            SRI LANKA'S PREMIER MUSIC FESTIVAL MANAGEMENT
                        </span>

                        <h1 class="rn-section-title">
                            EXPERIENCE THE RAVE,
                            <span>BORN UNDER THE MIDNIGHT SKY.</span>
                        </h1>

                        <p class="rn-section-description">
                            Rave Nations is Sri Lanka's leading world-class music festival management company. We redefine live electronic music spectacles across South Asia, merging cutting-edge audio-visual production, immersive coastal stages, and unmatched festival energy.
                        </p>

                        <div class="rn-hero-actions">
                            <a href="https://www.ticketsministry.com/concerts/blazing-abyss/6ac7895c772b1"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="rn-dark-button phase2-btn-override">
                                Phase 2 Tickets ↗
                            </a>

                            <a href="#events" class="rn-featured-explore-btn">Explore Events ↓</a>
                        </div>

                        <div class="rn-hero-highlights" aria-label="Production Standards">
                            <span>LIVE STAGES</span>
                            <i aria-hidden="true"></i>
                            <span>COASTAL ARENAS</span>
                            <i aria-hidden="true"></i>
                            <span>360° AUDIO-VISUAL</span>
                        </div>
                    </div>

                    <!-- Right 3D Fanned Poster Deck -->
                    <div class="rn-hero-visual" data-rn-reveal>
                        <?php
                        $posterDir = __DIR__ . '/assets/images/events/';
                        $posterWebPath = 'assets/images/events/';
                        $posters = [];

                        if (is_dir($posterDir)) {
                            $files = scandir($posterDir);
                            if ($files !== false) {
                                foreach ($files as $file) {
                                    if ($file !== '.' && $file !== '..') {
                                        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                                            $posters[] = $file;
                                        }
                                    }
                                }
                                natcasesort($posters);
                                $posters = array_values($posters);
                            }
                        }

                        if (empty($posters)) {
                            $posters[] = 'blazing-abyss-soldout.jpg';
                        }
                        ?>

                        <div class="rn-hero-deck-stage" id="rn-hero-deck">
                            <div class="rn-deck-plate rn-deck-plate-left" aria-hidden="true"></div>
                            <div class="rn-deck-plate rn-deck-plate-right" aria-hidden="true"></div>

                            <div class="rn-hero-poster-wrapper">
                                <?php foreach ($posters as $index => $posterFile):
                                    $isFirst = ($index === 0);
                                ?>
                                    <div class="rn-poster-card rn-hero-poster-item <?= $isFirst ? 'rn-poster-active' : '' ?>">
                                        <img
                                            src="<?= $posterWebPath ?><?= rnEscape($posterFile) ?>"
                                            alt="Rave Nations Sri Lanka Live Electronic Music Event Poster"
                                            loading="<?= $isFirst ? 'eager' : 'lazy' ?>"
                                            decoding="async"
                                            <?= $isFirst ? 'fetchpriority="high"' : '' ?>
                                        >
                                    </div>
                                <?php endforeach; ?>

                                <span class="rn-deck-badge">FEATURED</span>
                            </div>
                        </div>

                        <?php if (count($posters) > 1): ?>
                            <div class="rn-deck-controls" aria-label="Featured Poster Controls">
                                <button type="button" class="rn-deck-arrow" id="rn-deck-prev" aria-label="Previous poster">←</button>

                                <div class="rn-poster-dots" id="rn-poster-dots">
                                    <?php foreach ($posters as $index => $posterFile): ?>
                                        <button
                                            type="button"
                                            class="rn-poster-dot <?= $index === 0 ? 'is-active' : '' ?>"
                                            data-slide-index="<?= $index ?>"
                                            aria-label="Show poster <?= $index + 1 ?>">
                                        </button>
                                    <?php endforeach; ?>
                                </div>

                                <button type="button" class="rn-deck-arrow" id="rn-deck-next" aria-label="Next poster">→</button>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </section>

        <!-- Minimalist Editorial Ticker Ribbon -->
        <?php
        if (file_exists(__DIR__ . '/includes/brand-ribbon.php')) {
            require_once __DIR__ . '/includes/brand-ribbon.php';
        }
        ?>

        <!-- Introduction Section -->
        <section class="rn-section rn-intro" id="introduction">
            <div class="rn-container">
                <div class="rn-section-center" data-rn-reveal>
                    <span class="rn-section-label">WELCOME TO RAVE NATIONS SRI LANKA</span>

                    <h2 class="rn-section-title">
                        ONE NATION. <span>ENDLESS ENERGY.</span>
                    </h2>

                    <p class="rn-section-description">
                        Music is more than sound. It's a connection, a feeling and an experience. As Sri Lanka's premier music festival management enterprise, Rave Nations brings people together through world-class entertainment, creativity and unforgettable live spectacles.
                    </p>
                </div>

                <div class="rn-intro-grid">
                    <article class="rn-intro-card rn-delay-1" data-rn-reveal>
                        <div class="rn-intro-number">01</div>
                        <h3>LIVE ENTERTAINMENT</h3>
                        <p>Exciting performances and world-class festival music experiences across Sri Lanka.</p>
                    </article>

                    <article class="rn-intro-card rn-delay-2" data-rn-reveal>
                        <div class="rn-intro-number">02</div>
                        <h3>CREATIVE EXPERIENCES</h3>
                        <p>Immersive coastal stages, atmosphere, and visual productions designed for lasting memories.</p>
                    </article>

                    <article class="rn-intro-card rn-delay-3" data-rn-reveal>
                        <div class="rn-intro-number">03</div>
                        <h3>OUR COMMUNITY</h3>
                        <p>Connecting local and international music lovers who share a passion for electronic music culture.</p>
                    </article>
                </div>
            </div>
        </section>

        <!-- Centered Upcoming Events Section -->
        <section class="rn-section rn-events" id="events">
            <div class="rn-container">

                <div class="rn-events-center-header" data-rn-reveal>
                    <span class="rn-section-label">EXPLORE THE EXPERIENCE</span>
                    <h2 class="rn-section-title">DISCOVER <span>EVENTS.</span></h2>
                    <p>
                        Discover the electronic music events, energy and spectacles produced by Sri Lanka's leading management company.
                    </p>
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
                            $eventTimestamp = strtotime((string) $event['event_date']);
                            if ($eventTimestamp === false) {
                                $eventTimestamp = 0;
                            }

                            $eventDay = date('d', $eventTimestamp);
                            $eventMonth = strtoupper(date('M', $eventTimestamp));
                            $eventWeekday = strtoupper(date('D', $eventTimestamp));
                            $eventDescription = trim((string) ($event['description'] ?? ''));
                    ?>
                        <article class="rn-event-card" data-rn-reveal>
                            <a
                                class="rn-event-card-link"
                                href="eventinfor.php?id=<?= (int) $event['id'] ?>"
                                aria-label="Explore <?= rnEscape($event['event_name']) ?>"
                            >
                                <img
                                    class="rn-event-card-image"
                                    src="<?= rnEscape($event['poster_path']) ?>"
                                    alt="<?= rnEscape($event['event_name']) ?>"
                                    loading="lazy"
                                    decoding="async"
                                >

                                <span class="rn-event-badge">
                                    <?= strtoupper(rnEscape($event['status'] ?? 'UPCOMING')) ?>
                                </span>

                                <div class="rn-event-card-overlay">
                                    <h3><?= rnEscape($event['event_name']) ?></h3>

                                    <div class="rn-event-card-footer">
                                        <div
                                            class="rn-event-date-tile"
                                            aria-label="<?= rnEscape(date('d F Y', $eventTimestamp)) ?>"
                                        >
                                            <span class="rn-event-date-month"><?= rnEscape($eventMonth) ?></span>
                                            <strong><?= rnEscape($eventDay) ?></strong>
                                            <span class="rn-event-date-weekday"><?= rnEscape($eventWeekday) ?></span>
                                        </div>

                                        <div class="rn-event-card-meta">
                                            <strong class="rn-event-location"><?= rnEscape($event['location']) ?></strong>

                                            <?php if ($eventDescription !== ''): ?>
                                                <span class="rn-event-description">
                                                    <?= rnEscape(mb_strimwidth($eventDescription, 0, 85, '…', 'UTF-8')) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <span class="rn-event-card-arrow" aria-hidden="true">↗</span>
                                    </div>
                                </div>
                            </a>
                        </article>
                    <?php
                        endforeach;
                    else:
                    ?>
                        <p class="rn-no-events">
                            No upcoming events available at the moment.
                        </p>
                    <?php endif; ?>
                </div>

            </div>
        </section>

        <!-- Vision & Mission Section -->
        <section class="rn-section rn-about" id="about">
            <div class="rn-container rn-about-container">

                <div data-rn-reveal>
                    <span class="rn-section-label">CORE DIRECTIVE • VISION &amp; MISSION</span>
                    <h2 class="rn-section-title">
                        INNOVATING <span>THE INDUSTRY.</span>
                    </h2>
                </div>

                <div class="rn-about-cards">
                    <article class="rn-about-card rn-delay-1" data-rn-reveal>
                        <h3>OUR VISION</h3>
                        <p>
                            To become the premier world-class music festival management company in Sri Lanka, setting global benchmarks for electronic music culture and live entertainment.
                        </p>
                    </article>

                    <article class="rn-about-card rn-delay-2" data-rn-reveal>
                        <h3>OUR MISSION</h3>
                        <p>
                            To innovate the Sri Lankan entertainment and festival industry by curating world-class musical experiences that blend the vibrant energy of electronic music with the distinctive spirit and cultural flavors of the southern coast, delivering extraordinary spectacles for local and international music lovers.
                        </p>
                    </article>
                </div>

                <div class="rn-about-cta" data-rn-reveal>
                    <a href="#experience" class="rn-dark-button">Explore Our Experience ↗</a>
                </div>

            </div>
        </section>

        <!-- Experience Section -->
        <section class="rn-section rn-experience" id="experience">
            <div class="rn-container">

                <div class="rn-section-center" data-rn-reveal>
                    <span class="rn-section-label">THE RAVE NATIONS EXPERIENCE</span>

                    <h2 class="rn-section-title">
                        MORE THAN <span>A FESTIVAL.</span>
                    </h2>

                    <p class="rn-section-description">
                        A unique combination of music, coastal atmosphere and community.
                    </p>
                </div>

                <div class="rn-experience-grid">
                    <article class="rn-experience-card rn-delay-1" data-rn-reveal>
                        <span>01 / MUSIC</span>
                        <h3>THE SOUND</h3>
                        <p>Powerful audio engineering, international performances, and the unmatched energy of electronic music in Sri Lanka.</p>
                    </article>

                    <article class="rn-experience-card rn-delay-2" data-rn-reveal>
                        <span>02 / ATMOSPHERE</span>
                        <h3>THE MOMENT</h3>
                        <p>Immersive coastal stage designs, cutting-edge lighting, and unforgettable festival productions.</p>
                    </article>

                    <article class="rn-experience-card rn-delay-3" data-rn-reveal>
                        <span>03 / COMMUNITY</span>
                        <h3>THE PEOPLE</h3>
                        <p>Uniting music enthusiasts across Sri Lanka and beyond through a shared passion for world-class entertainment.</p>
                    </article>
                </div>

            </div>
        </section>

        <!-- Ticketing Preview Section -->
        <section class="rn-section rn-ticketing" id="platform">
            <div class="rn-container rn-ticket-grid">

                <div class="rn-ticket-content" data-rn-reveal>
                    <span class="rn-section-label">EVENT TICKETING</span>

                    <h2 class="rn-section-title">
                        YOUR NEXT <span>EXPERIENCE.</span>
                    </h2>

                    <p>
                        Secure your passes for Sri Lanka's most anticipated electronic music festivals and live coastal events.
                    </p>

                    <div class="rn-ticket-steps">
                        <div><span>01</span><strong>Discover Your Event</strong></div>
                        <div><span>02</span><strong>Explore Ticket Packages</strong></div>
                        <div><span>03</span><strong>Get Ready For The Experience</strong></div>
                    </div>

                    <a href="#events" class="rn-dark-button">Explore Events ↗</a>
                </div>

                <div class="rn-ticket-visual-container rn-delay-2" data-rn-reveal>
                    <?php
                    if (file_exists(__DIR__ . '/includes/ticket-3d.php')) {
                        require_once __DIR__ . '/includes/ticket-3d.php';
                    }
                    ?>
                </div>

            </div>
        </section>

        <!-- Contact Section -->
        <section class="rn-section rn-contact" id="contact">
            <div class="rn-container rn-section-center" data-rn-reveal>

                <span class="rn-section-label">CONNECT WITH RAVE NATIONS</span>

                <h2 class="rn-section-title">
                    LET'S CREATE <span>MEMORIES.</span>
                </h2>

                <p class="rn-section-description">
                    Contact Sri Lanka's premier music festival management company for event enquiries, brand collaborations, and upcoming experiences.
                </p>

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

    <!-- Unified Scroll Reveal & Poster Carousel Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            /* 1. Smooth Scroll Reveal */
            const revealEls = document.querySelectorAll('[data-rn-reveal]');
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries, obs) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-visible');
                            obs.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.1, rootMargin: '0px 0px -30px 0px' });

                revealEls.forEach(el => observer.observe(el));
            } else {
                revealEls.forEach(el => el.classList.add('is-visible'));
            }

            /* 2. Hero Poster Deck Carousel + Controls + Touch Swipe */
            const posterItems = document.querySelectorAll('.rn-hero-poster-item');
            const posterDots = document.querySelectorAll('.rn-poster-dot');
            const deckEl = document.getElementById('rn-hero-deck');
            const prevDeckBtn = document.getElementById('rn-deck-prev');
            const nextDeckBtn = document.getElementById('rn-deck-next');

            if (posterItems.length > 1) {
                let currentIndex = 0;
                let timer = null;
                let touchStartX = 0;

                const showPoster = (nextIndex) => {
                    posterItems[currentIndex].classList.remove('rn-poster-active');
                    if (posterDots[currentIndex]) posterDots[currentIndex].classList.remove('is-active');

                    currentIndex = (nextIndex + posterItems.length) % posterItems.length;

                    posterItems[currentIndex].classList.add('rn-poster-active');
                    if (posterDots[currentIndex]) posterDots[currentIndex].classList.add('is-active');
                };

                const startLoop = () => {
                    clearInterval(timer);
                    timer = setInterval(() => showPoster(currentIndex + 1), 4500);
                };

                posterDots.forEach((dot, idx) => {
                    dot.addEventListener('click', () => {
                        showPoster(idx);
                        startLoop();
                    });
                });

                if (prevDeckBtn) {
                    prevDeckBtn.addEventListener('click', () => {
                        showPoster(currentIndex - 1);
                        startLoop();
                    });
                }

                if (nextDeckBtn) {
                    nextDeckBtn.addEventListener('click', () => {
                        showPoster(currentIndex + 1);
                        startLoop();
                    });
                }

                if (deckEl) {
                    deckEl.addEventListener('touchstart', (e) => {
                        touchStartX = e.changedTouches[0].clientX;
                    }, { passive: true });

                    deckEl.addEventListener('touchend', (e) => {
                        const diff = e.changedTouches[0].clientX - touchStartX;
                        if (Math.abs(diff) > 40) {
                            showPoster(diff < 0 ? currentIndex + 1 : currentIndex - 1);
                            startLoop();
                        }
                    }, { passive: true });
                }

                startLoop();
            }

        });
    </script>

</body>
</html>