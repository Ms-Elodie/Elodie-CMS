"use strict";

document.documentElement.classList.add("admin-menu-ready");

document.addEventListener("DOMContentLoaded", () => {
    const toggle = document.querySelector("[data-admin-menu-toggle]");
    const sidebar = document.querySelector("#admin-sidebar");
    if (!(toggle instanceof HTMLButtonElement) || !(sidebar instanceof HTMLElement)) {
        return;
    }

    const mobileLayout = window.matchMedia("(max-width: 800px)");
    const closeMenu = () => {
        sidebar.classList.remove("is-open");
        toggle.setAttribute("aria-expanded", "false");
    };

    toggle.addEventListener("click", () => {
        const isOpen = toggle.getAttribute("aria-expanded") === "true";
        sidebar.classList.toggle("is-open", !isOpen);
        toggle.setAttribute("aria-expanded", String(!isOpen));
    });

    sidebar.addEventListener("click", (event) => {
        if (event.target instanceof Element && event.target.closest("a")) {
            closeMenu();
        }
    });

    document.addEventListener("click", (event) => {
        if (event.target instanceof Node
            && toggle.getAttribute("aria-expanded") === "true"
            && !sidebar.contains(event.target)
            && !toggle.contains(event.target)) {
            closeMenu();
        }
    });

    document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && toggle.getAttribute("aria-expanded") === "true") {
            closeMenu();
            toggle.focus();
        }
    });

    mobileLayout.addEventListener("change", (event) => {
        if (!event.matches) {
            closeMenu();
        }
    });
});
