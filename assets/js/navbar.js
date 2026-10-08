
"use strict";

/* ==========================================
   RAVE NATIONS - MOBILE NAVIGATION
   ========================================== */

document.addEventListener("DOMContentLoaded", () => {

    // SECTION 01 - FIND ELEMENTS
    const button = document.querySelector(".rn-menu-button");
    const navigation = document.querySelector(".rn-navigation");

    if (!button || !navigation) return;

    // SECTION 02 - MENU STATE
    function setMenu(open) {
        button.classList.toggle("is-open", open);
        navigation.classList.toggle("is-open", open);

        button.setAttribute("aria-expanded", String(open));

        button.setAttribute(
            "aria-label",
            open
                ? "Close navigation menu"
                : "Open navigation menu"
        );
    }

    // SECTION 03 - TOGGLE MOBILE MENU
    button.addEventListener("click", () => {
        const open = button.getAttribute("aria-expanded") !== "true";
        setMenu(open);
    });

    // SECTION 04 - CLOSE ON LINK CLICK
    navigation.querySelectorAll("a").forEach(link => {
        link.addEventListener("click", () => setMenu(false));
    });

    // SECTION 05 - ESCAPE KEY
    document.addEventListener("keydown", event => {
        if (event.key === "Escape") setMenu(false);
    });

    // SECTION 06 - CLICK OUTSIDE
    document.addEventListener("click", event => {
        if (
            !button.contains(event.target) &&
            !navigation.contains(event.target)
        ) {
            setMenu(false);
        }
    });

    // SECTION 07 - WINDOW RESIZE
    window.addEventListener("resize", () => {
        if (window.innerWidth > 900) setMenu(false);
    });

});
