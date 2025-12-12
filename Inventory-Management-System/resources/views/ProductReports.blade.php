<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Reports - Inventory Management System</title>

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            display: flex;
            font-family: 'Inter', sans-serif;
            background: #f8faff;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: #1e293b;
            color: #fff;
            min-height: 100vh;
            padding: 20px 0;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
        }

        .sidebar-logo {
            text-align: center;
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 40px;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0 20px;
        }

        .sidebar-menu .menu-item {
            padding: 12px 14px;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            border-radius: 10px;
            color: #cbd5e1;
            transition: 0.3s;
        }

        .sidebar-menu .menu-item a {
            display: flex;
            align-items: center;
            gap: 12px;
            color: inherit;
            text-decoration: none;
            width: 100%;
        }

        .sidebar-menu .menu-item:hover,
        .sidebar-menu .menu-item.active {
            background: #4361ee;
            color: #fff;
        }

        /* Main Content */
        .main-content {
            margin-left: 250px;
            width: calc(100% - 250px);
            padding: 24px;
        }

        /* Header */
        .dashboard-header {
            background: #ffffff;
            padding: 14px 24px;
            border-radius: 14px;
            box-shadow: 0 4px 20px rgba(67, 97, 238, 0.1);
        }

        .search-box {
            position: relative;
            width: 260px;
        }

        .search-box input {
            border-radius: 40px;
            padding-right: 40px;
        }

        .search-box .search-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #475569;
        }

        /* Reports */
        .reports-page {
            margin-top: 24px;
        }

        .reports-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 18px;
            gap: 16px;
        }

        .btn-generate-all {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding-inline: 24px;
        }

        /* Filters */
        .reports-filters {
            display: flex;
            gap: 16px;
            margin-bottom: 18px;
            flex-wrap: wrap;
        }

        .filter-group {
            min-width: 180px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .filter-group label {
            font-size: 13px;
            color: #4b5563;
        }

        /* Product Cards */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 18px;
        }

        .product-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 14px;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.05);
            display: flex;
            flex-direction: column;
            gap: 10px;
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .product-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
        }

        .product-image {
            width: 100%;
            height: 120px;
            border-radius: 12px;
            overflow: hidden;
            background: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Chart Containers */
        .chart-container {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .chart-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        /* Report Tables */
        .report-table {
            font-size: 14px;
        }

        .report-table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .status-low {
            color: #dc3545;
            font-weight: 600;
        }

        .status-out {
            color: #6c757d;
            font-weight: 600;
        }

        .status-normal {
            color: #28a745;
            font-weight: 600;
        }

        /* Loading */
        .loading-spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <aside class="sidebar">
        <div class="sidebar-logo">
            <h3>Inventra</h3>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-item"><a href="{{ url('/dashboard') }}"><i
                        class="bi bi-speedometer2"></i><span>Dashboard</span></a></li>
            <li class="menu-item"><a href="#"><i class="bi bi-box-seam"></i><span>Manage Products</span></a></li>
            <li class="menu-item"><a href="#"><i class="bi bi-people"></i><span>Manage Suppliers</span></a></li>
            <li class="menu-item active"><a href="{{ url('/reports') }}"><i
                        class="bi bi-bar-chart"></i><span>Reporting</span></a></li>
            <li class="menu-item"><a href="{{ url('/stockmang') }}"><i class="bi bi-boxes"></i><span>Stock
                        Management</span></a></li>
            <li class="menu-item"><a href="#"><i class="bi bi-gear"></i><span>Settings</span></a></li>
            <li class="menu-item"><a href="{{ url('/') }}"><i class="bi bi-house"></i><span>Home</span></a></li>
        </ul>
    </aside>

    <main class="main-content">
        <nav class="dashboard-header d-flex align-items-center justify-content-between">
            <div>
                <h5 class="fw-bold mb-1">Welcome {{ auth()->user()->name ?? 'Username' }}</h5>
                <small class="text-muted">{{ auth()->user()->email ?? 'user@example.com' }}</small>
            </div>

            <div class="search-box">
                <input id="globalSearch" type="text" class="form-control" placeholder="Search...">
                <i class="bi bi-search search-icon"></i>
            </div>

            <div class="notification"><i class="bi bi-bell"></i></div>
        </nav>

        <section class="reports-page">
            <div class="reports-header">
                <div>
                    <h4 class="mb-0">Reports & Analytics</h4>
                    <small class="text-muted">Sales, inventory and supplier insights</small>
                </div>

                <button id="generateFullReport" class="btn btn-primary btn-lg btn-generate-all">Generate Full
                    Report</button>
            </div>


            <div class="report-generator p-4 mb-5 border rounded bg-white">
                <h5 class="fw-bold mb-3">Generate Custom Report</h5>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Select Report Type</label>
                        <select id="reportType" class="form-select">
                            <option value="">-- Choose Report --</option>
                            <option value="monthly">Monthly Report</option>
                            <option value="weekly">Weekly Report</option>
                            <option value="input">Product Input</option>
                            <option value="output">Product Output</option>
                            <option value="sales">Sales Report (Full Info)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">From</label>
                        <input type="date" id="reportDateFrom" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">To</label>
                        <input type="date" id="reportDateTo" class="form-control">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button class="btn btn-primary w-100" id="generateReport">Generate</button>
                    </div>
                </div>

                <div id="reportPreview" class="border rounded p-4 mt-4 d-none bg-white shadow-sm">
                    <div class="d-flex justify-content-between">
                        <h6 class="fw-bold">Report Preview</h6>
                        <small id="reportMeta" class="text-muted">No report yet</small>
                    </div>
                    <div id="reportContent" class="mt-3">
                        <p class="text-muted">Generated report will appear here.</p>
                    </div>

                    <div class="row mt-4 g-3" id="chartsRow" style="display:none">
                        <div class="col-md-4">
                            <div class="card p-3"><canvas id="chartStockDistribution" height="160"></canvas></div>
                        </div>
                        <div class="col-md-4">
                            <div class="card p-3"><canvas id="chartProductDistribution" height="160"></canvas>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card p-3"><canvas id="chartProfitDistribution" height="160"></canvas></div>
                        </div>
                    </div>

                    <div id="outStockAlerts" class="mt-4" style="display:none">
                        <h6 class="fw-bold text-danger">Out-of-Stock Products</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered" id="outStockTable">
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <div id="lowStockAlerts" class="mt-4" style="display:none">
                        <h6 class="fw-bold text-warning">Low Stock Products</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered" id="lowStockTable">
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button id="downloadPdf" class="btn btn-success">Download PDF</button>
                        <button id="exportExcel" class="btn btn-outline-secondary ms-2">Export Excel</button>
                        <button class="btn btn-outline-dark ms-2" onclick="window.print()">Print</button>
                    </div>
                </div>
            </div>

            <div class="products-grid" id="productsGrid"></div>

            <div class="no-results d-none" id="no-results">
                <i class="bi bi-emoji-frown"></i>
                <p>No products found. Try changing the search or filters.</p>
            </div>
        </section>
    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
    <script src="{{ asset('js/ProductReports.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
