/* ==========================================
   RAVE NATIONS - MOBILE NAVBAR TOGGLE SCRIPT
   ========================================== */
document.addEventListener('click', function(event) {
    const menuButton = document.getElementById('rn-menu-button');
    const navMenu = document.getElementById('rn-navigation');

    if (!menuButton || !navMenu) return;

    // Check if the click was on the hamburger button or its inner spans
    const isClickInsideButton = menuButton.contains(event.target);

    if (isClickInsideButton) {
        // Toggle open states
        menuButton.classList.toggle('is-open');
        navMenu.classList.toggle('is-open');

        const expanded = menuButton.getAttribute('aria-expanded') === 'true';
        menuButton.setAttribute('aria-expanded', !expanded);
    } else if (!navMenu.contains(event.target) && navMenu.classList.contains('is-open')) {
        // Optional: Close menu if clicked outside of it on mobile
        menuButton.classList.remove('is-open');
        navMenu.classList.remove('is-open');
        menuButton.setAttribute('aria-expanded', 'false');
    }
});