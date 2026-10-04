"use strict";

document.querySelectorAll("[data-menu-editor]").forEach((editor) => {
    const list = editor.querySelector("[data-menu-list]");
    const template = editor.querySelector("[data-menu-template]");
    const addButton = editor.querySelector("[data-menu-add]");

    if (!list || !template || !addButton) {
        return;
    }

    const rows = () => Array.from(list.querySelectorAll("[data-menu-item]"));

    const refresh = () => {
        const items = rows();
        items.forEach((row, index) => {
            row.querySelectorAll("[name]").forEach((field) => {
                field.name = field.name.replace(/menu\[\d+\]/, `menu[${index}]`);
            });
            const type = row.querySelector("[data-menu-type]")?.value;
            const urlField = row.querySelector("[data-menu-url-field]");
            const articleField = row.querySelector("[data-menu-article-field]");
            const pageField = row.querySelector("[data-menu-page-field]");
            const urlInput = urlField?.querySelector("input");
            const articleSelect = articleField?.querySelector("select");
            const pageSelect = pageField?.querySelector("select");
            if (urlField && articleField && pageField && urlInput && articleSelect && pageSelect) {
                urlField.hidden = type !== "link";
                articleField.hidden = type !== "article";
                pageField.hidden = type !== "page";
                urlInput.required = type === "link";
                articleSelect.required = type === "article";
                pageSelect.required = type === "page";
            }

            const up = row.querySelector("[data-menu-up]");
            const down = row.querySelector("[data-menu-down]");
            if (up) up.disabled = index === 0;
            if (down) down.disabled = index === items.length - 1;
        });
        addButton.disabled = items.length >= 20;
    };

    addButton.addEventListener("click", () => {
        if (rows().length >= 20) {
            return;
        }
        list.append(template.content.cloneNode(true));
        refresh();
        rows().at(-1)?.querySelector("[data-menu-label]")?.focus();
    });

    editor.addEventListener("change", (event) => {
        if (event.target instanceof HTMLSelectElement && event.target.matches("[data-menu-type]")) {
            refresh();
        }
    });

    editor.addEventListener("click", (event) => {
        if (!(event.target instanceof Element)) {
            return;
        }
        const row = event.target.closest("[data-menu-item]");
        if (!row) {
            return;
        }
        if (event.target.closest("[data-menu-remove]")) {
            row.remove();
        } else if (event.target.closest("[data-menu-up]") && row.previousElementSibling) {
            list.insertBefore(row, row.previousElementSibling);
        } else if (event.target.closest("[data-menu-down]") && row.nextElementSibling) {
            list.insertBefore(row.nextElementSibling, row);
        } else {
            return;
        }
        refresh();
    });

    refresh();
});
