let productsData = [];
let stockLogsPage = 1;
let charts = {};

document.addEventListener("DOMContentLoaded", async () => {
    await loadProductsData();

    document.getElementById('generateReport')?.addEventListener('click', () => generateReport(false));
    document.getElementById('generateFullReport')?.addEventListener('click', () => generateReport(true));
    document.getElementById('downloadPdf')?.addEventListener('click', downloadPdf);
    document.getElementById('exportExcel')?.addEventListener('click', exportCsv);
});

async function generateReport(isFull = false) {
    const typeEl = document.getElementById('reportType');
    const type = (typeEl && typeEl.value) || '';
    const from = document.getElementById('reportDateFrom').value;
    const to = document.getElementById('reportDateTo').value;

    if (!isFull && !type) {
        return alert('Please choose a report type before generating.');
    }

    let filtered = [...productsData];

    let fromDate = from ? new Date(from) : null;
    let toDate = to ? new Date(to) : null;

    if (!fromDate && !toDate) {
        if (type === 'weekly') fromDate = new Date(Date.now() - 7 * 24 * 60 * 60 * 1000);
        if (type === 'monthly') fromDate = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000);
    }

    let dateFilteredMap = null;
    if (fromDate || toDate) {
        const logs = await fetchStockLogsAll();
        dateFilteredMap = computeProductQuantitiesFromLogs(logs, fromDate, toDate);

        filtered = filtered.map(p => {
            const m = dateFilteredMap[p.id] || { inputQty: 0, outputQty: 0 };
            return { ...p, inputQty: m.inputQty, outputQty: m.outputQty, calculatedStock: (m.inputQty - m.outputQty) };
        });
    }

    if (type === 'input') filtered = filtered.filter(p => (p.inputQty || 0) > 0);
    if (type === 'output' || type === 'sales') filtered = filtered.filter(p => (p.outputQty || 0) > 0);

    document.getElementById('reportPreview')?.classList.remove('d-none');
    document.getElementById('reportMeta').innerText = `Generated on ${new Date().toLocaleString()}`;

    window.lastReportData = filtered.map(p => {
        const calculatedStock = (p.calculatedStock != null) ? p.calculatedStock : ((p.inputQty || 0) - (p.outputQty || 0));
        const costUnit = Number(p.inputCost ?? p.input_cost ?? 0) || 0;
        const priceUnit = Number(p.salePrice ?? p.sale_price ?? 0) || 0;
        const profitUnit = priceUnit - costUnit;
        const totalProfit = profitUnit * (p.outputQty || 0);
        const status = (calculatedStock <= 0) ? 'Out of stock' : ((calculatedStock <= (p.reorderThreshold || p.reorder_threshold || 0)) ? 'Low stock' : 'Good');
        return { ...p, calculatedStock, costUnit, priceUnit, profitUnit, totalProfit, status };
    });

    renderSalesInventoryReport((isFull ? 'Full' : (type || 'Custom')) + ' Report', window.lastReportData);
    renderCharts(window.lastReportData);
    renderLowStockAlerts(window.lastReportData);
}

async function loadProductsData() {
    try {
        const res = await fetch('/api/reports/sales-inventory');
        if (!res.ok) {
            throw new Error(`HTTP error! status: ${res.status}`);
        }
        const data = await res.json();

        productsData = Array.isArray(data) ? data : (data.data || []);

        if (!productsData || productsData.length === 0) {
            console.info('reports endpoint empty — fallback to products + logs');

            const productsRes = await fetch('/api/product');
            if (!productsRes.ok) {
                throw new Error(`Failed to fetch products: ${productsRes.status}`);
            }
            const prodJson = await productsRes.json();
            const prodItems = Array.isArray(prodJson.data) ? prodJson.data : (Array.isArray(prodJson) ? prodJson : []);

            let categoriesMap = {};
            try {
                const catRes = await fetch('/api/category');
                if (catRes.ok) {
                    const cats = await catRes.json();
                    const cList = Array.isArray(cats.data) ? cats.data : (Array.isArray(cats) ? cats : []);
                    cList.forEach(c => categoriesMap[c.id] = c.name);
                }
            } catch (e) {
                console.warn('Failed to load categories:', e);
            }

            let suppliersMap = {};
            try {
                const supRes = await fetch('/api/supplier');
                if (supRes.ok) {
                    const sups = await supRes.json();
                    const sList = Array.isArray(sups.data) ? sups.data : (Array.isArray(sups) ? sups : []);
                    sList.forEach(s => suppliersMap[s.id] = s.name);
                }
            } catch (e) {
                console.warn('Failed to load suppliers:', e);
            }

            const logs = await fetchStockLogsAll();
            const logMap = computeProductQuantitiesFromLogs(logs, null, null);

            productsData = prodItems.map(p => {
                const m = logMap[p.id] || { inputQty: 0, outputQty: 0 };
                const cost = p.cost ?? p.input_cost ?? 0;
                const price = p.price ?? p.sale_price ?? 0;
                return {
                    id: p.id,
                    name: p.name || '-',
                    category: categoriesMap[p.category_id] || p.category_id || '-',
                    supplier: suppliersMap[p.supplier_id] || p.supplier_id || '-',
                    inputQty: m.inputQty,
                    outputQty: m.outputQty,
                    calculatedStock: (m.inputQty - m.outputQty),
                    inputCost: cost,
                    salePrice: price,
                    profit: (price - cost) * (m.outputQty || 0),
                    reorderThreshold: p.reorder_threshold ?? p.reorderThreshold ?? 0
                };
            });
        }

    } catch (err) {
        console.error('Failed to load products data:', err);
        productsData = [];
    }
}

async function fetchStockLogsAll(page = 1, acc = []) {
    try {
        const res = await fetch(`/api/v1/stock?page=${page}`);
        if (!res.ok) {
            console.warn(`Failed to fetch stock logs page ${page}: ${res.status}`);
            return acc;
        }
        const json = await res.json();
        const items = Array.isArray(json.data) ? json.data : (Array.isArray(json) ? json : []);
        acc.push(...items);
        if (json.next_page_url) {
            return fetchStockLogsAll(page + 1, acc);
        }
        return acc;
    } catch (e) {
        console.error('Failed to load stock logs:', e);
        return acc;
    }
}

function computeProductQuantitiesFromLogs(logs, fromDate, toDate) {
    const map = {};
    logs.forEach(l => {
        const dt = new Date(l.created_at || l.createdAt || l.date || null);
        if (isNaN(dt)) return;
        if (fromDate && dt < fromDate) return;
        if (toDate && dt > toDate) return;
        const pid = l.product_id || l.productId;
        if (!pid) return;
        if (!map[pid]) map[pid] = { inputQty: 0, outputQty: 0 };
        const qty = Number(l.quantity || 0);
        const type = (l.type || '').toLowerCase();
        if (type.includes('in')) map[pid].inputQty += qty;
        else map[pid].outputQty += qty;
    });
    return map;
}

function renderSalesInventoryReport(title, data) {
    const reportContent = document.getElementById('reportContent');
    reportContent.innerHTML = '';

    if (!data || !data.length) {
        reportContent.innerHTML = '<p>No data available.</p>';
        document.getElementById('chartsRow').style.display = 'none';
        document.getElementById('lowStockAlerts').style.display = 'none';
        return;
    }

    let html = `<div class="mb-3"><strong>${title}</strong></div>`;
    html += `
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Category</th>
                    <th>Stock</th>
                    <th>Input Qty</th>
                    <th>Output Qty</th>
                    <th>Cost / Unit</th>
                    <th>Price / Unit</th>
                    <th>Profit / Unit</th>
                    <th>Total Profit</th>
                    <th>Supplier</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
    `;

    data.forEach(p => {
        const calculatedStock = p.calculatedStock != null ? p.calculatedStock : ((p.inputQty || 0) - (p.outputQty || 0));
        const costUnit = Number(p.inputCost ?? p.input_cost ?? 0) || 0;
        const priceUnit = Number(p.salePrice ?? p.sale_price ?? 0) || 0;
        const profitUnit = priceUnit - costUnit;
        const totalProfit = profitUnit * (p.outputQty || 0);
        const status = calculatedStock <= 0 ? 'Out of stock'
            : calculatedStock <= (p.reorderThreshold || p.reorder_threshold || 0)
                ? 'Low stock'
                : 'Good';

        html += `
        <tr>
            <td>${escapeHtml(p.name)}</td>
            <td>${escapeHtml(p.category)}</td>
            <td>${calculatedStock}</td>
            <td>${p.inputQty || 0}</td>
            <td>${p.outputQty || 0}</td>
            <td>${costUnit.toFixed(2)}</td>
            <td>${priceUnit.toFixed(2)}</td>
            <td>${profitUnit.toFixed(2)}</td>
            <td>${totalProfit.toFixed(2)}</td>
            <td>${escapeHtml(p.supplier)}</td>
            <td>${status}</td>
        </tr>`;
    });

    html += `
            </tbody>
        </table>
    </div>
    `;

    reportContent.innerHTML = html;
}

function downloadPdf() {
    const el = document.getElementById('reportPreview');
    if (!el) return alert('No report to export.');

    const opt = {
        margin: 0.5,
        filename: `product-report-${Date.now()}.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'in', format: 'a4', orientation: 'landscape' }
    };

    html2pdf().set(opt).from(el).save();
}

function exportCsv() {
    const rows = window.lastReportData || [];
    if (!rows.length) return alert('No data to export.');

    const headers = [
        'Product','Category','Stock','InputQty','OutputQty',
        'CostPerUnit','PricePerUnit','ProfitPerUnit',
        'TotalProfit','Supplier','Status'
    ];

    const lines = [headers.join(',')];

    rows.forEach(r => {
        const profitUnit = (Number(r.salePrice || r.price || 0) - Number(r.inputCost || r.cost || 0));
        const totalProfit = profitUnit * (r.outputQty || 0);
        const cols = [
            `"${(r.name || '').replace(/"/g, '""')}"`,
            `"${(r.category || '').replace(/"/g, '""')}"`,
            r.calculatedStock || 0,
            r.inputQty || 0,
            r.outputQty || 0,
            Number(r.inputCost || r.cost || 0).toFixed(2),
            Number(r.salePrice || r.price || 0).toFixed(2),
            profitUnit.toFixed(2),
            totalProfit.toFixed(2),
            `"${(r.supplier || '').replace(/"/g, '""')}"`,
            `"${(r.status || '').replace(/"/g, '""')}"`
        ];
        lines.push(cols.join(','));
    });

    const csv = lines.join('\n');
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);

    const a = document.createElement('a');
    a.href = url;
    a.download = `product-report-${Date.now()}.csv`;
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"']/g, m => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[m]));
}

function renderLowStockAlerts(data) {
    const lowTbl = document.querySelector('#lowStockTable tbody');
    const outTbl = document.querySelector('#outStockTable tbody');

    lowTbl.innerHTML = '';
    outTbl.innerHTML = '';

    const outList = data.filter(p => (p.calculatedStock || 0) <= 0);
    const lowList = data.filter(p => {
        const stock = p.calculatedStock || 0;
        const threshold = p.reorderThreshold || p.reorder_threshold || 0;
        return stock > 0 && stock <= threshold;
    });

    if (outList.length) {
        outList.forEach(p => {
            outTbl.insertAdjacentHTML('beforeend', `
            <tr class="table-danger">
                <td>${escapeHtml(p.name)}</td>
                <td>${escapeHtml(p.supplier)}</td>
                <td>${p.calculatedStock}</td>
                <td>${p.reorderThreshold || p.reorder_threshold || 0}</td>
            </tr>`);
        });
        document.getElementById('outStockAlerts').style.display = 'block';
    } else {
        document.getElementById('outStockAlerts').style.display = 'none';
    }

    if (lowList.length) {
        lowList.forEach(p => {
            lowTbl.insertAdjacentHTML('beforeend', `
            <tr class="table-warning">
                <td>${escapeHtml(p.name)}</td>
                <td>${escapeHtml(p.supplier)}</td>
                <td>${p.calculatedStock}</td>
                <td>${p.reorderThreshold || p.reorder_threshold || 0}</td>
            </tr>`);
        });
        document.getElementById('lowStockAlerts').style.display = 'block';
    } else {
        document.getElementById('lowStockAlerts').style.display = 'none';
    }
}

function renderCharts(data) {
    if (!data || data.length === 0) {
        document.getElementById('chartsRow').style.display = 'none';
        return;
    }

    const labels = data.map(d => d.name || 'Unknown');
    const stockValues = data.map(d => Number(d.calculatedStock || 0));
    const profitValues = data.map(d => Number(d.profit || 0));

    const catMap = {};
    data.forEach(d => { 
        const c = d.category || 'Uncategorized'; 
        catMap[c] = (catMap[c] || 0) + 1; 
    });

    const catLabels = Object.keys(catMap);
    const catValues = Object.values(catMap);

    const chartsRow = document.getElementById('chartsRow');
    if (!chartsRow) return;
    
    chartsRow.style.display = 'flex';

    ['chartStockDistribution','chartProductDistribution','chartProfitDistribution'].forEach(id => {
        if (charts[id]) { 
            charts[id].destroy(); 
            charts[id] = null; 
        }
    });

    try {
        const ctx1 = document.getElementById('chartStockDistribution');
        if (ctx1) {
            charts.chartStockDistribution = new Chart(ctx1.getContext('2d'), {
                type: 'pie',
                data: { 
                    labels: labels.slice(0,10), 
                    datasets: [{ 
                        data: stockValues.slice(0,10), 
                        backgroundColor: generateColors(10) 
                    }] 
                },
                options: { 
                    plugins: { 
                        legend: { position: 'bottom' },
                        title: { display: true, text: 'Stock Distribution' }
                    } 
                }
            });
        }

        const ctx2 = document.getElementById('chartProductDistribution');
        if (ctx2) {
            charts.chartProductDistribution = new Chart(ctx2.getContext('2d'), {
                type: 'pie',
                data: { 
                    labels: catLabels, 
                    datasets: [{ 
                        data: catValues, 
                        backgroundColor: generateColors(catLabels.length) 
                    }] 
                },
                options: { 
                    plugins: { 
                        legend: { position: 'bottom' },
                        title: { display: true, text: 'Product Distribution by Category' }
                    } 
                }
            });
        }

        const ctx3 = document.getElementById('chartProfitDistribution');
        if (ctx3) {
            charts.chartProfitDistribution = new Chart(ctx3.getContext('2d'), {
                type: 'pie',
                data: { 
                    labels: labels.slice(0,10), 
                    datasets: [{ 
                        data: profitValues.slice(0,10), 
                        backgroundColor: generateColors(10) 
                    }] 
                },
                options: { 
                    plugins: { 
                        legend: { position: 'bottom' },
                        title: { display: true, text: 'Profit Distribution' }
                    } 
                }
            });
        }
    } catch (e) {
        console.error('Error rendering charts:', e);
    }
}

function generateColors(n) {
    const base = ['#4e79a7','#f28e2b','#e15759','#76b7b2','#59a14f','#edc949','#af7aa1','#ff9da7','#9c755f','#bab0ac'];
    const out = [];
    for (let i=0; i<n; i++) out.push(base[i % base.length]);
    return out;
}
