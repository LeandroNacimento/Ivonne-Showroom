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
    ) {
    }

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
            ->with('success', 'Contenido del hero actualizado con éxito.');
    }

    public function storeSlide(StoreHomeHeroSlideRequest $request): RedirectResponse
    {
        $this->homeHeroService->createSlide(
            $this->homeHeroService->singleton(),
            $request->validated()
        );

        return redirect()
            ->route('admin.home.hero.edit')
            ->with('success', 'Slide creada con éxito.');
    }

    public function updateSlide(UpdateHomeHeroSlideRequest $request, HomeHeroSlide $slide): RedirectResponse
    {
        $this->homeHeroService->updateSlide($slide, $request->validated());

        return redirect()
            ->route('admin.home.hero.edit')
            ->with('success', 'Slide actualizada con éxito.');
    }

    public function destroySlide(HomeHeroSlide $slide): RedirectResponse
    {
        $this->homeHeroService->deleteSlide($slide);

        return redirect()
            ->route('admin.home.hero.edit')
            ->with('success', 'Slide eliminada con éxito.');
    }
}
