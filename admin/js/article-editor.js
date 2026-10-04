(() => {
    const editorRoots = document.querySelectorAll("[data-article-editor]");

    for (const root of editorRoots) {
        const source = root.querySelector("textarea[name='contenu']");
        const canvas = root.querySelector("[data-editor-canvas]");
        const form = root.closest("form");
        const formatSelect = root.querySelector("[data-content-format]");
        const toolbar = root.querySelector(".article-editor-toolbar");
        const imageControls = root.querySelector(".article-image-insert");
        const imageSelect = root.querySelector("[data-editor-image]");
        const imageButton = root.querySelector("[data-insert-image]");
        const markupHelp = root.querySelector("[data-markup-help]");
        if (!(source instanceof HTMLTextAreaElement)
            || !(canvas instanceof HTMLElement)
            || !(form instanceof HTMLFormElement)
            || !(formatSelect instanceof HTMLSelectElement)
            || !(toolbar instanceof HTMLElement)
            || !(imageControls instanceof HTMLElement)
            || !(imageSelect instanceof HTMLSelectElement)
            || !(imageButton instanceof HTMLButtonElement)) {
            continue;
        }

        const error = root.querySelector("[data-editor-error]");
        let savedRange = null;

        const saveSelection = () => {
            const selection = window.getSelection();
            if (selection && selection.rangeCount > 0
                && canvas.contains(selection.getRangeAt(0).commonAncestorContainer)) {
                savedRange = selection.getRangeAt(0).cloneRange();
            }
        };

        const restoreSelection = () => {
            canvas.focus();
            const selection = window.getSelection();
            if (!selection) {
                return null;
            }
            const range = savedRange && canvas.contains(savedRange.commonAncestorContainer)
                ? savedRange.cloneRange()
                : document.createRange();
            if (!savedRange || !canvas.contains(savedRange.commonAncestorContainer)) {
                range.selectNodeContents(canvas);
                range.collapse(false);
            }
            selection.removeAllRanges();
            selection.addRange(range);
            return range;
        };

        let activeFormat = formatSelect.value;
        canvas.innerHTML = activeFormat === "visual" ? source.value : "";

        const setMode = () => {
            const isVisual = formatSelect.value === "visual";
            toolbar.hidden = !isVisual;
            imageControls.hidden = !isVisual;
            if (markupHelp instanceof HTMLElement) {
                markupHelp.hidden = isVisual;
            }
            canvas.hidden = !isVisual;
            source.hidden = isVisual;
            source.required = !isVisual;
        };

        setMode();
        document.addEventListener("selectionchange", saveSelection);

        toolbar.addEventListener("mousedown", (event) => {
            if (event.target instanceof Element && event.target.closest("button")) {
                saveSelection();
                event.preventDefault();
            }
        });

        canvas.addEventListener("mouseup", saveSelection);
        canvas.addEventListener("keyup", saveSelection);
        canvas.addEventListener("input", () => {
            saveSelection();
            if (error instanceof HTMLElement) {
                error.hidden = true;
            }
        });

        canvas.addEventListener("paste", (event) => {
            event.preventDefault();
            const text = event.clipboardData?.getData("text/plain") || "";
            document.execCommand("insertText", false, text);
            source.value = canvas.innerHTML;
        });

        formatSelect.addEventListener("change", () => {
            if (activeFormat === "visual") {
                source.value = canvas.innerHTML;
            }
            if (formatSelect.value === "visual" && activeFormat !== "visual") {
                canvas.textContent = source.value;
            }
            activeFormat = formatSelect.value;
            setMode();
        });

        root.addEventListener("click", (event) => {
            if (!(event.target instanceof Element)) {
                return;
            }
            const imageControl = event.target.closest("[data-insert-image]");
            if (imageControl instanceof HTMLButtonElement) {
                const option = imageSelect.selectedOptions[0];
                const imageUrl = imageSelect.value;
                if (!imageUrl || !option) {
                    return;
                }

                const range = restoreSelection();
                if (!range) {
                    return;
                }
                range.deleteContents();
                const image = document.createElement("img");
                image.src = imageUrl;
                image.alt = option.dataset.alt || "";
                range.insertNode(image);
                range.setStartAfter(image);
                range.collapse(true);
                const selection = window.getSelection();
                if (selection) {
                    selection.removeAllRanges();
                    selection.addRange(range);
                    savedRange = range.cloneRange();
                }
                source.value = canvas.innerHTML;
                return;
            }

            const button = event.target.closest("[data-editor-command]");
            if (!(button instanceof HTMLButtonElement)) {
                return;
            }
            const command = button.dataset.editorCommand;
            if (command) {
                restoreSelection();
                document.execCommand(command, false, button.dataset.editorValue || null);
                source.value = canvas.innerHTML;
            }
        });

        form.addEventListener("submit", (event) => {
            const isVisual = formatSelect.value === "visual";
            if (isVisual) {
                source.value = canvas.innerHTML;
            }
            const hasContent = isVisual
                ? canvas.textContent.trim() !== "" || canvas.querySelector("img") !== null
                : source.value.trim() !== "";
            if (!hasContent) {
                event.preventDefault();
                if (error instanceof HTMLElement) {
                    error.textContent = root.dataset.emptyArticle || "";
                    error.hidden = false;
                }
                if (isVisual) {
                    canvas.focus();
                } else {
                    source.focus();
                }
            }
        });
    }
})();
