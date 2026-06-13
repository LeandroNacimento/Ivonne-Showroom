<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReorderHomeSlidesRequest;
use App\Http\Requests\Admin\StoreHomeHeroSlideRequest;
use App\Http\Requests\Admin\UpdateHomeHeroSlideRequest;
use App\Models\HomeHeroSlide;
use App\Services\HomeHeroService;
use Illuminate\Http\JsonResponse;
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

    public function storeSlide(StoreHomeHeroSlideRequest $request): RedirectResponse
    {
        $this->homeHeroService->createSlide(
            $this->homeHeroService->singleton(),
            $request->validated()
        );

        return redirect()
            ->route('admin.home.hero.edit')
            ->with('success', 'Banner agregado a la portada con éxito.');
    }

    public function updateSlide(UpdateHomeHeroSlideRequest $request, HomeHeroSlide $slide): RedirectResponse
    {
        $this->homeHeroService->updateSlide($slide, $request->validated());

        return redirect()
            ->route('admin.home.hero.edit')
            ->with('success', 'Banner actualizado con éxito.');
    }

    public function destroySlide(HomeHeroSlide $slide): RedirectResponse
    {
        $this->homeHeroService->deleteSlide($slide);

        return redirect()
            ->route('admin.home.hero.edit')
            ->with('success', 'Banner eliminado de la portada con éxito.');
    }

    public function reorderSlides(ReorderHomeSlidesRequest $request): JsonResponse
    {
        try {
            $hero = $this->homeHeroService->singleton();
            $this->homeHeroService->reorderSlides($hero, $request->validated()['ids']);

            return response()->json(['ok' => true]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => 'No se pudo guardar el nuevo orden.'], 500);
        }
    }

    public function toggleSlide(HomeHeroSlide $slide): JsonResponse
    {
        try {
            $updated = $this->homeHeroService->toggleSlideActive($slide);

            return response()->json(['ok' => true, 'is_active' => $updated->is_active]);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => 'No se pudo cambiar el estado.'], 500);
        }
    }
}
