<?php

use App\Http\Controllers\ShowroomController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ShowroomController::class, 'index'])->name('home');
Route::get('/catalogo', [ShowroomController::class, 'catalog'])->name('catalog');
Route::get('/producto/{slug}', [ShowroomController::class, 'product'])->name('product.show');
Route::get('/carrito', [ShowroomController::class, 'cart'])->name('cart');
Route::post('/carrito/agregar', [ShowroomController::class, 'addToCart'])->name('cart.add');
Route::post('/carrito/remover', [ShowroomController::class, 'removeFromCart'])->name('cart.remove');
Route::post('/carrito/actualizar', [ShowroomController::class, 'updateCart'])->name('cart.update');
Route::get('/contacto', [ShowroomController::class, 'contact'])->name('contact');
Route::get('/sobre-ivonne', [ShowroomController::class, 'about'])->name('about');

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ProductController;

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
        
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('dashboard');

        // Categories CRUD
        Route::resource('categories', CategoryController::class);

        // Products CRUD
        Route::resource('products', ProductController::class);
    });
});
