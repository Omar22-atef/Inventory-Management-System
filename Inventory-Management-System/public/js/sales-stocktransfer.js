/* sales-stocktransfer.js
   Complete frontend logic for the Sales & Stock Transfer page
   - Sample data included (replace with API calls if needed)
   - Features: render sales by supplier, chart, transfers history,
               create transfers (adds sale), search/filter, CSV export
*/

(() => {
  // ---------- Sample data (replace with API results) ----------
  const suppliers = [
    { id: 's1', name: 'Alpha Supplies' },
    { id: 's2', name: 'Beta Traders' },
    { id: 's3', name: 'Gamma Wholesale' },
    { id: 's4', name: 'Delta Ltd' }
  ];

  // sales: supplierId, amount, items, date (YYYY-MM-DD)
  const sales = [
    { supplierId:'s1', amount:450, items:3, date:'2025-11-20' },
    { supplierId:'s2', amount:1200, items:10, date:'2025-11-23' },
    { supplierId:'s3', amount:760, items:6, date:'2025-11-23' },
    { supplierId:'s1', amount:300, items:2, date:'2025-11-25' },
    { supplierId:'s4', amount:190, items:1, date:'2025-11-24' },
    { supplierId:'s2', amount:400, items:4, date:'2025-10-11' }
  ];

  // transfers history
  const transfers = [
    { date:'2025-11-24', to:'Alpha Supplies', item:'Widget A', qty:20, status:'Pending' },
    { date:'2025-11-20', to:'Beta Traders', item:'Widget C', qty:50, status:'Completed' }
  ];

  // ---------- DOM refs ----------
  const supplierTableBody = document.getElementById('supplierTableBody');
  const transfersTableBody = document.getElementById('transfersTableBody');
  const supplierSelect = document.getElementById('supplierSelect');

  const cardTotalSales = document.getElementById('cardTotalSales');
  const cardSalesToday = document.getElementById('cardSalesToday');
  const cardSalesMonth = document.getElementById('cardSalesMonth');
  const cardPendingTransfers = document.getElementById('cardPendingTransfers');

  const topSupplierEl = document.getElementById('topSupplier');
  const lastTransferEl = document.getElementById('lastTransfer');

  const searchSupplierInput = document.getElementById('searchSupplier');
  const globalSearchInput = document.getElementById('globalSearch');
  const filterDateInput = document.getElementById('filterDate');
  const chartWrapper = document.getElementById('chartWrapper');
  const exportCsvBtn = document.getElementById('exportCsvBtn');

  // form refs
  const transferForm = document.getElementById('transferForm');
  const itemInput = document.getElementById('itemInput');
  const qtyInput = document.getElementById('qtyInput');
  const dateInput = document.getElementById('dateInput');
  const clearFormBtn = document.getElementById('clearFormBtn');
  const sourceSelect = document.getElementById('sourceSelect');

  // ---------- utilities ----------
  function formatCurrency(n){ return '$' + Number(n).toLocaleString(); }

  function aggregateSalesBySupplier(filteredSales){
    const map = new Map();
    suppliers.forEach(s => map.set(s.id, { supplierId: s.id, name: s.name, amount:0, items:0, lastSale: null }));
    for (const s of filteredSales){
      const rec = map.get(s.supplierId);
      if(!rec) continue;
      rec.amount += s.amount;
      rec.items += s.items;
      if(!rec.lastSale || new Date(s.date) > new Date(rec.lastSale)) rec.lastSale = s.date;
    }
    return Array.from(map.values()).sort((a,b) => b.amount - a.amount);
  }

  // ---------- rendering ----------
  function renderSupplierSelect(){
    supplierSelect.innerHTML = '<option value="" selected disabled>Select supplier</option>';
    suppliers.forEach(s => {
      const opt = document.createElement('option');
      opt.value = s.id; opt.textContent = s.name;
      supplierSelect.appendChild(opt);
    });
  }

  function renderSupplierTable(filterText = '', dateFilter = '', globalFilter = ''){
    // filter sales by supplier name filter and date
    const filtered = sales.filter(s => {
      const sup = suppliers.find(x => x.id === s.supplierId);
      if(!sup) return false;
      if(filterText && !sup.name.toLowerCase().includes(filterText.toLowerCase())) return false;
      if(globalFilter){
        const needle = globalFilter.toLowerCase();
        if(!sup.name.toLowerCase().includes(needle) && !s.date.includes(needle) && !(s.amount + '').includes(needle)) return false;
      }
      if(dateFilter && s.date !== dateFilter) return false;
      return true;
    });

    const agg = aggregateSalesBySupplier(filtered);
    supplierTableBody.innerHTML = '';
    if(agg.length === 0){
      const tr = document.createElement('tr');
      tr.innerHTML = '<td colspan="4" class="text-muted">No results</td>';
      supplierTableBody.appendChild(tr);
    } else {
      agg.forEach(row => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${row.name}</td>
          <td>${row.items}</td>
          <td>${formatCurrency(row.amount)}</td>
          <td class="text-muted">${row.lastSale || '—'}</td>
        `;
        supplierTableBody.appendChild(tr);
      });
    }

    renderBarChart(agg);
    updateQuickStats(agg);
    updateSummaryCards(filtered);
  }

  function renderBarChart(agg){
    // very small SVG bar chart (horizontal)
    const max = agg.length ? Math.max(...agg.map(a => a.amount)) : 0;
    const width = 220;
    const rowH = 28;
    const height = Math.max(120, agg.length * rowH);
    const svgNS = 'http://www.w3.org/2000/svg';
    const svg = document.createElementNS(svgNS, 'svg');
    svg.setAttribute('viewBox', `0 0 ${width} ${height}`);
    svg.setAttribute('preserveAspectRatio', 'none');

    agg.forEach((a, i) => {
      const y = i * rowH;
      const barW = max ? Math.round((a.amount / max) * (width - 90)) : 0;

      const label = document.createElementNS(svgNS,'text');
      label.setAttribute('x', 6);
      label.setAttribute('y', y + 14);
      label.setAttribute('font-size','10');
      label.setAttribute('fill','#0f172a');
      label.textContent = a.name.length > 14 ? a.name.slice(0,14) + '…' : a.name;
      svg.appendChild(label);

      const bg = document.createElementNS(svgNS,'rect');
      bg.setAttribute('x', 90);
      bg.setAttribute('y', y + 4);
      bg.setAttribute('width', width - 94);
      bg.setAttribute('height', 16);
      bg.setAttribute('fill', '#eef2ff');
      svg.appendChild(bg);

      const bar = document.createElementNS(svgNS,'rect');
      bar.setAttribute('x', 90);
      bar.setAttribute('y', y + 4);
      bar.setAttribute('width', barW);
      bar.setAttribute('height', 16);
      bar.setAttribute('fill', '#4361ee');
      svg.appendChild(bar);

      const val = document.createElementNS(svgNS,'text');
      val.setAttribute('x', width - 6);
      val.setAttribute('y', y + 14);
      val.setAttribute('font-size','10');
      val.setAttribute('fill','#0f172a');
      val.setAttribute('text-anchor','end');
      val.textContent = formatCurrency(a.amount);
      svg.appendChild(val);
    });

    chartWrapper.innerHTML = '';
    chartWrapper.appendChild(svg);
  }

  function renderTransfersTable(dateFilter = '', globalFilter = ''){
    const rows = transfers.filter(t => {
      if(dateFilter && t.date !== dateFilter) return false;
      if(globalFilter){
        const n = globalFilter.toLowerCase();
        if(!t.to.toLowerCase().includes(n) && !t.item.toLowerCase().includes(n) && !(t.qty + '').includes(n)) return false;
      }
      return true;
    });

    transfersTableBody.innerHTML = '';
    if(rows.length === 0){
      const tr = document.createElement('tr');
      tr.innerHTML = '<td colspan="5" class="text-muted">No transfers</td>';
      transfersTableBody.appendChild(tr);
    } else {
      rows.forEach(t => {
        const tr = document.createElement('tr');
        const badgeClass = t.status.toLowerCase() === 'pending' ? 'badge bg-warning-subtle text-warning' : 'badge bg-success-subtle text-success';
        tr.innerHTML = `
          <td>${t.date}</td>
          <td>${t.to}</td>
          <td>${t.item}</td>
          <td>${t.qty}</td>
          <td><span class="${badgeClass}">${t.status}</span></td>
        `;
        transfersTableBody.appendChild(tr);
      });
    }

    cardPendingTransfers.textContent = transfers.filter(t => t.status.toLowerCase() === 'pending').length;
    lastTransferEl.textContent = transfers.length ? `${transfers[transfers.length-1].date} — ${transfers[transfers.length-1].item}` : '—';
  }

  function updateQuickStats(agg){
    topSupplierEl.textContent = agg.length ? `${agg[0].name} (${formatCurrency(agg[0].amount)})` : '—';
  }

  function updateSummaryCards(filteredSales){
    const totalAll = sales.reduce((s,x) => s + x.amount, 0);
    const dateFilter = filterDateInput.value || '';
    const todayAmount = filteredSales.filter(s => s.date === dateFilter).reduce((s,x) => s + x.amount, 0);

    const now = new Date();
    const monthKey = now.toISOString().slice(0,7); // YYYY-MM
    const monthSum = sales.filter(s => s.date.startsWith(monthKey)).reduce((s,x) => s + x.amount, 0);

    cardTotalSales.textContent = formatCurrency(totalAll);
    cardSalesToday.textContent = formatCurrency(todayAmount || 0);
    cardSalesMonth.textContent = formatCurrency(monthSum);
  }

  // ---------- events ----------
  searchSupplierInput.addEventListener('input', () => {
    renderSupplierTable(searchSupplierInput.value.trim(), filterDateInput.value, globalSearchInput.value.trim());
  });

  globalSearchInput.addEventListener('input', () => {
    // global search triggers supplier table and transfers
    const q = globalSearchInput.value.trim();
    renderSupplierTable(searchSupplierInput.value.trim(), filterDateInput.value, q);
    renderTransfersTable(filterDateInput.value, q);
  });

  filterDateInput.addEventListener('change', () => {
    const d = filterDateInput.value;
    renderSupplierTable(searchSupplierInput.value.trim(), d, globalSearchInput.value.trim());
    renderTransfersTable(d, globalSearchInput.value.trim());
  });

  exportCsvBtn.addEventListener('click', () => {
    // Export aggregated supplier table (current view)
    const rows = Array.from(document.querySelectorAll('#supplierTableBody tr')).map(tr => {
      return Array.from(tr.children).map(td => td.innerText.trim());
    });
    const header = ['Supplier','Items Sold','Amount','Last Sale'];
    const csv = [header, ...rows].map(r => r.map(cell => `"${cell.replace(/"/g,'""')}"`).join(',')).join('\n');
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'sales_by_supplier.csv';
    document.body.appendChild(a); a.click(); a.remove();
    URL.revokeObjectURL(url);
  });

  clearFormBtn.addEventListener('click', () => {
    transferForm.reset();
    supplierSelect.selectedIndex = 0;
  });

  transferForm.addEventListener('submit', (e) => {
    e.preventDefault();
    // simple validation
    const supplierId = supplierSelect.value;
    if(!supplierId){ alert('Please choose a supplier'); return; }
    const supplier = suppliers.find(s => s.id === supplierId);
    const item = itemInput.value.trim();
    const qty = parseInt(qtyInput.value, 10);
    const date = dateInput.value;

    if(!item || !qty || !date){
      alert('Please fill all fields');
      return;
    }

    // Add transfer (Pending)
    transfers.push({ date, to: supplier.name, item, qty, status: 'Pending' });

    // Add sale record (assumption: sold to supplier; price example = qty * 10)
    const pricePerUnit = 10;
    sales.push({ supplierId: supplier.id, amount: qty * pricePerUnit, items: qty, date });

    // Rerender
    renderAll();

    // Reset
    transferForm.reset();
    supplierSelect.selectedIndex = 0;
    alert('Transfer created ✅');
  });

  // ---------- init ----------
  function renderAll(){
    renderSupplierSelect();
    renderSupplierTable('', '', '');
    renderTransfersTable();
  }

  // set default date input to today
  (function init(){
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth()+1).padStart(2,'0');
    const dd = String(today.getDate()).padStart(2,'0');
    dateInput.value = `${yyyy}-${mm}-${dd}`;
    filterDateInput.value = ''; // no filter default
    renderAll();
  })();

})();


/**
 * public/js/sales-stocktransfer.js
 * Make sure your blade includes: <script src="{{ asset('js/sales-stocktransfer.js') }}"></script>
 * Uses API_BASE from the blade: const API_BASE = "{{ url('/api/v1') }}";
 */

const API_BASE = (typeof API_BASE !== 'undefined') ? API_BASE : '/api/v1';

// Format money nicely
function fmtAmount(n) {
  if (n === null || n === undefined) return '$0.00';
  const num = Number(n) || 0;
  return '$' + num.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// Load dashboard totals and update cards
async function loadStats(date = null) {
  try {
    let url = API_BASE + '/dashboard/totals';
    if (date) url += '?date=' + encodeURIComponent(date);

    const res = await fetch(url, { headers: { 'Accept': 'application/json' }});
    if (!res.ok) {
      console.error('Failed to load stats:', res.status, res.statusText);
      // if JSON error included, try to log it
      try { const err = await res.json(); console.debug(err); } catch(e){}
      return;
    }

    const data = await res.json();

    document.getElementById('cardTotalSales')?.textContent = fmtAmount(data.total_sales || 0);
    document.getElementById('cardSalesToday')?.textContent = fmtAmount(data.sales_selected_date || 0);
    document.getElementById('cardSalesMonth')?.textContent = fmtAmount(data.sales_this_month || 0);
    document.getElementById('cardPendingTransfers')?.textContent = (data.pending_transfers !== undefined) ? String(data.pending_transfers) : '0';

    // If you later add a stock value card, you can set it here:
    // document.getElementById('cardStockValue')?.textContent = fmtAmount(data.stock_value || 0);

  } catch (err) {
    console.error('Error in loadStats():', err);
  }
}

// Try to load suppliers (optional endpoint). If your API doesn't have it, table will show a placeholder.
async function loadSuppliers() {
  try {
    const out = document.getElementById('supplierTableBody');
    if (!out) return;
    const url = API_BASE + '/suppliers';
    const res = await fetch(url, { headers: { 'Accept': 'application/json' }});
    if (!res.ok) {
      out.innerHTML = '<tr><td colspan="4" class="text-muted small">No suppliers API found</td></tr>';
      return;
    }
    const suppliers = await res.json();
    out.innerHTML = '';
    (suppliers || []).forEach(s => {
      const tr = document.createElement('tr');
      tr.innerHTML = `<td>${s.name || '—'}</td>
                      <td>${s.items_count ?? s.items ?? 0}</td>
                      <td>${fmtAmount(s.amount ?? s.total ?? 0)}</td>
                      <td>${s.last_delivery ?? '—'}</td>`;
      out.appendChild(tr);
    });
  } catch (err) {
    console.error('loadSuppliers error:', err);
  }
}

// Load transfers history (optional)
async function loadTransfers() {
  try {
    const out = document.getElementById('transfersTableBody');
    if (!out) return;
    const url = API_BASE + '/transfers';
    const res = await fetch(url, { headers: { 'Accept': 'application/json' }});
    if (!res.ok) {
      out.innerHTML = '<tr><td colspan="5" class="text-muted small">No transfers API found</td></tr>';
      return;
    }
    const transfers = await res.json();
    out.innerHTML = '';
    (transfers || []).forEach(t => {
      const tr = document.createElement('tr');
      tr.innerHTML = `<td>${t.date ?? t.created_at ?? '—'}</td>
                      <td>${t.to_location ?? t.to ?? '—'}</td>
                      <td>${t.item_name ?? t.item ?? '—'}</td>
                      <td>${t.qty ?? t.quantity ?? 0}</td>
                      <td>${t.status ?? '—'}</td>`;
      out.appendChild(tr);
    });
  } catch (err) {
    console.error('loadTransfers error:', err);
  }
}

// Central reload function called after actions (receive, sale)
async function reloadAll() {
  let dateVal = null;
  const dateInput = document.getElementById('filterDate');
  if (dateInput && dateInput.value) dateVal = dateInput.value;

  await Promise.allSettled([
    loadStats(dateVal),
    loadSuppliers(),
    loadTransfers()
  ]);
}

// initialize
document.addEventListener('DOMContentLoaded', function() {
  reloadAll();

  const filterDate = document.getElementById('filterDate');
  if (filterDate) filterDate.addEventListener('change', () => loadStats(filterDate.value));

  const searchSupplier = document.getElementById('searchSupplier');
  if (searchSupplier) {
    searchSupplier.addEventListener('input', function(){
      const q = (this.value || '').toLowerCase();
      const tbody = document.getElementById('supplierTableBody');
      if (!tbody) return;
      Array.from(tbody.querySelectorAll('tr')).forEach(tr => {
        const txt = tr.textContent.toLowerCase();
        tr.style.display = txt.includes(q) ? '' : 'none';
      });
    });
  }

  const csvBtn = document.getElementById('exportCsvBtn');
  if (csvBtn) {
    csvBtn.addEventListener('click', function(){
      const url = API_BASE + '/suppliers/export';
      window.open(url, '_blank');
    });
  }
});

// make reloadAll available globally (your blade calls it after receive)
window.reloadAll = reloadAll;
