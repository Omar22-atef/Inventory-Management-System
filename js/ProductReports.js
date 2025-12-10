let productsData = [];
let stockLogsPage = 1;
let charts = {};

document.addEventListener("DOMContentLoaded", async () => {
    await loadProductsData();

    document.getElementById('generateReport')?.addEventListener('click', () => generateReport(false));
    document.getElementById('generateFullReport')?.addEventListener('click', () => generateReport(true));
});

async function generateReport(isFull = false) {
    const typeEl = document.getElementById('reportType');
    const type = (typeEl && typeEl.value) || '';
    const from = document.getElementById('reportDateFrom').value;
    const to = document.getElementById('reportDateTo').value;

    if (!isFull && !type) {
        return alert('Please choose a report type before generating.');
    }

    // For now we fetch aggregated products report from backend and also fetch stock logs for date filtering/details
    const products = productsData;

    // build filteredProducts depending on type and date range
    let filtered = [...products];

    // If user selected weekly/monthly and didn't provide dates, we derive dates
    let fromDate = from ? new Date(from) : null;
    let toDate = to ? new Date(to) : null;
    if (!fromDate && !toDate) {
        if (type === 'weekly') fromDate = new Date(Date.now() - 7 * 24 * 60 * 60 * 1000);
        if (type === 'monthly') fromDate = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000);
    }

    // If we have a date range we will fetch stock logs and compute input/output by date client-side
    let dateFilteredMap = null;
    if (fromDate || toDate) {
        const logs = await fetchStockLogsAll();
        dateFilteredMap = computeProductQuantitiesFromLogs(logs, fromDate, toDate);
        // merge into filtered products
        filtered = filtered.map(p => {
            const m = dateFilteredMap[p.id] || { inputQty: 0, outputQty: 0 };
            return { ...p, inputQty: m.inputQty, outputQty: m.outputQty, calculatedStock: (m.inputQty - m.outputQty) };
        });
    }

    // if the user asked for input/output specific type, filter by non-zero
    if (type === 'input') filtered = filtered.filter(p => (p.inputQty || 0) > 0);
    if (type === 'output' || type === 'sales') filtered = filtered.filter(p => (p.outputQty || 0) > 0);

    // show report preview
    document.getElementById('reportPreview')?.classList.remove('d-none');
    document.getElementById('reportMeta').innerText = `Generated on ${new Date().toLocaleString()}`;

    renderSalesInventoryReport((isFull ? 'Full' : (type || 'Custom')) + ' Report', filtered);
    renderCharts(filtered);
    renderLowStockAlerts(filtered);
}

async function loadProductsData() {
    try {
        const res = await fetch('/api/reports/sales-inventory');
        const data = await res.json();
        productsData = Array.isArray(data) ? data : (data.data || []);

        // If backend report endpoint returned no rows, build a fallback from products + stock logs
        if (!productsData || productsData.length === 0) {
            console.info('reports endpoint empty — falling back to /api/product + stock logs');
            const productsRes = await fetch('/api/product');
            const prodJson = await productsRes.json();
            const prodItems = Array.isArray(prodJson.data) ? prodJson.data : (Array.isArray(prodJson) ? prodJson : []);

            // fetch categories and suppliers for nicer labels (optional)
            let categoriesMap = {};
            try {
                const catRes = await fetch('/api/category');
                const cats = await catRes.json();
                const cList = Array.isArray(cats.data) ? cats.data : (Array.isArray(cats) ? cats : []);
                cList.forEach(c => categoriesMap[c.id] = c.name);
            } catch (e) {
                categoriesMap = {};
            }

            let suppliersMap = {};
            try {
                const supRes = await fetch('/api/supplier');
                const sups = await supRes.json();
                const sList = Array.isArray(sups.data) ? sups.data : (Array.isArray(sups) ? sups : []);
                sList.forEach(s => suppliersMap[s.id] = s.name);
            } catch (e) {
                suppliersMap = {};
            }

            const logs = await fetchStockLogsAll();
            const logMap = computeProductQuantitiesFromLogs(logs, null, null);

            productsData = prodItems.map(p => {
                const m = logMap[p.id] || { inputQty: 0, outputQty: 0 };
                return {
                    id: p.id,
                    name: p.name,
                    category: categoriesMap[p.category_id] || p.category_id || '-',
                    supplier: suppliersMap[p.supplier_id] || p.supplier_id || '-',
                    inputQty: m.inputQty,
                    outputQty: m.outputQty,
                    calculatedStock: (m.inputQty - m.outputQty) || p.quantity || 0,
                    inputCost: p.cost ?? p.input_cost ?? 0,
                    salePrice: p.price ?? p.sale_price ?? 0,
                    profit: ((p.price ?? 0) - (p.cost ?? 0)) * (m.outputQty || 0),
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
    // fetch stock movements/logs from backend endpoint /api/v1/stock (paginated)
    try {
        const res = await fetch(`/api/v1/stock?page=${page}`);
        const json = await res.json();
        // if response has data key (paginator), concatenate
        const items = Array.isArray(json.data) ? json.data : (Array.isArray(json) ? json : []);
        acc.push(...items);
        // try to detect last page from meta
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
        const pid = l.product_id || l.productId || l.productId;
        if (!pid) return;
        if (!map[pid]) map[pid] = { inputQty: 0, outputQty: 0 };
        const qty = Number(l.quantity || 0);
        const type = (l.type || '').toLowerCase();
        if (type.includes('in')) map[pid].inputQty += qty; else map[pid].outputQty += qty;
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
    html += `<div class="table-responsive"><table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Product</th>
                <th>Category</th>
                <th>Stock</th>
                <th>Input Qty</th>
                <th>Output Qty</th>
                <th>Sale Price</th>
                <th>Cost</th>
                <th>Profit</th>
                <th>Supplier</th>
            </tr>
        </thead>
        <tbody>`;

    data.forEach(p => {
        const calculatedStock = (p.calculatedStock != null) ? p.calculatedStock : ((p.inputQty || 0) - (p.outputQty || 0));
        const profit = (p.profit != null) ? p.profit : (((p.salePrice || p.sale_price || 0) - (p.inputCost || p.input_cost || 0)) * (p.outputQty || 0));
        const warnClass = (calculatedStock <= (p.reorderThreshold || p.reorder_threshold || 0)) ? 'table-warning' : '';
        html += `<tr class="${warnClass}">
            <td>${escapeHtml(p.name)}</td>
            <td>${escapeHtml(p.category)}</td>
            <td>${calculatedStock}</td>
            <td>${p.inputQty || 0}</td>
            <td>${p.outputQty || 0}</td>
            <td>${p.salePrice ?? p.sale_price ?? ''}</td>
            <td>${p.inputCost ?? p.input_cost ?? ''}</td>
            <td>${profit}</td>
            <td>${escapeHtml(p.supplier)}</td>
        </tr>`;
    });

    html += `</tbody></table></div>`;
    reportContent.innerHTML = html;
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>\"']/g, function (m) { return ({'&':'&amp;','<':'&lt;','>':'&gt;','\"':'&quot;',"'":'&#39;' })[m]; });
}

function renderLowStockAlerts(data) {
    const tbl = document.querySelector('#lowStockTable tbody');
    tbl.innerHTML = '';
    const low = data.filter(p => (p.calculatedStock != null ? p.calculatedStock : ((p.inputQty || 0) - (p.outputQty || 0))) <= (p.reorderThreshold || p.reorder_threshold || 0));
    if (!low.length) {
        document.getElementById('lowStockAlerts').style.display = 'none';
        return;
    }
    low.forEach(p => {
        const stock = p.calculatedStock != null ? p.calculatedStock : ((p.inputQty || 0) - (p.outputQty || 0));
        const row = `<tr>
            <td>${escapeHtml(p.name)}</td>
            <td>${escapeHtml(p.supplier)}</td>
            <td>${stock}</td>
            <td>${p.reorderThreshold ?? p.reorder_threshold ?? 0}</td>
        </tr>`;
        tbl.insertAdjacentHTML('beforeend', row);
    });
    document.getElementById('lowStockAlerts').style.display = 'block';
}

function renderCharts(data) {
    const labels = data.map(d => d.name);
    const stockValues = data.map(d => Number(d.calculatedStock != null ? d.calculatedStock : ((d.inputQty || 0) - (d.outputQty || 0))) || 0);
    const profitValues = data.map(d => Number(d.profit ?? ((d.salePrice || d.sale_price || 0) - (d.inputCost || d.input_cost || 0)) * (d.outputQty || 0)) || 0);

    // category distribution: count of products per category
    const catMap = {};
    data.forEach(d => { const c = d.category || 'Uncategorized'; catMap[c] = (catMap[c] || 0) + 1; });
    const catLabels = Object.keys(catMap);
    const catValues = Object.values(catMap);

    document.getElementById('chartsRow').style.display = 'flex';

    // destroy existing charts
    ['chartStockDistribution','chartProductDistribution','chartProfitDistribution'].forEach(id => {
        if (charts[id]) { charts[id].destroy(); charts[id] = null; }
    });

    const ctx1 = document.getElementById('chartStockDistribution').getContext('2d');
    charts.chartStockDistribution = new Chart(ctx1, {
        type: 'pie',
        data: { labels: labels.slice(0,10), datasets: [{ data: stockValues.slice(0,10), backgroundColor: generateColors(10) }] },
        options: { plugins: { legend: { position: 'bottom' } } }
    });

    const ctx2 = document.getElementById('chartProductDistribution').getContext('2d');
    charts.chartProductDistribution = new Chart(ctx2, {
        type: 'pie',
        data: { labels: catLabels, datasets: [{ data: catValues, backgroundColor: generateColors(catLabels.length) }] },
        options: { plugins: { legend: { position: 'bottom' } } }
    });

    const ctx3 = document.getElementById('chartProfitDistribution').getContext('2d');
    charts.chartProfitDistribution = new Chart(ctx3, {
        type: 'pie',
        data: { labels: labels.slice(0,10), datasets: [{ data: profitValues.slice(0,10), backgroundColor: generateColors(10) }] },
        options: { plugins: { legend: { position: 'bottom' } } }
    });
}

function generateColors(n) {
    const base = ['#4e79a7','#f28e2b','#e15759','#76b7b2','#59a14f','#edc949','#af7aa1','#ff9da7','#9c755f','#bab0ac'];
    const out = [];
    for (let i=0;i<n;i++) out.push(base[i % base.length]);
    return out;
}
