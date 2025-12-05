<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Suppliers</title>

    <!-- CSRF Token for AJAX requests -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- CSS -->
    @vite(['resources/css/ManageSuppliers.css', 'resources/js/ManageSuppliers.js'])
</head>
<body>

    <aside class="sidebar">
        <div class="sidebar-logo">
            <h3>Inventra</h3>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-item">
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
            <li class="menu-item active">
                <a href="{{ route('suppliers.index') }}">
                    <i class="bi bi-people"></i>
                    <span>Manage Suppliers</span>
                </a>
            </li>
            <li class="menu-item">
                <a href="{{ route('categories.index') }}">
                    <i class="bi bi-people"></i>
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
            <li class="menu-item">
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
                <input type="text" id="supplier-search" class="form-control" placeholder="Search...">
                <i class="bi bi-search search-icon"></i>
            </div>

            <div class="notification">
                <i class="bi bi-bell"></i>
            </div>
        </nav>

        <!-- Toast Notification -->
        <div id="successToast" class="toast-container position-fixed top-0 end-0 p-3">
            <div class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body"></div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>

        <section style="margin: 20px;">
            <h2>Manage Suppliers</h2>
            <section class="suppliers-section">
                <div class="suppliers-header d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">Suppliers Inventory</h4>
                    <button class="btn btn-primary btn-add-supplier">Add New Supplier</button>
                </div>

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Supplier ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($suppliers as $supplier)
                        <tr>
                            <td>{{ $supplier->id }}</td>
                            <td>{{ $supplier->name }}</td>
                            <td>{{ $supplier->email }}</td>
                            <td>{{ $supplier->phone }}</td>
                            <td>{{ $supplier->address }}</td>
                            <td>
                                <button class="btn btn-sm btn-warning btn-edit"
                                    data-id="{{ $supplier->id }}"
                                    data-name="{{ $supplier->name }}"
                                    data-email="{{ $supplier->email }}"
                                    data-phone="{{ $supplier->phone }}"
                                    data-address="{{ $supplier->address }}">
                                    Edit
                                </button>
                                <button class="btn btn-sm btn-danger delete-btn"
                                    data-id="{{ $supplier->id }}"
                                    data-name="{{ $supplier->name }}">
                                    Delete
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        </section>
    </main>

    <!-- Add Supplier Popup -->
    <section class="add-supplier-popup">
        <div class="popup-content">
            <span class="close-popup">&times;</span>
            <form id="addSupplierForm">
                @csrf
                <h2>Add New Supplier</h2>

                <label for="supplier-name">Supplier Name:</label>
                <input type="text" id="supplier-name" name="name" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>

                <label for="phone">Phone:</label>
                <input type="text" id="phone" name="phone" required>

                <label for="address">Address:</label>
                <input type="text" id="address" name="address" required>

                <button type="submit" class="btn btn-primary mt-3">Add Supplier</button>
            </form>
        </div>
    </section>

    <!-- Edit Supplier Popup -->
    <section class="edit-supplier-popup">
        <div class="popup-content">
            <span class="close-popup">&times;</span>
            <form id="editSupplierForm">
                @csrf
                @method('PUT')
                <h2>Edit Supplier</h2>

                <input type="hidden" id="edit-supplier-id" name="id">

                <label for="edit-supplier-name">Supplier Name:</label>
                <input type="text" id="edit-supplier-name" name="name" required>

                <label for="edit-email">Email:</label>
                <input type="email" id="edit-email" name="email" required>

                <label for="edit-phone">Phone:</label>
                <input type="text" id="edit-phone" name="phone" required>

                <label for="edit-address">Address:</label>
                <input type="text" id="edit-address" name="address" required>

                <button type="submit" class="btn btn-warning mt-3">Update Supplier</button>
            </form>
        </div>
    </section>

    <!-- Delete Supplier Popup -->
    <section class="delete-supplier-popup">
        <div class="popup-content">
            <span class="close-popup">&times;</span>

            <h2>Delete Supplier</h2>
            <p>
                Are you sure you want to delete
                <strong id="delete-supplier-name"></strong>?
            </p>

            <div class="mt-4 d-flex justify-content-end gap-2">
                <button class="btn btn-secondary cancel-delete">Cancel</button>
                <button class="btn btn-danger confirm-delete" data-id="">Delete</button>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


</body>
</html>
