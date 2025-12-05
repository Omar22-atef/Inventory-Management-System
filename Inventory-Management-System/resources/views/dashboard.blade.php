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
                <h5 class="fw-bold mb-1">Welcome {{ auth()->user()->name ?? 'User' }}</h5>
                <small class="text-muted">{{ auth()->user()->email ?? '' }}</small>
            </div>

            <div class="search-box">
                <input type="text" class="form-control" placeholder="Search...">
                <i class="bi bi-search search-icon"></i>
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
    </script>
</body>
</html>
