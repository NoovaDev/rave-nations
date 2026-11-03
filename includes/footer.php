<!-- ======================================
     FOOTER COMPONENT
======================================= -->
<footer class="rn-footer">

    <div class="rn-container">

        <div class="rn-footer-grid">

            <!-- COLUMN 1: BRAND INFO -->
            <div class="rn-footer-col">
                <a href="#home" class="rn-footer-logo">
                    RAVE<span>NATIONS</span>
                </a>
                <span class="rn-footer-subbrand">MIDNIGHT ENTERTAINMENT</span>
                <p class="rn-footer-desc">
                    Sri Lanka's preeminent electronic dance music brand. Bringing world-class production, international headliners, and high-octane sensory experiences to the island.
                </p>
            </div>

            <!-- COLUMN 2: NAVIGATION -->
            <div class="rn-footer-col">
                <h4 class="rn-footer-title">NAVIGATION</h4>
                <nav class="rn-footer-nav" aria-label="Footer Navigation">
                    <a href="#home">Home</a>
                    <a href="#about">About Us</a>
                    <a href="#events">Festival Calendar</a>
                    <a href="#experience">Media Gallery</a>
                    <a href="#contact">Contact & Bookings</a>
                </nav>
            </div>

            <!-- COLUMN 3: HEADQUARTERS -->
            <div class="rn-footer-col">
                <h4 class="rn-footer-title">HEADQUARTERS</h4>
                <p class="rn-footer-address">
                    No. 6, Sumanasara Road, Welegoda, Matara
                </p>

                <div class="rn-footer-contacts">
                    <a href="mailto:info@ravenations.lk">info@ravenations.lk</a>
                    <a href="tel:0412238497">0412238497</a>
                </div>

                <span class="rn-desk-badge">
                    🟢 Instant Desk Available
                </span>
            </div>

            <!-- COLUMN 4: NEWSLETTER -->
            <div class="rn-footer-col">
                <h4 class="rn-footer-title">JOIN THE NATION</h4>
                <p class="rn-footer-desc">
                    Get exclusive presale ticket codes and secret afterparty locations straight to your inbox.
                </p>
                <form class="rn-footer-form" onsubmit="event.preventDefault(); alert('Subscribed!');">
                    <input type="email" placeholder="Enter your email" required>
                    <button type="submit">JOIN</button>
                </form>
            </div>

        </div>

        <!-- FOOTER BOTTOM -->
        <div class="rn-footer-bottom">
            <span>&copy; <?= date('Y') ?> RAVE NATIONS & MIDNIGHT ENTERTAINMENT. ALL RIGHTS RESERVED.</span>
            <span>SRI LANKA</span>
        </div>

    </div>

</footer>