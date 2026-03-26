<?php

use App\Http\Controllers\Admin\SettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Settings Routes
|--------------------------------------------------------------------------
|
| Named routes:
|   admin.settings.edit
|   admin.settings.update
|
*/

Route::middleware(['auth:web', 'admin'])->group(function () {
    Route::get('/settings', [SettingController::class, 'edit'])
        ->name('settings.edit');

    Route::put('/settings', [SettingController::class, 'update'])
        ->name('settings.update');
});
