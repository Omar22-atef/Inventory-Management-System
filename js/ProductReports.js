document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById("product-report-search");
    const cards = document.querySelectorAll(".product-card");
    const noResults = document.getElementById("no-results");
    
    const generateBtn = document.getElementById("generateReport");
    const reportPreview = document.getElementById("reportPreview");
    const reportMeta = document.getElementById("reportMeta");
    const reportContent = document.getElementById("reportContent");

    let currentChart = null;

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
        if (noResults) noResults.classList.toggle("d-none", visible !== 0);
    }

    if (searchInput) searchInput.addEventListener("input", filterCards);

    const productsData = [
        { name: "Product A", 
          category: "Category A", 
          stock: 30, 
          inputQty: 100, 
          outputQty: 70, 
          inputCost: 10, 
          salePrice: 15, 
          supplier: "Supplier 1", 
          inputDate: "2025-11-01", 
          outputDate: "2025-11-05", 
          reason: "Sale", 
          reorderThreshold: 20 },
          
        { name: "Product B", 
            category: "Category B", 
            stock: 180, 
            inputQty: 200, 
            outputQty: 20, 
            inputCost: 8, 
            salePrice: 12, 
            supplier: "Supplier 2", 
            inputDate: "2025-11-03", 
            outputDate: "2025-11-07", 
            reason: "Sale", 
            reorderThreshold: 15 },

        { name: "Product C", 
            category: "Category B", 
            stock: 10, 
            inputQty: 100, 
            outputQty: 90, 
            inputCost: 10, 
            salePrice: 20, 
            supplier: "Supplier 3", 
            inputDate: "2025-11-21", 
            outputDate: "2025-11-27", 
            reason: "Sale", 
            reorderThreshold: 15 }
    ];

     function renderSalesInventoryReport(title, data) {
        if (!reportContent) return;

        // Clear previous content
        reportContent.innerHTML = "";

        // Table
        let html = `<table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Product</th><th>Category</th><th>Stock</th>
                    <th>Input Qty</th><th>Output Qty</th>
                    <th>Sale Price</th><th>Cost</th><th>Profit</th>
                    <th>Supplier / Reason</th>
                </tr>
            </thead><tbody>`;
        data.forEach(p => {
            const profit = (p.salePrice - p.inputCost) * (p.outputQty || 0);
            html += `<tr ${p.stock <= p.reorderThreshold ? 'class="table-warning"' : ''}>
                <td>${p.name}</td>
                <td>${p.category}</td>
                <td>${p.stock}</td>
                <td>${p.inputQty || '-'}</td>
                <td>${p.outputQty || '-'}</td>
                <td>${p.salePrice}</td>
                <td>${p.inputCost}</td>
                <td>${profit}</td>
                <td>${p.supplier || '-'} / ${p.reason || '-'}</td>
            </tr>`;
        });
        html += `</tbody></table>`;
        reportContent.innerHTML = html;

        // Remove old chart
        if (currentChart) {
            currentChart.destroy();
            currentChart = null;
        }
        const oldCanvas = document.getElementById("reportChart");
        if (oldCanvas) oldCanvas.remove();

        // Chart
        const chartCanvas = document.createElement("canvas");
        chartCanvas.id = "reportChart";
        chartCanvas.height = 250;
        reportContent.appendChild(chartCanvas);

        const labels = data.map(p => p.name);
        const stock = data.map(p => p.stock);
        const inputQty = data.map(p => p.inputQty);
        const outputQty = data.map(p => p.outputQty);
        const profits = data.map(p => (p.salePrice - p.inputCost) * (p.outputQty || 0));

        currentChart = new Chart(chartCanvas, {
            type: 'bar',
            data: {
                labels,
                datasets: [
                    { label: 'Stock', data: stock, backgroundColor: 'rgba(255, 206, 86, 0.6)' },
                    { label: 'Input Qty', data: inputQty, backgroundColor: 'rgba(54, 162, 235, 0.6)' },
                    { label: 'Output Qty', data: outputQty, backgroundColor: 'rgba(255, 99, 132, 0.6)' },
                    { label: 'Profit', data: profits, backgroundColor: 'rgba(75, 192, 192, 0.6)' }
                ]
            },
            options: { responsive: true, plugins: { legend: { position: 'top' }, title: { display: true, text: title } }, scales: { y: { beginAtZero: true } } }
        });
    }

    // ====================
    // Generate Button
    // ====================
    if (generateBtn) {
        generateBtn.addEventListener("click", () => {
            const typeSelect = document.getElementById("reportType");
            const fromInput = document.getElementById("reportDateFrom");
            const toInput = document.getElementById("reportDateTo");

            if (!typeSelect) return;
            const type = typeSelect.value;
            const from = fromInput ? fromInput.value : "";
            const to = toInput ? toInput.value : "";

            if (!type) { alert("Please select a report type!"); return; }

            let filtered = [...productsData];

            if ((type === "weekly" || type === "monthly") && (from || to)) {
                if (from) filtered = filtered.filter(p => new Date(p.inputDate) >= new Date(from));
                if (to) filtered = filtered.filter(p => new Date(p.inputDate) <= new Date(to));
            }

            if (type === "sales") filtered = [...productsData];
            if (type === "input") filtered = filtered.map(p => ({ ...p, outputQty: '-', reason: '-' }));
            if (type === "output") filtered = filtered.map(p => ({ ...p, inputQty: '-', supplier: '-' }));

            let title = "";
            switch (type) {
                case "monthly": title = "Monthly Sales & Inventory Report"; break;
                case "weekly": title = "Weekly Sales & Inventory Report"; break;
                case "sales": title = "Full Sales Report"; break;
                case "input": title = "Product Input Report"; break;
                case "output": title = "Product Output Report"; break;
            }

            renderSalesInventoryReport(title, filtered);

            if (reportPreview) reportPreview.classList.remove("d-none");
            if (reportMeta) reportMeta.innerText = `Generated on ${new Date().toLocaleString()}`;
        });
    }
});