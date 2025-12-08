<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplierOrderController;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Dashboard & Stats
|--------------------------------------------------------------------------
*/
Route::get('/login', function() { return view('login'); })->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');

Route::get('/register', function() { return view('register'); })->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])
    ->middleware('guest')
    ->name('password.request');

// Send reset link
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware('guest')
    ->name('password.email');

// Reset password form
Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])
    ->middleware('guest')
    ->name('password.reset');

// Save new password
Route::post('/reset-password', [NewPasswordController::class, 'store'])
    ->middleware('guest')
    ->name('password.update');
/////////////////////////////////////////////////////

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->name('dashboard');

Route::get('/api/dashboard-stats', [DashboardController::class, 'getStats'])
    ->name('dashboard.stats');

/*
|--------------------------------------------------------------------------
| Notifications
|--------------------------------------------------------------------------
*/

// List notifications in a page (if you use it)
Route::get('/notifications', [NotificationController::class, 'index'])
    ->name('notifications.index');

// Mark single notification as read (used by "Ignore" button in overlay)
Route::post('/notifications/{notification}/read', function (DatabaseNotification $notification) {
    $notification->markAsRead();

    return response()->json(['success' => true]);
})->name('notifications.read');

// Mark all as read (if you use it anywhere)
Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])
    ->name('notifications.readAll');

/*
|--------------------------------------------------------------------------
| Supplier: Send Order Email
|--------------------------------------------------------------------------
*/

Route::post('/supplier/send-order/{product}', [SupplierOrderController::class, 'sendOrderEmail'])
    ->name('supplier.sendOrder');

/*
|--------------------------------------------------------------------------
| Products CRUD
|--------------------------------------------------------------------------
*/

Route::get('/products', [ProductController::class, 'index'])
    ->name('products.index');

Route::post('/products', [ProductController::class, 'store'])
    ->name('products.store');

Route::get('/products/{id}', [ProductController::class, 'show'])
    ->name('products.show');

Route::put('/products/{id}', [ProductController::class, 'update'])
    ->name('products.update');

Route::delete('/products/{id}', [ProductController::class, 'destroy'])
    ->name('products.destroy');

/*
|--------------------------------------------------------------------------
| Suppliers CRUD
|--------------------------------------------------------------------------
*/

Route::get('/suppliers', [SupplierController::class, 'index'])
    ->name('suppliers.index');

Route::post('/suppliers', [SupplierController::class, 'store'])
    ->name('suppliers.store');

Route::put('/suppliers/{id}', [SupplierController::class, 'update'])
    ->name('suppliers.update');

Route::delete('/suppliers/{id}', [SupplierController::class, 'destroy'])
    ->name('suppliers.destroy');

/*
|--------------------------------------------------------------------------
| Categories CRUD
|--------------------------------------------------------------------------
*/

Route::get('/categories', [CategoryController::class, 'index'])
    ->name('categories.index');

Route::post('/categories', [CategoryController::class, 'store'])
    ->name('categories.store');

Route::put('/categories/{id}', [CategoryController::class, 'update'])
    ->name('categories.update');

Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])
    ->name('categories.destroy');

/*
|--------------------------------------------------------------------------
| Extra Views
|--------------------------------------------------------------------------
*/

Route::get('/salesstock', function () {
    return view('salesstock');
});
