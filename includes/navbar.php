<?php
/* ==========================================
   RAVE NATIONS - FROSTED WHITE SINGLE-ROW NAVBAR
   ========================================== */
?>

<header class="rn-header" id="rn-header">
    <div class="rn-nav-shell">

        <!-- BRAND (BLACK LOGO CLEARLY VISIBLE ON WHITE NAVBAR) -->
        <a href="/rave/" class="rn-brand" aria-label="Rave Nations Homepage">
            <img src="/rave/assets/design/logo.png" alt="Rave Nations Logo" class="rn-logo-img">
            <span class="rn-brand-text">RAVE<span>NATIONS</span></span>
        </a>

        <!-- NAVIGATION LINKS -->
        <nav class="rn-navigation" id="rn-navigation" aria-label="Main Navigation">
            <a href="/rave/" class="rn-active">Home</a>
            <a href="#events">Events</a>
            <a href="#promotions">Promotions</a>
            <a href="#about">About Us</a>
            <a href="#experience">Experience</a>
            <a href="#platform">Ticketing</a>
            <a href="#contact">Contact</a>

            <!-- MOBILE ACTION BUTTONS -->
            <div class="rn-mobile-actions">
                <a href="#events" class="rn-nav-button rn-button-purple">Get Tickets</a>
               
            </div>
        </nav>

        <!-- DESKTOP ACTIONS -->
        <div class="rn-nav-actions">
            <a href="#events" class="rn-nav-button rn-button-purple">Get Tickets</a>
            <a href="#contact" class="rn-nav-button rn-button-dark">Contact</a>
        </div>

        <!-- MOBILE HAMBURGER BUTTON -->
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
</header>