<?php

use App\Http\Controllers\ShowroomController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ShowroomController::class, 'index'])->name('home');
Route::get('/catalogo', [ShowroomController::class, 'catalog'])->name('catalog');
Route::get('/producto/{slug}', \App\Livewire\ProductPage::class)->name('product.show');
Route::get('/carrito', [ShowroomController::class, 'cart'])->name('cart');
Route::middleware(['throttle:60,1'])->group(function () {
    Route::post('/carrito/agregar', [ShowroomController::class, 'addToCart'])->name('cart.add');
});
Route::get('/contacto', [ShowroomController::class, 'contact'])->name('contact');

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Guest Admin Routes
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    });

    // Authenticated Admin Routes
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Categories CRUD
        Route::resource('categories', CategoryController::class);

        // Products CRUD
        Route::get('/products/search', [ProductController::class, 'search'])
            ->name('products.search')
            ->middleware('throttle:30,1');
        Route::resource('products', ProductController::class);

        // Clients CRUD
        Route::resource('clients', ClientController::class);

        // Orders CRUD
        Route::resource('orders', OrderController::class);

        // Reports
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

        // Settings
        Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});
