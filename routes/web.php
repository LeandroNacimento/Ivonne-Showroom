<?php

use App\Http\Controllers\ShowroomController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Showroom Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [ShowroomController::class, 'index'])->name('home');
Route::get('/catalogo', [ShowroomController::class, 'catalog'])->name('catalog');
Route::get('/producto/{slug}', \App\Livewire\ProductPage::class)->name('product.show');
Route::get('/carrito', [ShowroomController::class, 'cart'])->name('cart');
Route::middleware(['throttle:60,1'])->group(function () {
    Route::post('/carrito/agregar', [ShowroomController::class, 'addToCart'])->name('cart.add');
});
Route::get('/contacto', [ShowroomController::class, 'contact'])->name('contact');

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| The prefix is read from config('admin.path') — NOT from env() directly.
|
| Why: env() returns null once the application runs from config cache
| (php artisan config:cache). Using config() ensures the value is always
| available, whether caches are active or not.
|
| Route files are loaded dynamically from routes/admin/*.php so that each
| module (auth, products, clients, etc.) can be maintained independently.
|
*/

Route::prefix(config('admin.path'))
    ->name('admin.')
    ->group(function () {
        foreach (glob(base_path('routes/admin/*.php')) as $file) {
            require $file;
        }
    });
