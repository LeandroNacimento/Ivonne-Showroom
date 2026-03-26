<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Auth Routes
|--------------------------------------------------------------------------
|
| Root redirect + guest routes (login) + authenticated logout.
| This file is loaded within the admin prefix group defined in web.php.
|
*/

// Root admin redirect → dashboard
Route::get('/', fn () => redirect()->route('admin.dashboard'));

// Guest-only routes (accessible only when not authenticated)
Route::middleware('guest:web')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLoginForm'])
        ->name('login');

    Route::post('/login', [AdminAuthController::class, 'login'])
        ->name('login.submit')
        ->middleware('throttle:5,1');
});

// Authenticated-only: logout
Route::middleware(['auth:web', 'admin'])->group(function () {
    Route::post('/logout', [AdminAuthController::class, 'logout'])
        ->name('logout');
});
