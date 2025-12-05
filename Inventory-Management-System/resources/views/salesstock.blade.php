{{-- resources/views/ui/salesstock.blade.php --}}
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Sales & Stock Transfer | Inventra</title>

  <!-- Bootstrap + Icons + Font -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Your CSS files (move your css into public/css) -->
  <link rel="stylesheet" href="{{ asset('css/stockmang.css') }}">
  <link rel="stylesheet" href="{{ asset('css/sales-stocktransfer.css') }}">


  <script src="{{ asset('js/stockman.js') }}"></script>
<script src="{{ asset('js/sales-stocktransfer.js') }}"></script>


  <style>
    /* small fallback if your page-specific CSS wasn't loaded */
    body { font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial; }
  </style>
</head>
<body>

  {{-- SIDEBAR --}}
  <aside class="sidebar">
    <div class="sidebar-logo"><h3>Inventra</h3></div>
    <ul class="sidebar-menu">
      <li class="menu-item"><a href="#"><i class="bi bi-speedometer2"></i><span>Dashboard</span></a></li>
      <li class="menu-item"><a href="#"><i class="bi bi-box-seam"></i><span>Manage Products</span></a></li>
      <li class="menu-item"><a href="#"><i class="bi bi-people"></i><span>Manage Suppliers</span></a></li>
      <li class="menu-item"><a href="#"><i class="bi bi-bar-chart"></i><span>Reporting</span></a></li>
      <li class="menu-item active"><a href="#"><i class="bi bi-bar-chart"></i><span>Stock Management</span></a></li>
      <li class="menu-item"><a href="#"><i class="bi bi-gear"></i><span>Settings</span></a></li>
      <li class="menu-item logout"><a href="#"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a></li>
    </ul>
  </aside>

  {{-- MAIN CONTENT --}}
  <main class="main-content container-fluid">
    <div class="dashboard-header d-flex align-items-center justify-content-between mb-4">
      <div>
        <h5 class="fw-bold mb-0">Sales & Stock Transfer</h5>
        <div class="small text-muted">Sales per supplier & create stock transfers</div>
      </div>

      <div class="d-flex align-items-center gap-2">
        <div class="search-box">
          <input id="globalSearch" class="form-control" placeholder="Search suppliers, items..." />
          <i class="bi bi-search search-icon"></i>
        </div>
        <div class="ms-2">
          <input id="filterDate" type="date" class="form-control" />
        </div>
      </div>
    </div>

    <!-- Summary cards -->
    <div class="row g-4 mb-4">
      <div class="col-md-3">
        <div class="overview-card text-center">
          <div class="card-icon blue"><i class="bi bi-cart3 fs-3"></i></div>
          <h3 id="cardTotalSales">$0</h3>
          <p>Total Sales</p>
        </div>
      </div>

      <div class="col-md-3">
        <div class="overview-card text-center">
          <div class="card-icon green"><i class="bi bi-calendar-day fs-3"></i></div>
          <h3 id="cardSalesToday">$0</h3>
          <p>Sales (selected date)</p>
        </div>
      </div>

      <div class="col-md-3">
        <div class="overview-card text-center">
          <div class="card-icon purple"><i class="bi bi-calendar3-week fs-3"></i></div>
          <h3 id="cardSalesMonth">$0</h3>
          <p>Sales (this month)</p>
        </div>
      </div>

      <div class="col-md-3">
        <div class="overview-card text-center">
          <div class="card-icon red"><i class="bi bi-arrow-left-right fs-3"></i></div>
          <h3 id="cardPendingTransfers">0</h3>
          <p>Pending Transfers</p>
        </div>
      </div>
    </div>

    <!-- Main grid -->
    <div class="row">
      <div class="col-lg-7 mb-4">
        <div class="card shadow-sm border-0" style="border-radius:14px;">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
              <h5 class="fw-bold mb-0">Sales by Supplier</h5>
              <div class="d-flex gap-2">
                <input id="searchSupplier" type="text" class="form-control form-control-sm" placeholder="Filter supplier..." />
                <button class="btn btn-sm btn-primary" id="exportCsvBtn"><i class="bi bi-download"></i> CSV</button>
              </div>
            </div>

            <div class="d-flex gap-3">
              <div style="flex:1">
                <div class="table-responsive">
                  <table class="table table-hover mb-0">
                    <thead style="background:#f1f5f9;">
                      <tr><th>Supplier</th><th>Items</th><th>Amount</th><th>Last Delivery</th></tr>
                    </thead>
                    <tbody id="supplierTableBody"></tbody>
                  </table>
                </div>
              </div>

              <div style="width:240px">
                <div class="small text-muted mb-2">Sales chart</div>
                <div class="card p-2" id="chartWrapper" style="height:220px;overflow:auto"></div>
              </div>
            </div>
          </div>
        </div>

        <div class="card shadow-sm border-0 mt-4" style="border-radius:14px;">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="fw-bold mb-0">Transfers History</h5>
              <div class="small text-muted">Records of transfers</div>
            </div>

            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead style="background:#f1f5f9;">
                  <tr><th>Date</th><th>To</th><th>Item</th><th>Qty</th><th>Status</th></tr>
                </thead>
                <tbody id="transfersTableBody"></tbody>
              </table>
            </div>
          </div>
        </div>

      </div>

      <div class="col-lg-5">
        <!-- you can add more widgets here -->
        <div class="card shadow-sm border-0" style="border-radius:14px;">
          <div class="card-body">
            <h6>Quick Actions</h6>
            <div class="d-grid gap-2 mt-3">
              <a class="btn btn-primary" href="#" onclick="openReceiveModal()">Receive stock</a>
              <a class="btn btn-outline-primary" href="#" onclick="openTransferModal()">Create transfer</a>
              <a class="btn btn-outline-success" href="#" onclick="openSaleModal()">Record sale</a>
            </div>
          </div>
        </div>
        <!-- modals (we will use native prompt boxes in this version to stay simple) -->
      </div>
    </div>
  </main>

  <!-- Scripts -->
  <script>
    const API_BASE = "{{ url('/api/v1') }}";

    // helper: normalize paginated responses or arrays
    function normalizeList(respJson) {
      // many controllers return paginated: { data: [..], ... } or raw array
      if (!respJson) return [];
      if (Array.isArray(respJson)) return respJson;
      if (respJson.data && Array.isArray(respJson.data)) return respJson.data;
      // some return object with docs
      return respJson;
    }

    // load stock movements (supplier_in, transfer, out)
    async function fetchStock(type, page = 1) {
      let url = new URL(API_BASE + '/stock', window.location.origin);
      if (type) url.searchParams.append('type', type);
      url.searchParams.append('page', page);
      const res = await fetch(url.toString());
      if (!res.ok) throw new Error('Failed to fetch stock: ' + res.status);
      const json = await res.json();
      return normalizeList(json);
    }

    // Aggregate supplier deliveries from stock (type = supplier_in)
    async function loadSuppliers() {
      try {
        const list = await fetchStock('supplier_in');
        // aggregate by supplier_id
        const agg = {};
        list.forEach(row => {
          const sid = row.supplier_id ?? row.supplierId ?? ('supplier_'+(row.supplier_id||'unknown'));
          if (!agg[sid]) agg[sid] = { supplier_id: sid, items: 0, amount: 0, last: null };
          const qty = Number(row.quantity || 0);
          const amt = Number(row.total_price || row.amount || 0); // fallback
          agg[sid].items += qty;
          agg[sid].amount += amt;
          if (!agg[sid].last || new Date(row.created_at) > new Date(agg[sid].last)) agg[sid].last = row.created_at;
        });

        const tbody = document.getElementById('supplierTableBody');
        tbody.innerHTML = '';
        Object.values(agg).forEach(s => {
          const tr = document.createElement('tr');
          tr.innerHTML = `<td>Supplier ${s.supplier_id}</td><td>${s.items}</td><td>${s.amount.toFixed(2)}</td><td>${s.last? s.last : '-'}</td>`;
          tbody.appendChild(tr);
        });
      } catch (e) {
        console.error(e);
        document.getElementById('supplierTableBody').innerHTML = '<tr><td colspan="4">Failed to load suppliers</td></tr>';
      }
    }

    // load transfers (type = transfer)
    async function loadTransfers() {
      try {
        const list = await fetchStock('transfer');
        const tbody = document.getElementById('transfersTableBody');
        tbody.innerHTML = '';
        list.forEach(r => {
          const date = r.created_at ? new Date(r.created_at).toLocaleString() : '-';
          const toWh = r.to_warehouse_id ?? r.toWarehouseId ?? 'N/A';
          const item = 'Product '+(r.product_id ?? r.productId ?? '-');
          const qty = r.quantity ?? 0;
          const status = r.type ?? 'transfer';
          const tr = document.createElement('tr');
          tr.innerHTML = `<td>${date}</td><td>${toWh}</td><td>${item}</td><td>${qty}</td><td><span class="transfer-badge bg-light">${status}</span></td>`;
          tbody.appendChild(tr);
        });

        // pending transfers
        document.getElementById('cardPendingTransfers').innerText = list.length;
      } catch (e) {
        console.error(e);
        document.getElementById('transfersTableBody').innerHTML = '<tr><td colspan="5">Failed to load transfers</td></tr>';
      }
    }

    // load summary stats from stock 'out' (sales) and 'in' (receives)
    async function loadStats() {
      try {
        const outs = await fetchStock('out');
        const ins = await fetchStock('supplier_in');
        // total sales (sum out.quantity)
        const totalSalesQty = outs.reduce((s,r)=> s + Number(r.quantity || 0), 0);
        document.getElementById('cardTotalSales').innerText = '$' + (totalSalesQty).toFixed(2);

        // filter by selected date for "Sales Today"
        const dateInput = document.getElementById('filterDate').value;
        if (dateInput) {
          const dayouts = outs.filter(r => r.created_at && r.created_at.startsWith(dateInput));
          const daySum = dayouts.reduce((s,r)=> s + Number(r.quantity||0), 0);
          document.getElementById('cardSalesToday').innerText = '$' + daySum.toFixed(2);
        } else {
          document.getElementById('cardSalesToday').innerText = '$0';
        }

        // this month
        const now = new Date();
        const monthStr = now.getFullYear() + '-' + String(now.getMonth()+1).padStart(2,'0');
        const monthOuts = outs.filter(r => r.created_at && r.created_at.startsWith(monthStr));
        const monthSum = monthOuts.reduce((s,r)=> s + Number(r.quantity||0), 0);
        document.getElementById('cardSalesMonth').innerText = '$' + monthSum.toFixed(2);

      } catch (e) {
        console.error(e);
      }
    }

    // export supplier table to CSV
    function exportCsv() {
      const rows = Array.from(document.querySelectorAll('#supplierTableBody tr')).map(tr=>{
        return Array.from(tr.children).map(td=> td.innerText.trim());
      });
      if (!rows.length) { alert('No data'); return; }
      const header = ['Supplier','Items','Amount','Last Delivery'];
      const csv = [header.join(','), ...rows.map(r=>r.map(c=>`"${c.replace(/"/g,'""')}"`).join(','))].join('\n');
      const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'suppliers.csv';
      document.body.appendChild(a);
      a.click();
      a.remove();
    }

    // quick actions: in this simple version prompt user for values and call endpoints
    async function openReceiveModal(){
      const pid = prompt('Product ID to receive:');
      if (!pid) return;
      const qty = prompt('Quantity:','1');
      if (!qty) return;
      const payload = { product_id: Number(pid), quantity: Number(qty), notes: 'manual receive from UI' };
      const res = await fetch(API_BASE + '/stock/receive', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
      const json = await res.json();
      if (!res.ok) { alert('Error: ' + (json.message || JSON.stringify(json))); return; }
      alert('Received OK');
      await reloadAll();
    }

    async function openTransferModal(){
      const pid = prompt('Product ID to transfer:');
      if (!pid) return;
      const qty = prompt('Quantity:','1');
      if (!qty) return;
      const payload = { product_id: Number(pid), quantity: Number(qty), notes: 'transfer from UI' };
      const res = await fetch(API_BASE + '/stock/transfer', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
      const json = await res.json();
      if (!res.ok) { alert('Error: ' + (json.message || JSON.stringify(json))); return; }
      alert('Transfer created');
      await reloadAll();
    }

    async function openSaleModal(){
      const pid = prompt('Product ID to sell:');
      if (!pid) return;
      const qty = prompt('Quantity:','1');
      if (!qty) return;
      const payload = { items: [{ product_id: Number(pid), quantity: Number(qty) }] };
      const res = await fetch(API_BASE + '/sales', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) });
      const json = await res.json();
      if (!res.ok) { alert('Error: ' + (json.message || JSON.stringify(json))); return; }
      alert('Sale recorded (id: ' + (json.sale_id ?? '-') + ')');
      await reloadAll();
    }

    // reload all widgets
    async function reloadAll(){
      await Promise.all([ loadSuppliers(), loadTransfers(), loadStats() ]);
    }

    // wire up events
    document.getElementById('exportCsvBtn').addEventListener('click', exportCsv);
    document.getElementById('filterDate').addEventListener('change', loadStats);
    document.getElementById('searchSupplier').addEventListener('input', (e)=> {
      // simple client-side filter for supplier table
      const q = e.target.value.toLowerCase();
      document.querySelectorAll('#supplierTableBody tr').forEach(tr=>{
        tr.style.display = tr.innerText.toLowerCase().includes(q) ? '' : 'none';
      });
    });

    // init
    reloadAll();
  </script>

</body>
</html>
