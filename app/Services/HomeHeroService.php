<?php

namespace App\Services;

use App\Models\HomeHero;
use App\Models\HomeHeroSlide;
use App\Support\HomeHeroValidator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class HomeHeroService
{
    public function singleton(): HomeHero
    {
        return HomeHero::singleton();
    }

    public function getRenderableHero(): ?HomeHero
    {
        $hero = $this->singleton()->load('activeSlides');

        return $hero->is_renderable ? $hero : null;
    }

    public function updateContent(array $attributes): HomeHero
    {
        $hero = $this->singleton();
        $validated = HomeHeroValidator::validateContent($attributes);

        $hero->fill($validated);
        $hero->save();

        return $hero->fresh();
    }

    public function createSlide(HomeHero $hero, array $attributes): HomeHeroSlide
    {
        $hero = $hero->exists ? $hero : $this->singleton();
        $validated = HomeHeroValidator::validateSlideCreate($attributes);
        $path = $this->storeImage($validated['image']);

        try {
            return DB::transaction(function () use ($hero, $validated, $path) {
                $orderedIds = $hero->slides()->pluck('id')->all();
                $targetPosition = $this->normalizeTargetPosition($validated['position'] ?? null, count($orderedIds));

                $slide = $hero->slides()->create([
                    'image_path' => $path,
                    'alt_text' => $validated['alt_text'],
                    'position' => $targetPosition,
                    'is_active' => $validated['is_active'] ?? true,
                ]);

                array_splice($orderedIds, $targetPosition, 0, [$slide->id]);
                $this->reindexSlidesByIds($hero, $orderedIds);

                return $slide->fresh();
            });
        } catch (Throwable $e) {
            Storage::disk('public')->delete($path);

            throw $e;
        }
    }

    public function updateSlide(HomeHeroSlide $slide, array $attributes): HomeHeroSlide
    {
        $validated = HomeHeroValidator::validateSlideUpdate($attributes);
        $newPath = isset($validated['image']) ? $this->storeImage($validated['image']) : null;
        $oldPath = $slide->image_path;

        try {
            $updatedSlide = DB::transaction(function () use ($slide, $validated, $newPath) {
                $hero = $slide->homeHero()->firstOrFail();
                $orderedIds = $hero->slides()
                    ->whereKeyNot($slide->id)
                    ->pluck('id')
                    ->all();

                $currentIndex = $hero->slides()->pluck('id')->search($slide->id);
                $targetPosition = array_key_exists('position', $validated)
                    ? $this->normalizeTargetPosition($validated['position'], count($orderedIds))
                    : max(0, (int) $currentIndex);

                if (array_key_exists('alt_text', $validated)) {
                    $slide->alt_text = $validated['alt_text'];
                }

                if (array_key_exists('is_active', $validated)) {
                    $slide->is_active = $validated['is_active'];
                }

                if ($newPath !== null) {
                    $slide->image_path = $newPath;
                }

                $slide->position = $targetPosition;
                $slide->save();

                array_splice($orderedIds, $targetPosition, 0, [$slide->id]);
                $this->reindexSlidesByIds($hero, $orderedIds);

                return $slide->fresh();
            });
        } catch (Throwable $e) {
            if ($newPath !== null) {
                Storage::disk('public')->delete($newPath);
            }

            throw $e;
        }

        if ($newPath !== null && $oldPath !== $newPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return $updatedSlide;
    }

    public function deleteSlide(HomeHeroSlide $slide): void
    {
        $hero = $slide->homeHero()->firstOrFail();
        $path = $slide->image_path;

        DB::transaction(function () use ($hero, $slide) {
            $orderedIds = $hero->slides()
                ->whereKeyNot($slide->id)
                ->pluck('id')
                ->all();

            $slide->delete();
            $this->reindexSlidesByIds($hero, $orderedIds);
        });

        Storage::disk('public')->delete($path);
    }

    public function normalizePositions(HomeHero $hero): void
    {
        $orderedIds = $hero->slides()->pluck('id')->all();

        $this->reindexSlidesByIds($hero, $orderedIds);
    }

    private function reindexSlidesByIds(HomeHero $hero, array $orderedIds): void
    {
        foreach (array_values($orderedIds) as $position => $slideId) {
            $hero->slides()->whereKey($slideId)->update([
                'position' => $position,
            ]);
        }
    }

    private function normalizeTargetPosition(?int $requestedPosition, int $count): int
    {
        if ($requestedPosition === null) {
            return $count;
        }

        return max(0, min($requestedPosition, $count));
    }

    private function storeImage(UploadedFile $image): string
    {
        return $image->store(HomeHeroSlide::STORAGE_DIRECTORY, 'public');
    }
}
