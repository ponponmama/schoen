function applyDocumentTitleFromDataset() {
    const title = document.body.dataset.documentTitle?.trim();
    if (title) {
        document.title = title;
    }
}

function loadStylesheetOnce(href) {
    if (!href) return;
    const id = `schoen-page-css-${href.replace(/[^\w.-]/g, "_")}`;
    if (document.getElementById(id)) return;

    const link = document.createElement("link");
    link.rel = "stylesheet";
    link.href = href;
    link.id = id;
    document.head.appendChild(link);
}

function loadPageStylesheetsFromDataset() {
    const raw = document.body.dataset.pageStylesheet;
    if (!raw) return;

    raw.split(",")
        .map((part) => part.trim())
        .filter(Boolean)
        .forEach(loadStylesheetOnce);
}

document.addEventListener("DOMContentLoaded", async () => {
    applyDocumentTitleFromDataset();
    loadPageStylesheetsFromDataset();

    const root = document.querySelector(".page-slot[data-include]");
    if (!root?.dataset.include) {
        document.dispatchEvent(new CustomEvent("schoen:page-includes-done", { bubbles: true }));
        return;
    }

    const includeHtml = window.includeHtml;
    if (typeof includeHtml !== "function") {
        console.error("includeHtml が未定義です。header.js を app.js より先に読み込んでください。");
        document.dispatchEvent(new CustomEvent("schoen:page-includes-done", { bubbles: true }));
        return;
    }

    await includeHtml(root);
    document.dispatchEvent(new CustomEvent("schoen:page-includes-done", { bubbles: true }));
});
