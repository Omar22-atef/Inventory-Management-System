<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories</title>

    <!-- CSRF Token for AJAX requests -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap 5.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS -->
    @vite(['resources/css/manage-categories.css', 'resources/js/manage-categories.js'])
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
                <input type="text" id="category-search" class="form-control" placeholder="Search categories...">
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
            <h2>Manage Categories</h2>
            <section class="suppliers-section">
                <div class="suppliers-header d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">Category Inventory</h4>
                    <button class="btn btn-primary btn-add-category">Add New Category</button>
                </div>

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Category ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($categories as $category)
                        <tr>
                            <td>{{ $category->id }}</td>
                            <td>{{ $category->name }}</td>
                            <td>{{ $category->description }}</td>
                            <td>
                                <button class="btn btn-sm btn-warning btn-edit"
                                    data-id="{{ $category->id }}"
                                    data-name="{{ $category->name }}"
                                    data-description="{{ $category->description }}">
                                    Edit
                                </button>
                                <button class="btn btn-sm btn-danger delete-btn"
                                    data-id="{{ $category->id }}"
                                    data-name="{{ $category->name }}">
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

    <!-- Add Category Popup -->
    <section class="add-supplier-popup">
        <div class="popup-content">
            <span class="close-popup">&times;</span>
            <form id="addCategoryForm">
                @csrf
                <h2>Add New Category</h2>

                <label for="category-name">Category Name:</label>
                <input type="text" id="category-name" name="name" required>

                <label for="description">Description:</label>
                <textarea id="description" name="description" rows="3" required></textarea>

                <button type="submit" class="btn btn-primary mt-3">Add Category</button>
            </form>
        </div>
    </section>

    <!-- Edit Category Popup -->
    <section class="edit-supplier-popup">
        <div class="popup-content">
            <span class="close-popup">&times;</span>
            <form id="editCategoryForm">
                @csrf
                @method('PUT')
                <h2>Edit Category</h2>

                <input type="hidden" id="edit-category-id" name="id">

                <label for="edit-category-name">Category Name:</label>
                <input type="text" id="edit-category-name" name="name" required>

                <label for="edit-description">Description:</label>
                <textarea id="edit-description" name="description" rows="3" required></textarea>

                <button type="submit" class="btn btn-warning mt-3">Update Category</button>
            </form>
        </div>
    </section>

    <!-- Delete Category Popup -->
    <section class="delete-supplier-popup">
        <div class="popup-content">
            <span class="close-popup">&times;</span>

            <h2>Delete Category</h2>
            <p>
                Are you sure you want to delete
                <strong id="delete-category-name"></strong>?
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
