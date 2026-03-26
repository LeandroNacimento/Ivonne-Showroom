<?php

use App\Http\Controllers\Admin\ClientController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Client Routes
|--------------------------------------------------------------------------
|
| IMPORTANT: The /clients/search route MUST be defined BEFORE the resource
| to prevent Laravel from treating "search" as a {client} model binding.
|
| Named routes:
|   admin.clients.search
|   admin.clients.index, .create, .store, .show, .edit, .update, .destroy
|
*/

Route::middleware(['auth:web', 'admin'])->group(function () {
    // Search must come before resource to avoid route parameter collision
    Route::get('/clients/search', [ClientController::class, 'search'])
        ->name('clients.search');

    Route::resource('clients', ClientController::class);
});
