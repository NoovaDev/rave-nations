<!-- ======================================
     FOOTER COMPONENT
======================================= -->
<footer class="rn-footer">

    <div class="rn-container">

        <div class="rn-footer-grid">

            <!-- COLUMN 1: BRAND INFO & SOCIALS -->
            <div class="rn-footer-col">
                <a href="index.php#home" class="rn-footer-logo">
                    RAVE<span>NATIONS</span>
                </a>
                <span class="rn-footer-subbrand">MIDNIGHT ENTERTAINMENT</span>
                <p class="rn-footer-desc">
                    Sri Lanka's preeminent electronic dance music brand. Bringing world-class production, international headliners, and high-octane sensory experiences to the island.
                </p>

                <!-- Social Media Links -->
                <div class="rn-footer-socials">
                    <a href="https://www.facebook.com/ravenationshq" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                        <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
                    </a>
                    <a href="https://www.instagram.com/ravenationshq/" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                        <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
                    </a>
                    <a href="https://www.tiktok.com/@ravenationshq?is_from_webapp=1&sender_device=pc" target="_blank" rel="noopener noreferrer" aria-label="TikTok">
                        <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"></path></svg>
                    </a>
                    <a href="https://www.youtube.com/@RaveNationsMidnight" target="_blank" rel="noopener noreferrer" aria-label="YouTube">
                         <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                             <path d="M23.5 6.2a3 3 0 0 0-2.1-2.1C19.5 3.6 12 3.6 12 3.6s-7.5 0-9.4.5A3 3 0 0 0 .5 6.2 31 31 0 0 0 0 12a31 31 0 0 0 .5 5.8 3 3 0 0 0 2.1 2.1c1.9.5 9.4.5 9.4.5s7.5 0 9.4-.5a3 3 0 0 0 2.1-2.1A31 31 0 0 0 24 12a31 31 0 0 0-.5-5.8ZM9.6 15.6V8.4l6.2 3.6-6.2 3.6Z"/>
</svg>
</a>
                </div>
            </div>

            <!-- COLUMN 2: QUICK LINKS & POLICIES -->
            <div class="rn-footer-col">
                <h4 class="rn-footer-title">QUICK LINKS</h4>
                <nav class="rn-footer-nav" aria-label="Footer Navigation">
                    <a href="index.php#home">Home</a>
                    <a href="index.php#about">About Us</a>
                    <a href="index.php#events">Festival Calendar</a>
                    <a href="privacy.php">Privacy Policy</a>
                    <a href="terms.php">Terms & Conditions</a>
                    <a href="refund.php">Return & Refund Policy</a>
                </nav>
            </div>

            <!-- COLUMN 3: HEADQUARTERS -->
            <div class="rn-footer-col">
                <h4 class="rn-footer-title">HEADQUARTERS</h4>
                <p class="rn-footer-address">
                    No. 6, Sumanasara Road, Welegoda, Matara
                </p>

                <div class="rn-footer-contacts">
                    <a href="mailto:support@ravenations.lk">support@ravenations.lk</a>
                    <a href="tel:+94 74 268 7787">+94 74 268 7787</a>
                </div>
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