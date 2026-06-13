<?php

use App\Http\Controllers\Admin\HomeHeroController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Home Routes
|--------------------------------------------------------------------------
|
| Named routes:
|   admin.home.hero.edit
|   admin.home.hero.slides.store
|   admin.home.hero.slides.update
|   admin.home.hero.slides.destroy
|   admin.home.hero.slides.reorder   (AJAX — JSON)
|   admin.home.hero.slides.toggle    (AJAX — JSON)
|
*/

Route::middleware(['auth:web', 'admin'])->group(function () {
    Route::get('/home/hero', [HomeHeroController::class, 'edit'])
        ->name('home.hero.edit');

    Route::post('/home/hero/slides', [HomeHeroController::class, 'storeSlide'])
        ->name('home.hero.slides.store');

    Route::put('/home/hero/slides/{slide}', [HomeHeroController::class, 'updateSlide'])
        ->name('home.hero.slides.update');

    Route::delete('/home/hero/slides/{slide}', [HomeHeroController::class, 'destroySlide'])
        ->name('home.hero.slides.destroy');

    Route::post('/home/hero/slides/reorder', [HomeHeroController::class, 'reorderSlides'])
        ->name('home.hero.slides.reorder');

    Route::patch('/home/hero/slides/{slide}/toggle', [HomeHeroController::class, 'toggleSlide'])
        ->name('home.hero.slides.toggle');
});
