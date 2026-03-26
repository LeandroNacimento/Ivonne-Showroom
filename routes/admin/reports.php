<?php

use App\Http\Controllers\Admin\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Report Routes
|--------------------------------------------------------------------------
|
| Named routes:
|   admin.reports.index
|   admin.reports.export
|
*/

Route::middleware(['auth:web', 'admin'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])
        ->name('reports.index');

    Route::get('/reports/export', [ReportController::class, 'export'])
        ->name('reports.export');
});
