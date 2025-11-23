// js/ProductReports.js

document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById("product-report-search");
    const cards = document.querySelectorAll(".product-card");
    const noResults = document.getElementById("no-results");

    function filterCards() {
        const q = searchInput.value.toLowerCase().trim();
        let visible = 0;

        cards.forEach(card => {
            const name = card.dataset.name.toLowerCase();
            const category = card.dataset.category.toLowerCase();
            const matches = name.includes(q) || category.includes(q);

            card.style.display = matches ? "" : "none";
            if (matches) visible++;
        });

        if (noResults) {
            noResults.classList.toggle("d-none", visible !== 0);
        }
    }

    if (searchInput) {
        searchInput.addEventListener("input", filterCards);
    }
});
