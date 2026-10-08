
<?php
/* =====================================================
   RAVE NATIONS - OFFICIAL HOMEPAGE
   ===================================================== */

/* SECTION 01 - WEBSITE SETTINGS */

$pageTitle = "Rave Nations | Official Website";

function rnEscape($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/*
   SECTION 02 - FEATURED UPCOMING EVENT

   Replace these sample values when your next
   real event is confirmed.

   If no event is available, change:
   "active" => false
*/

$featuredEvent = [
    "active" => true,
    "title" => "THE NEXT EXPERIENCE",
    "subtitle" => "Feel the Energy. Live the Moment.",
    "date" => "To Be Announced",
    "location" => "Sri Lanka",
    "description" => "Get ready for an unforgettable electronic music experience with Rave Nations.",
    "image" => "assets/images/featured-event.jpg",
    "link" => "#events",
    "button" => "Explore Events"
];


/*
   SECTION 03 - EVENT CARDS

   These are sample showcase cards.
   Replace with confirmed events later.
*/

$events = [
    [
        "title" => "THE NEXT EXPERIENCE",
        "category" => "COMING SOON",
        "description" => "Discover the next chapter of music and entertainment.",
        "image" => "assets/images/event-1.jpg"
    ],
    [
        "title" => "LIVE MUSIC CULTURE",
        "category" => "ENTERTAINMENT",
        "description" => "Experience the energy of unforgettable live performances.",
        "image" => "assets/images/event-2.jpg"
    ],
    [
        "title" => "BEYOND THE MUSIC",
        "category" => "OUR COMMUNITY",
        "description" => "Celebrate music, creativity and the moments that connect us.",
        "image" => "assets/images/event-3.jpg"
    ]
];
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <!-- SECTION 04 - META SETTINGS -->

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <meta name="theme-color" content="#ffffff">

    <meta name="description"
          content="Discover electronic music events, unforgettable performances and live entertainment with Rave Nations Sri Lanka.">

    <title><?php echo rnEscape($pageTitle); ?></title>


    <!-- SECTION 05 - GOOGLE FONT -->

    <link rel="preconnect"
          href="https://fonts.googleapis.com">

    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Titillium+Web:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">


    <!-- SECTION 06 - CSS FILES -->

    <link rel="stylesheet"
          href="/rave/style/navbar.css?v=40">

    <link rel="stylesheet"
          href="/rave/style/style.css?v=40">


    <!-- SECTION 07 - JAVASCRIPT -->

    <script
        src="/rave/assets/js/navbar.js?v=40"
        defer>
    </script>

</head>

<body>

    <!-- ==========================================
         SECTION 08 - EXISTING NAVIGATION BAR
         ========================================== -->

    <?php
    require_once __DIR__ . '/includes/navbar.php';
    ?>


    <main id="main-content">

<!-- ==========================================
     SECTION 09 - UPCOMING EVENT HERO
     ========================================== -->

<section class="rn-featured-section" id="home">

    <div class="rn-container">

        <!-- SECTION 09.1 - EVENT POSTER -->

        <div class="rn-featured-poster-simple">

            <img
                src="assets/images/hero-bg.jpg"
                alt="Rave Nations upcoming event poster"
                fetchpriority="high">

        </div>


        <!-- SECTION 09.2 - EXPLORE EVENTS BUTTON -->

        <div class="rn-featured-action">

            <a href="#events"
               class="rn-featured-explore-btn">

                Explore EventS

                <span aria-hidden="true">↗</span>

            </a>

        </div>

    </div>
<!-- Rave Nations development version -->
</section>

<!-- END UPCOMING EVENT HERO -->


        <!-- ======================================
             SECTION 10 - BRAND STRIP
             ====================================== -->

        <section class="rn-brand-strip"
                 aria-label="Rave Nations highlights">

            <div class="rn-brand-strip-track">

                <span>LIVE ENTERTAINMENT</span>
                <i></i>

                <span>ELECTRONIC MUSIC</span>
                <i></i>

                <span>RAVE CULTURE</span>
                <i></i>

                <span>UNFORGETTABLE MOMENTS</span>

            </div>

        </section>


        <!-- ======================================
             SECTION 11 - INTRODUCTION
             ====================================== -->

        <section class="rn-section rn-intro" id="introduction">

            <div class="rn-container">

                <div class="rn-section-center">

                    <span class="rn-section-label">
                        WELCOME TO RAVE NATIONS
                    </span>

                    <h2 class="rn-section-title">
                        ONE NATION.
                        <span>ENDLESS ENERGY.</span>
                    </h2>

                    <p class="rn-section-description">
                        Music is more than sound.
                        It's a connection, a feeling and an experience.
                        Rave Nations brings people together through
                        music, creativity and live entertainment.
                    </p>

                </div>


                <!-- INTRO FEATURE CARDS -->

                <div class="rn-intro-grid">

                    <article class="rn-intro-card">

                        <div class="rn-intro-number">01</div>

                        <h3>LIVE ENTERTAINMENT</h3>

                        <p>
                            Exciting performances and unforgettable
                            music experiences.
                        </p>

                    </article>


                    <article class="rn-intro-card">

                        <div class="rn-intro-number">02</div>

                        <h3>CREATIVE EXPERIENCES</h3>

                        <p>
                            Music, atmosphere and visuals designed
                            to create lasting memories.
                        </p>

                    </article>


                    <article class="rn-intro-card">

                        <div class="rn-intro-number">03</div>

                        <h3>OUR COMMUNITY</h3>

                        <p>
                            Connecting people who share a passion
                            for music and entertainment.
                        </p>

                    </article>

                </div>

            </div>

        </section>


        <!-- ======================================
             SECTION 12 - UPCOMING EVENTS
             ====================================== -->

        <section class="rn-section rn-events" id="events">

            <div class="rn-container">

                <div class="rn-section-header">

                    <div>

                        <span class="rn-section-label">
                            EXPLORE THE EXPERIENCE
                        </span>

                        <h2 class="rn-section-title">
                            DISCOVER <span>EVENTS.</span>
                        </h2>

                    </div>

                    <p>
                        Discover the music, energy and
                        experiences that define Rave Nations.
                    </p>

                </div>


                <div class="rn-event-grid">

                    <?php foreach ($events as $event): ?>

                    <article class="rn-event-card">

                        <div class="rn-event-image">

                            <img
                                src="<?php echo rnEscape($event['image']); ?>"
                                alt="<?php echo rnEscape($event['title']); ?>"
                                loading="lazy">

                            <span class="rn-event-badge">
                                <?php echo rnEscape($event['category']); ?>
                            </span>

                        </div>


                        <div class="rn-event-body">

                            <h3>
                                <?php echo rnEscape($event['title']); ?>
                            </h3>

                            <p>
                                <?php echo rnEscape($event['description']); ?>
                            </p>

                            <a href="#contact" class="rn-event-link">
                                Enquire Now
                                <span aria-hidden="true">↗</span>
                            </a>

                        </div>

                    </article>

                    <?php endforeach; ?>

                </div>

            </div>

        </section>


        <!-- ======================================
             SECTION 13 - ABOUT RAVE NATIONS
             ====================================== -->

        <section class="rn-section rn-about" id="about">

            <div class="rn-container rn-about-grid">

                <div class="rn-about-image">

                    <img
                        src="assets/images/about-rave.jpg"
                        alt="Electronic music festival atmosphere"
                        loading="lazy">

                    <span class="rn-about-caption">
                        EXPERIENCE THE EXTRAORDINARY
                    </span>

                </div>


                <div class="rn-about-content">

                    <span class="rn-section-label">
                        WHO WE ARE
                    </span>

                    <h2 class="rn-section-title">
                        BEYOND
                        <span>THE MUSIC.</span>
                    </h2>

                    <p>
                        Rave Nations is a celebration of electronic
                        music, creativity and community.
                    </p>

                    <p>
                        We believe extraordinary events are created
                        through meaningful experiences, powerful
                        performances and the people who share them.
                    </p>

                    <a href="#experience" class="rn-dark-button">
                        Explore Our Experience ↗
                    </a>

                </div>

            </div>

        </section>


        <!-- ======================================
             SECTION 14 - OUR EXPERIENCE
             ====================================== -->

        <section class="rn-section rn-experience" id="experience">

            <div class="rn-container">

                <div class="rn-section-center">

                    <span class="rn-section-label">
                        THE RAVE NATIONS EXPERIENCE
                    </span>

                    <h2 class="rn-section-title">
                        MORE THAN
                        <span>A FESTIVAL.</span>
                    </h2>

                    <p class="rn-section-description">
                        A unique combination of music,
                        atmosphere and community.
                    </p>

                </div>


                <div class="rn-experience-grid">

                    <article class="rn-experience-card">

                        <span>01 / MUSIC</span>

                        <h3>THE SOUND</h3>

                        <p>
                            Powerful performances and the
                            energy of electronic music.
                        </p>

                    </article>


                    <article class="rn-experience-card">

                        <span>02 / ATMOSPHERE</span>

                        <h3>THE MOMENT</h3>

                        <p>
                            Immersive visuals, lighting and
                            unforgettable experiences.
                        </p>

                    </article>


                    <article class="rn-experience-card">

                        <span>03 / COMMUNITY</span>

                        <h3>THE PEOPLE</h3>

                        <p>
                            Bringing people together through
                            a shared passion for entertainment.
                        </p>

                    </article>

                </div>

            </div>

        </section>


        <!-- ======================================
             SECTION 15 - TICKETING PREVIEW
             ====================================== -->

        <section class="rn-section rn-ticketing" id="platform">

            <div class="rn-container rn-ticket-grid">

                <div class="rn-ticket-content">

                    <span class="rn-section-label">
                        EVENT TICKETING
                    </span>

                    <h2 class="rn-section-title">
                        YOUR NEXT
                        <span>EXPERIENCE.</span>
                    </h2>

                    <p>
                        Discover upcoming events and explore
                        the experiences waiting for you.
                    </p>

                    <div class="rn-ticket-steps">

                        <div>
                            <span>01</span>
                            <strong>Discover Your Event</strong>
                        </div>

                        <div>
                            <span>02</span>
                            <strong>Explore Ticket Packages</strong>
                        </div>

                        <div>
                            <span>03</span>
                            <strong>Get Ready For The Experience</strong>
                        </div>

                    </div>

                    <a href="#events" class="rn-dark-button">
                        Explore Events ↗
                    </a>

                </div>


                <!-- DESIGN PREVIEW ONLY -->

                <div class="rn-ticket-visual">

                    <div class="rn-ticket-top">
                        <strong>RAVE NATIONS</strong>
                        <span>LIVE EXPERIENCE</span>
                    </div>

                    <div class="rn-ticket-middle">

                        <span>FEEL THE</span>

                        <h3>ENERGY.</h3>

                        <p>LIVE THE MOMENT.</p>

                    </div>

                    <div class="rn-ticket-bottom">
                        <span>YOUR EXPERIENCE STARTS HERE</span>
                        <span>↗</span>
                    </div>

                </div>

            </div>

        </section>


        <!-- ======================================
             SECTION 16 - GALLERY
             ====================================== -->

        <section class="rn-section rn-gallery" id="gallery">

            <div class="rn-container">

                <div class="rn-section-header">

                    <div>

                        <span class="rn-section-label">
                            CAPTURE THE MOMENT
                        </span>

                        <h2 class="rn-section-title">
                            OUR <span>GALLERY.</span>
                        </h2>

                    </div>

                    <p>
                        Explore the atmosphere and unforgettable
                        moments of live entertainment.
                    </p>

                </div>


                <div class="rn-gallery-grid">

                    <img
                        src="assets/images/gallery-1.jpg"
                        alt="Music festival audience"
                        loading="lazy">

                    <img
                        src="assets/images/gallery-2.jpg"
                        alt="Live electronic music performance"
                        loading="lazy">

                    <img
                        src="assets/images/gallery-3.jpg"
                        alt="Festival stage lighting"
                        loading="lazy">

                    <img
                        src="assets/images/gallery-4.jpg"
                        alt="Festival experience"
                        loading="lazy">

                </div>

            </div>

        </section>


        <!-- ======================================
             SECTION 17 - CONTACT
             ====================================== -->

        <section class="rn-section rn-contact" id="contact">

            <div class="rn-container rn-section-center">

                <span class="rn-section-label">
                    CONNECT WITH RAVE NATIONS
                </span>

                <h2 class="rn-section-title">
                    LET'S CREATE
                    <span>MEMORIES.</span>
                </h2>

                <p class="rn-section-description">
                    Contact Rave Nations for event enquiries,
                    collaborations and upcoming experiences.
                </p>

                <a href="mailto:info@ravenations.lk"
                   class="rn-contact-button">

                    Contact Rave Nations ↗

                </a>

            </div>

        </section>

    </main>


    <!-- ==========================================
         SECTION 18 - FOOTER
         ========================================== -->

    <footer class="rn-footer">

        <div class="rn-container">

            <div class="rn-footer-top">

                <div class="rn-footer-brand">

                    <a href="#home">
                        RAVE<span>NATIONS</span>
                    </a>

                    <p>
                        Music. Energy. Community.
                        <br>
                        Unforgettable experiences.
                    </p>

                </div>


                <nav class="rn-footer-links"
                     aria-label="Footer Navigation">

                    <a href="#home">Home</a>
                    <a href="#events">Events</a>
                    <a href="#about">About</a>
                    <a href="#experience">Experience</a>
                    <a href="#contact">Contact</a>

                </nav>

            </div>


            <div class="rn-footer-bottom">

                <span>
                    &copy; <?php echo date('Y'); ?>
                    Rave Nations. All Rights Reserved.
                </span>

                <span>Sri Lanka</span>

            </div>

        </div>

    </footer>

</body>
</html>
