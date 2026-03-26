<?php

use App\Http\Controllers\Admin\CategoryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Category Routes
|--------------------------------------------------------------------------
|
| Resource: admin.categories.index, .create, .store, .show, .edit, .update, .destroy
|
*/

Route::middleware(['auth:web', 'admin'])->group(function () {
    Route::resource('categories', CategoryController::class);
});
