<?php

use App\Http\Controllers\Admin\OrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Order Routes
|--------------------------------------------------------------------------
|
| Named routes:
|   admin.orders.index, .create, .store, .show, .edit, .update, .destroy
|
*/

Route::middleware(['auth:web', 'admin'])->group(function () {
    Route::resource('orders', OrderController::class);
});
