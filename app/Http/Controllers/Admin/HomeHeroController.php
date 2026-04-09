<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreHomeHeroSlideRequest;
use App\Http\Requests\Admin\UpdateHomeHeroContentRequest;
use App\Http\Requests\Admin\UpdateHomeHeroSlideRequest;
use App\Models\HomeHeroSlide;
use App\Services\HomeHeroService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HomeHeroController extends Controller
{
    public function __construct(
        private readonly HomeHeroService $homeHeroService
    ) {}

    public function edit(): View
    {
        $hero = $this->homeHeroService->singleton()->load('slides');

        return view('admin.home.hero.edit', [
            'hero' => $hero,
            'slides' => $hero->slides,
            'activeSlidesCount' => $hero->slides->where('is_active', true)->count(),
        ]);
    }

    public function updateContent(UpdateHomeHeroContentRequest $request): RedirectResponse
    {
        $this->homeHeroService->updateContent($request->validated());

        return redirect()
            ->route('admin.home.hero.edit')
            ->with('success', 'Texto principal de la portada actualizado con exito.');
    }

    public function storeSlide(StoreHomeHeroSlideRequest $request): RedirectResponse
    {
        $this->homeHeroService->createSlide(
            $this->homeHeroService->singleton(),
            $request->validated()
        );

        return redirect()
            ->route('admin.home.hero.edit')
            ->with('success', 'Imagen agregada a la portada con exito.');
    }

    public function updateSlide(UpdateHomeHeroSlideRequest $request, HomeHeroSlide $slide): RedirectResponse
    {
        $this->homeHeroService->updateSlide($slide, $request->validated());

        return redirect()
            ->route('admin.home.hero.edit')
            ->with('success', 'Imagen actualizada con exito.');
    }

    public function destroySlide(HomeHeroSlide $slide): RedirectResponse
    {
        $this->homeHeroService->deleteSlide($slide);

        return redirect()
            ->route('admin.home.hero.edit')
            ->with('success', 'Imagen eliminada de la portada con exito.');
    }
}
