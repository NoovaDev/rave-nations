<?php
/* ==========================================
   RAVE NATIONS - NAVBAR COMPONENT
   ========================================== */
?>

<header class="rn-header" id="rn-header">

    <div class="rn-nav-shell">

        <!-- SECTION 01 - TOP ROW -->
        <div class="rn-nav-top">

            <div class="rn-nav-spacer"
                 aria-hidden="true"></div>

            <!-- LOGO WITH COMPANY IMAGE -->
            <a href="/rave/"
               class="rn-brand"
               aria-label="Rave Nations Homepage">
                <img src="/rave/assets/design/logo.png" alt="Rave Nations Logo" class="rn-logo-img">
                <span class="rn-brand-text">RAVE<span>NATIONS</span></span>
            </a>

            <!-- DESKTOP BUTTONS -->
            <div class="rn-nav-actions">

                <a href="#contact"
                   class="rn-nav-button rn-button-dark">
                    Contact Us
                </a>

                <a href="#events"
                   class="rn-nav-button rn-button-purple">
                    Explore Events
                </a>

            </div>

            <!-- MOBILE MENU BUTTON (Added id="rn-menu-button" for JS targeting) -->
            <button
                class="rn-menu-button"
                id="rn-menu-button"
                type="button"
                aria-label="Open navigation menu"
                aria-controls="rn-navigation"
                aria-expanded="false">

                <span></span>
                <span></span>
                <span></span>

            </button>

        </div>

        <!-- SECTION 02 - DIVIDER -->
        <div class="rn-nav-divider"></div>

        <!-- SECTION 03 - LINKS -->
        <nav class="rn-navigation"
             id="rn-navigation"
             aria-label="Main Navigation">

            <a href="/rave/" class="rn-active">Home</a>
            <a href="#events">Events</a>
            <a href="#about">About Us</a>
            <a href="#experience">Experience</a>
            <a href="#platform">Ticketing</a>
            <a href="#contact">Contact</a>

            <!-- MOBILE BUTTONS -->
            <div class="rn-mobile-actions">

                <a href="#contact"
                   class="rn-nav-button rn-button-dark">
                    Contact Us
                </a>

                <a href="#events"
                   class="rn-nav-button rn-button-purple">
                    Explore Events
                </a>

            </div>

        </nav>

    </div>

</header>