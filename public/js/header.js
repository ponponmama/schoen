async function includeHtml(root) {
    const includePath = root.dataset.include;
    if (!includePath) return;

    try {
        const response = await fetch(includePath, { cache: "no-store" });
        if (!response.ok) {
            throw new Error(`Failed to load include: ${response.status}`);
        }

        root.innerHTML = await response.text();
    } catch (error) {
        console.error(error);
    }
}

window.includeHtml = includeHtml;

function closeMenu(toggleButton, menuPanel) {
    toggleButton.setAttribute("aria-expanded", "false");
    toggleButton.classList.remove("is-open");
    menuPanel.classList.remove("is-open");
}

function initHamburgerMenu(root) {
    const toggleButton = root.querySelector("[data-menu-toggle]");
    const menuPanel = root.querySelector("[data-menu-panel]");

    if (!toggleButton || !menuPanel) return;

    toggleButton.addEventListener("click", () => {
        const isOpen = toggleButton.getAttribute("aria-expanded") === "true";
        const nextIsOpen = !isOpen;

        toggleButton.setAttribute("aria-expanded", String(nextIsOpen));
        toggleButton.classList.toggle("is-open", nextIsOpen);
        menuPanel.classList.toggle("is-open", nextIsOpen);
    });

    menuPanel.querySelectorAll("a[href]").forEach((link) => {
        link.addEventListener("click", () => {
            closeMenu(toggleButton, menuPanel);
        });
    });
}

document.addEventListener("DOMContentLoaded", async () => {
    const includeRoot = document.querySelector("#hamburger-menu-root");
    if (!includeRoot) return;

    await includeHtml(includeRoot);
    initHamburgerMenu(includeRoot);
});
