<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <!-- CSRF Token for AJAX requests -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Dashboard</title>

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Custom Style -->
    @vite(['resources/css/dashboard.css'])

</head>

<body>

    <aside class="sidebar">
        <div class="sidebar-logo">
            <h3>Inventra</h3>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-item active">
                <a href="{{ route('dashboard') }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="{{ route('products.index') }}">
                    <i class="bi bi-box-seam"></i>
                    <span>Manage Products</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="{{ route('suppliers.index') }}">
                    <i class="bi bi-people"></i>
                    <span>Manage Suppliers</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="{{ route('categories.index') }}">
                    <i class="bi bi-tags"></i>
                    <span>Manage Categories</span>
                </a>
            </li>
            <li class="menu-item">
                {{-- <a href="{{ route('reports.products') }}"> --}}
                    <i class="bi bi-bar-chart"></i>
                    <span>Reporting</span>
                </a>
            </li>
            <li class="menu-item">
                {{-- <a href="{{ route('stock.index') }}"> --}}
                    <i class="bi bi-bar-chart"></i>
                    <span>Stock Management</span>
                </a>
            </li>
            <li class="menu-item">
                {{-- <a href="{{ route('settings') }}"> --}}
                    <i class="bi bi-gear"></i>
                    <span>Settings</span>
                </a>
            </li>
            <li class="menu-item logout">
                {{-- <a href="{{ route('logout') }}"> --}}
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>

    </aside>

    <main class="main-content">

       <nav class="dashboard-header d-flex align-items-center justify-content-between">
    <div>
      <h5 class="fw-bold mb-1">Welcome {{ $user->name ?? 'Admin' }}</h5>
      <small class="text-muted">{{ $user->email ?? 'admin@example.com' }}</small>
    </div>

    <div class="d-flex align-items-center gap-3">
        <div class="search-box">
            <input type="text" class="form-control" placeholder="Search...">
            <i class="bi bi-search search-icon"></i>
        </div>

        <!-- Notification icon -->
        <div class="notification" id="notificationTrigger">
            <i class="bi bi-bell"></i>
        </div>
    </div>
</nav>


        <section class="overview mt-4">
            <h5 class="fw-bold mb-3">Overview</h5>

            <div class="row g-4">
                <div class="col-md-3">
                    <div class="overview-card">
                        <i class="bi bi-box-seam card-icon blue"></i>
                        <h3>{{ $totalProducts }}</h3>
                        <p>Total Products</p>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="overview-card">
                        <i class="bi bi-cart-check card-icon green"></i>
                        <h3>{{ $totalOrders }}</h3>
                        <p>Orders</p>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="overview-card">
                        <i class="bi bi-layers card-icon purple"></i>
                        <h3>{{ $totalStock }}</h3>
                        <p>Total Stock</p>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="overview-card">
                        <i class="bi bi-exclamation-triangle card-icon red"></i>
                        <h3>{{ $outOfStock }}</h3>
                        <p>Out Of Stock</p>
                    </div>
                </div>
            </div>

        </section>

        <!-- Additional Stats Section -->
        <section class="stats mt-5">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="stats-card">
                        <h6 class="fw-bold mb-3">Recent Products</h6>
                        <div class="list-group">
                            @foreach($recentProducts as $product)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $product->name }}</strong>
                                    <small class="text-muted d-block">{{ $product->category->name ?? 'Uncategorized' }}</small>
                                </div>
                                <span class="badge bg-primary">{{ $product->quantity }} in stock</span>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="stats-card">
                        <h6 class="fw-bold mb-3">Low Stock Alert</h6>
                        <div class="list-group">
                            @foreach($lowStockProducts as $product)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $product->name }}</strong>
                                    <small class="text-muted d-block">Reorder threshold: {{ $product->reorder_threshold }}</small>
                                </div>
                                <span class="badge bg-danger">{{ $product->quantity }} left</span>
                            </div>
                            @endforeach
                            @if($lowStockProducts->isEmpty())
                            <div class="list-group-item text-center text-muted">
                                All products are well-stocked
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

               <!-- Low-Stock Notifications Overlay -->
    <div id="lowStockOverlay" class="ls-overlay">
        <div class="ls-overlay-backdrop"></div>

        <div class="ls-panel">
            <button class="ls-close-btn" aria-label="Close">
                ✕
            </button>

            <h2>Low-Stock Notifications</h2>
            <p class="ls-intro">
                These products have reached their low-stock threshold. You can ignore or email the supplier to reorder.
            </p>

            @if($lowStockAlerts->isEmpty())
                <p class="text-muted mb-0">No low-stock notifications right now 🎉</p>
            @else
                <div class="ls-list">
                    @foreach($lowStockAlerts as $notification)
                        @php
                            $data = $notification->data;
                        @endphp

                        <div class="ls-item" data-notification-id="{{ $notification->id }}" data-product-id="{{ $data['product_id'] ?? '' }}">
                            <div class="ls-item-main">
                                <h5 class="mb-1">{{ $data['product_name'] ?? 'Product' }}</h5>
                                <p class="mb-1 small text-muted">
                                    {{ $data['message'] ?? '' }}
                                </p>
                                <p class="mb-0 small">
                                    Quantity: <strong>{{ $data['level'] ?? $data['quantity'] ?? 'N/A' }}</strong> &nbsp;·&nbsp;
                                    Reorder threshold: <strong>{{ $data['reorder_threshold'] ?? 'N/A' }}</strong>
                                </p>
                            </div>

                            <div class="ls-item-actions">
                                <button type="button"
                                        class="btn btn-sm btn-outline-secondary btn-ignore"
                                        data-notification-id="{{ $notification->id }}">
                                    Ignore
                                </button>

                                <button type="button"
                                        class="btn btn-sm btn-primary btn-send-email"
                                        data-product-id="{{ $data['product_id'] ?? '' }}">
                                    Send email to supplier
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Dashboard JavaScript -->
    <script>
        // Auto-refresh dashboard data every 30 seconds
        function refreshDashboardData() {
            fetch('/api/dashboard-stats')
                .then(response => response.json())
                .then(data => {
                    // Update the stats
                    document.querySelector('.overview-card:nth-child(1) h3').textContent = data.totalProducts;
                    document.querySelector('.overview-card:nth-child(2) h3').textContent = data.totalOrders;
                    document.querySelector('.overview-card:nth-child(3) h3').textContent = data.totalStock;
                    document.querySelector('.overview-card:nth-child(4) h3').textContent = data.outOfStock;


                });
        }

        // Refresh every 30 seconds
        setInterval(refreshDashboardData, 30000);
        
  
  
    // Low-Stock overlay open/close + actions
    document.addEventListener('DOMContentLoaded', function () {
        const notifTrigger = document.getElementById('notificationTrigger');
        const overlay      = document.getElementById('lowStockOverlay');
        const closeBtn     = overlay ? overlay.querySelector('.ls-close-btn') : null;
        const backdrop     = overlay ? overlay.querySelector('.ls-overlay-backdrop') : null;

        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        if (notifTrigger && overlay) {
            notifTrigger.addEventListener('click', function () {
                overlay.classList.add('show');
            });
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', function () {
                overlay.classList.remove('show');
            });
        }

        if (backdrop) {
            backdrop.addEventListener('click', function () {
                overlay.classList.remove('show');
            });
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && overlay) {
                overlay.classList.remove('show');
            }
        });

        // Delegated click handling for buttons
        if (overlay) {
            overlay.addEventListener('click', async function (e) {
                const ignoreBtn = e.target.closest('.btn-ignore');
                const sendBtn   = e.target.closest('.btn-send-email');

                // Ignore button → mark notification as read
                if (ignoreBtn) {
                    const notifId = ignoreBtn.dataset.notificationId;

                    try {
                        const res = await fetch(`{{ route('notifications.read', ':id') }}`.replace(':id', notifId), {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            }
                        });

                        if (res.ok) {
                            const item = ignoreBtn.closest('.ls-item');
                            if (item) item.remove();
                        }
                    } catch (err) {
                        console.error(err);
                        alert('Failed to ignore notification.');
                    }
                }

                // Send email button → call supplier.sendOrder route
                if (sendBtn) {
                    const productId = sendBtn.dataset.productId;

                    sendBtn.disabled = true;

                    try {
                        const res = await fetch(`{{ route('supplier.sendOrder', ':id') }}`.replace(':id', productId), {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            }
                        });

                        const data = await res.json();
                        alert(data.message || (res.ok ? 'Email sent.' : 'Failed to send email.'));
                    } catch (err) {
                        console.error(err);
                        alert('Error sending email.');
                    } finally {
                        sendBtn.disabled = false;
                    }
                }
            });
        }
    });


    </script>
</body>
</html>
