{{-- resources/views/ui/salesstock.blade.php --}}
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <title>Sales & Stock Transfer | Inventra</title>

  <!-- Bootstrap + Icons + Font -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Your CSS files (move your css into public/css) -->
  <link rel="stylesheet" href="{{ asset('css/stockmang.css') }}">
  <link rel="stylesheet" href="{{ asset('css/sales-stocktransfer.css') }}">

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
      </div>
    </div>
  </main>

  <!-- Receive Stock Modal -->
  <div class="modal fade" id="receiveModal" tabindex="-1" aria-labelledby="receiveModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
      <div class="modal-content">
        <form id="receiveForm">
          <div class="modal-header">
            <h5 class="modal-title" id="receiveModalLabel">Receive stock</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div id="receiveAlert" class="alert d-none" role="alert"></div>

            <div class="mb-3">
              <label for="receiveProductId" class="form-label">Product ID</label>
              <input type="number" class="form-control" id="receiveProductId" name="product_id" required min="1">
            </div>

            <div class="mb-3">
              <label for="receiveQuantity" class="form-label">Quantity</label>
              <input type="number" class="form-control" id="receiveQuantity" name="quantity" required min="0.0001" step="any">
            </div>

            <div class="mb-3">
              <label for="receiveNotes" class="form-label">Notes (optional)</label>
              <input type="text" class="form-control" id="receiveNotes" name="notes" placeholder="Supplier delivery, ...">
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button id="receiveSubmitBtn" type="submit" class="btn btn-primary">Receive</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Scripts: bootstrap bundle + your page scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script src="{{ asset('js/stockman.js') }}"></script>
  <script src="{{ asset('js/sales-stocktransfer.js') }}"></script>

  <!-- Inline JS: modal handler + override openReceiveModal -->
  <script>
    const API_BASE = "{{ url('/api/v1') }}";

    // Create bootstrap modal instance
    const receiveModalEl = document.getElementById('receiveModal');
    const receiveModal = receiveModalEl ? new bootstrap.Modal(receiveModalEl) : null;

    // override the openReceiveModal to show bootstrap modal
    function openReceiveModal(prefillProductId = '') {
      // reset state
      const alertEl = document.getElementById('receiveAlert');
      alertEl.classList.add('d-none');
      alertEl.classList.remove('alert-success','alert-danger');
      document.getElementById('receiveForm').reset();

      if (prefillProductId) document.getElementById('receiveProductId').value = prefillProductId;

      if (receiveModal) receiveModal.show();
    }

    // helper to show alert inside modal
    function showReceiveAlert(text, type='success') {
      const el = document.getElementById('receiveAlert');
      el.innerText = text;
      el.classList.remove('d-none','alert-success','alert-danger');
      el.classList.add('alert-' + (type === 'success' ? 'success' : 'danger'));
    }

    // form submit
    document.getElementById('receiveForm').addEventListener('submit', async function(e){
      e.preventDefault();
      const btn = document.getElementById('receiveSubmitBtn');
      btn.disabled = true;
      btn.innerText = 'Sending...';

      const product_id = Number(document.getElementById('receiveProductId').value || 0);
      const quantity = Number(document.getElementById('receiveQuantity').value || 0);
      const notes = (document.getElementById('receiveNotes').value || '').trim();

      if (!product_id || quantity <= 0) {
        showReceiveAlert('Please enter a valid product id and quantity.', 'danger');
        btn.disabled = false;
        btn.innerText = 'Receive';
        return;
      }

      try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        const res = await fetch(API_BASE + '/stock/receive', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token
          },
          body: JSON.stringify({
            product_id: product_id,
            quantity: quantity,
            notes: notes || 'receive from UI'
          })
        });

        const json = await res.json().catch(()=>({}));

        if (!res.ok) {
          const msg = json?.message || (json?.errors ? JSON.stringify(json.errors) : 'Server error');
          showReceiveAlert('Error: ' + msg, 'danger');
        } else {
          showReceiveAlert('Stock received successfully.', 'success');
          // update UI
          if (typeof reloadAll === 'function') {
            await reloadAll();
          }
          setTimeout(()=>{ if (receiveModal) receiveModal.hide(); }, 700);
        }
      } catch(err) {
        showReceiveAlert('Network error: ' + (err.message || err), 'danger');
      } finally {
        btn.disabled = false;
        btn.innerText = 'Receive';
      }
    });

    // if you want other quick action functions to remain, they are defined in sales-stocktransfer.js
    // but we make sure reloadAll exists. If not, keep the old one below:
    if (typeof reloadAll !== 'function') {
      async function reloadAll(){
        try {
          if (typeof loadSuppliers === 'function') await loadSuppliers();
          if (typeof loadTransfers === 'function') await loadTransfers();
          if (typeof loadStats === 'function') await loadStats();
        } catch(e){ console.error(e); }
      }
    }
  </script>

</body>
</html>
