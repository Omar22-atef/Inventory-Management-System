<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products</title>

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    @vite(['resources/css/MangeProducts.css', 'resources/js/ManageProducts.js'])

</head>

<body>

    <aside class="sidebar">
        <div class="sidebar-logo">
            <h3>Inventra</h3>
        </div>

        <ul class="sidebar-menu">
            <li class="menu-item">
                {{-- <a href="{{ route('dashboard') }}"> --}}
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
                <input type="text" id="product-search" class="form-control" placeholder="Search...">
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

        <!-- TABLE -->
        <section style="margin: 20px;">
            <h2>Manage Products</h2>

            <section class="products-section mt-4">
                <div class="products-header d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">Product Inventory</h4>
                    <button class="btn btn-primary btn-add-product">Add New Product</button>
                </div>

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Reorder</th>
                            <th>Supplier</th>
                            <th>Category</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($products as $product)
                        <tr>
                            <td>{{ $product->id }}</td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->quantity }}</td>
                            <td>{{ $product->price }}</td>
                            <td>{{ $product->reorder_threshold }}</td>
                            <td>{{ $product->supplier->name ?? 'N/A' }}</td>
                            <td>{{ $product->category->name ?? 'N/A' }}</td>
                            <td>
                                <button class="btn btn-sm btn-warning btn-edit"
                                    data-id="{{ $product->id }}"
                                    data-name="{{ $product->name }}"
                                    data-qty="{{ $product->quantity }}"
                                    data-price="{{ $product->price }}"
                                    data-supplier="{{ $product->supplier_id }}"
                                    data-category="{{ $product->category_id }}">
                                    Edit
                                </button>

                                <button class="btn btn-sm btn-danger delete-btn"
                                    data-id="{{ $product->id }}"
                                    data-name="{{ $product->name }}">
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

    <!-- Add Product Popup -->
    <section class="add-product-popup">
        <div class="popup-content">
            <span class="close-popup">&times;</span>
            <form id="addProductForm">
                @csrf <!-- Add CSRF token -->

                <h2>Add New Product</h2>

                <label>Product Name:</label>
                <input type="text" id="product-name" name="name" required>

                <label>Category:</label>
                <select id="category_id" name="category_id" required class="form-control">
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>

                <label>Supplier:</label>
                <select id="supplier_id" name="supplier_id" required class="form-control">
                    <option value="">Select Supplier</option>
                    @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>

                <label>Stock Quantity:</label>
                <input type="number" id="stock-quantity" name="quantity" required>

                <label>Reorder Threshold:</label>
                <input type="number" id="reorder-threshold" name="reorder_threshold" required>

                <label>Price:</label>
                <input type="number" step="0.01" id="price" name="price" required>

                <button type="submit" class="btn btn-primary mt-3">Add Product</button>
            </form>
        </div>
    </section>

<!-- Edit Product Popup -->
    <section class="edit-product-popup">
        <div class="popup-content">
            <span class="close-popup">&times;</span>
            <form id="editProductForm">
                @csrf
                @method('PUT')

                <h2>Edit Product</h2>

                <input type="hidden" id="edit-product-id" name="id">

                <label>Product Name:</label>
                <input type="text" id="edit-product-name" name="name" required>

                <label>Category:</label>
                <select id="edit-category-id" name="category_id" required class="form-control">
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>

                <label>Supplier:</label>
                <select id="edit-supplier-id" name="supplier_id" required class="form-control">
                    @foreach($suppliers as $supplier)
                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                    @endforeach
                </select>

                <label>Stock Quantity:</label>
                <input type="number" id="edit-stock-quantity" name="quantity" required>

                <label>Reorder Threshold:</label>
                <!-- NOTE: changed id to edit-reorder-threshold -->
                <input type="number" id="edit-reorder-threshold" name="reorder_threshold" required>

                <label>Price:</label>
                <input type="number" step="0.01" id="edit-price" name="price" required>

                <button type="submit" class="btn btn-warning mt-3">Update Product</button>
            </form>
        </div>
    </section>

    <!-- Delete Popup -->
    <section class="delete-product-popup">
        <div class="popup-content">
            <span class="close-popup">&times;</span>

            <h2>Delete Product</h2>
            <p>
                Are you sure you want to delete <strong id="delete-product-name"></strong>?
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
