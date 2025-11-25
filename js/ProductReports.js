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

// Sample products data
const productsData = [
    {
        name: "Product A",
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
        reorderThreshold: 20
    },
    {
        name: "Product B",
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
        reorderThreshold: 15
    },
    {
        name: "Product C",
        category: "Category B",
        stock: 10,
        inputQty: 100,
        outputQty: 90,
        inputCost: 10,
        salePrice: 20,
        supplier: "Supplier 3",
        inputDate: "2025-11-20",
        outputDate: "2025-11-26",
        reason: "Sale",
        reorderThreshold: 15
    }
];

// Helper to parse dates
function parseDate(str) {
    return new Date(str);
}

// Render report table and chart
// === Full Sales & Inventory Report Function ===
function renderSalesInventoryReport(title, data) {
    const reportContent = document.getElementById("reportContent");
    reportContent.innerHTML = "";

    // Table header
    let html = `<table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>Product</th>
                <th>Category</th>
                <th>Stock</th>
                <th>Input Qty (Available)</th>
                <th>Output Qty</th>
                <th>Sale Price</th>
                <th>Cost</th>
                <th>Profit</th>
                <th>Supplier / Reason</th>
            </tr>
        </thead>
        <tbody>`;

    // Totals for summary
    let totalInput = 0, totalOutput = 0, totalStock = 0, totalProfit = 0;

    data.forEach(p => {
        const calculatedStock = (p.inputQty || 0) - (p.outputQty || 0);
        const profit = (p.salePrice - p.inputCost) * (p.outputQty || 0);

        // Update totals
        totalInput += p.inputQty || 0;
        totalOutput += p.outputQty || 0;
        totalStock += calculatedStock;
        totalProfit += profit;

        html += `<tr${calculatedStock <= p.reorderThreshold ? ' class="table-warning"' : ''}>
            <td>${p.name}</td>
            <td>${p.category}</td>
            <td>${calculatedStock}</td>
            <td>${p.inputQty || '-'} (Available: ${calculatedStock})</td>
            <td>${p.outputQty || '-'}</td>
            <td>${p.salePrice}</td>
            <td>${p.inputCost}</td>
            <td>${profit}</td>
            <td>${p.supplier || '-'} / ${p.reason || '-'}</td>
        </tr>`;
    });

    html += `</tbody></table>`;

    // Add summary row
    html += `<div class="mt-3 fw-bold">
        <p>Total Input Qty: ${totalInput}</p>
        <p>Total Output Qty: ${totalOutput}</p>
        <p>Total Stock: ${totalStock}</p>
        <p>Total Profit: ${totalProfit}</p>
    </div>`;

    reportContent.innerHTML = html;

    // Chart: Inputs, Outputs, Stock, Profits
    const chartContainer = document.createElement("canvas");
    chartContainer.id = "reportChart";
    chartContainer.height = 250;
    reportContent.appendChild(chartContainer);

    const labels = data.map(p => p.name);
    const stockLevels = data.map(p => (p.inputQty || 0) - (p.outputQty || 0));
    const inputQty = data.map(p => p.inputQty);
    const outputQty = data.map(p => p.outputQty);
    const profits = data.map(p => (p.salePrice - p.inputCost) * (p.outputQty || 0));

    new Chart(chartContainer, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                { label: 'Stock', data: stockLevels, backgroundColor: 'rgba(255, 206, 86, 0.6)' },
                { label: 'Input Qty', data: inputQty, backgroundColor: 'rgba(54, 162, 235, 0.6)' },
                { label: 'Output Qty', data: outputQty, backgroundColor: 'rgba(255, 99, 132, 0.6)' },
                { label: 'Profit', data: profits, backgroundColor: 'rgba(75, 192, 192, 0.6)' }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                title: { display: true, text: title }
            },
            scales: { y: { beginAtZero: true } }
        }
    });
}

// === Generate report on button click ===
document.getElementById("generateReport").addEventListener("click", () => {
    const type = document.getElementById("reportType").value;
    const from = document.getElementById("reportDateFrom").value;
    const to = document.getElementById("reportDateTo").value;

    let filtered = [...productsData];
    if (from) filtered = filtered.filter(p => new Date(p.inputDate) >= new Date(from));
    if (to) filtered = filtered.filter(p => new Date(p.inputDate) <= new Date(to));

    switch (type) {
        case 'monthly':
        case 'weekly':
        case 'sales':
            renderSalesInventoryReport("Sales & Inventory Report", filtered);
            break;
        case 'input':
            renderSalesInventoryReport("Product Input Report", filtered.map(p => ({...p, outputQty:'-', reason:'-'})));
            break;
        case 'output':
            renderSalesInventoryReport("Product Output Report", filtered.map(p => ({...p, inputQty:'-', supplier:'-'})));
            break;
        default:
            alert("Please select a report type!");
            return;
    }

    document.getElementById("reportPreview").classList.remove("d-none");
    document.getElementById("reportMeta").innerText = `Generated on ${new Date().toLocaleString()}`;
});
