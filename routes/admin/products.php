<?php

use App\Http\Controllers\Admin\ProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Product Routes
|--------------------------------------------------------------------------
|
| IMPORTANT: The /products/search route MUST be defined BEFORE the resource
| to prevent Laravel from treating "search" as a {product} model binding.
|
| Named routes:
|   admin.products.search
|   admin.products.index, .create, .store, .show, .edit, .update, .destroy
|
*/

Route::middleware(['auth:web', 'admin'])->group(function () {
    // Search must come before resource to avoid route parameter collision
    Route::get('/products/search', [ProductController::class, 'search'])
        ->name('products.search')
        ->middleware('throttle:30,1');

    Route::resource('products', ProductController::class);
});
