"use strict";

document.querySelectorAll("[data-theme-media]").forEach((selector) => {
    const field = selector.closest("label")?.querySelector("[data-theme-url]");
    if (!(field instanceof HTMLInputElement)) {
        return;
    }

    selector.addEventListener("change", () => {
        if (selector.value !== "") {
            field.value = selector.value;
        }
    });

    field.addEventListener("input", () => {
        const selected = Array.from(selector.options).find((option) => option.value === field.value);
        selector.value = selected ? selected.value : "";
    });
});
