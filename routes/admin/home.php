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
|   admin.home.hero.update
|   admin.home.hero.slides.store
|   admin.home.hero.slides.update
|   admin.home.hero.slides.destroy
|
*/

Route::middleware(['auth:web', 'admin'])->group(function () {
    Route::get('/home/hero', [HomeHeroController::class, 'edit'])
        ->name('home.hero.edit');

    Route::put('/home/hero', [HomeHeroController::class, 'updateContent'])
        ->name('home.hero.update');

    Route::post('/home/hero/slides', [HomeHeroController::class, 'storeSlide'])
        ->name('home.hero.slides.store');

    Route::put('/home/hero/slides/{slide}', [HomeHeroController::class, 'updateSlide'])
        ->name('home.hero.slides.update');

    Route::delete('/home/hero/slides/{slide}', [HomeHeroController::class, 'destroySlide'])
        ->name('home.hero.slides.destroy');
});
