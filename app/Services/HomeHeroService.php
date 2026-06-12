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

    public function createSlide(HomeHero $hero, array $attributes): HomeHeroSlide
    {
        $hero = $hero->exists ? $hero : $this->singleton();
        $validated = HomeHeroValidator::validateSlideCreate($attributes);

        $desktopPath = $this->storeImageInDirectory(
            $validated['desktop_image'],
            HomeHeroSlide::STORAGE_DIRECTORY_DESKTOP
        );

        $mobilePath = $this->storeImageInDirectory(
            $validated['mobile_image'],
            HomeHeroSlide::STORAGE_DIRECTORY_MOBILE
        );

        try {
            return DB::transaction(function () use ($hero, $validated, $desktopPath, $mobilePath) {
                $orderedIds = $hero->slides()->pluck('id')->all();
                $targetPosition = count($orderedIds);

                $slide = $hero->slides()->create([
                    'name'               => $validated['name'] ?? null,
                    'desktop_image_path' => $desktopPath,
                    'mobile_image_path'  => $mobilePath,
                    'alt_text'           => $validated['alt_text'],
                    'link_type'          => $validated['link_type'] ?? 'none',
                    'link_url'           => $validated['link_url'] ?? null,
                    'position'           => $targetPosition,
                    'is_active'          => $validated['is_active'] ?? true,
                ]);

                $orderedIds[] = $slide->id;
                $this->reindexSlidesByIds($hero, $orderedIds);

                return $slide->fresh();
            });
        } catch (Throwable $e) {
            Storage::disk('public')->delete($desktopPath);
            Storage::disk('public')->delete($mobilePath);

            throw $e;
        }
    }

    public function updateSlide(HomeHeroSlide $slide, array $attributes): HomeHeroSlide
    {
        $validated = HomeHeroValidator::validateSlideUpdate($attributes);

        $newDesktopPath = isset($validated['desktop_image'])
            ? $this->storeImageInDirectory($validated['desktop_image'], HomeHeroSlide::STORAGE_DIRECTORY_DESKTOP)
            : null;

        $newMobilePath = isset($validated['mobile_image'])
            ? $this->storeImageInDirectory($validated['mobile_image'], HomeHeroSlide::STORAGE_DIRECTORY_MOBILE)
            : null;

        $oldDesktopPath = $slide->desktop_image_path;
        $oldMobilePath  = $slide->mobile_image_path;

        try {
            $updatedSlide = DB::transaction(function () use ($slide, $validated, $newDesktopPath, $newMobilePath) {
                if (array_key_exists('name', $validated)) {
                    $slide->name = $validated['name'];
                }

                if (array_key_exists('alt_text', $validated)) {
                    $slide->alt_text = $validated['alt_text'];
                }

                if (array_key_exists('link_type', $validated)) {
                    $slide->link_type = $validated['link_type'];
                }

                if (array_key_exists('link_url', $validated)) {
                    $slide->link_url = $validated['link_url'];
                }

                if (array_key_exists('is_active', $validated)) {
                    $slide->is_active = $validated['is_active'];
                }

                if ($newDesktopPath !== null) {
                    $slide->desktop_image_path = $newDesktopPath;
                }

                if ($newMobilePath !== null) {
                    $slide->mobile_image_path = $newMobilePath;
                }

                $slide->save();

                return $slide->fresh();
            });
        } catch (Throwable $e) {
            if ($newDesktopPath !== null) {
                Storage::disk('public')->delete($newDesktopPath);
            }

            if ($newMobilePath !== null) {
                Storage::disk('public')->delete($newMobilePath);
            }

            throw $e;
        }

        // Borrar archivos antiguos solo si fueron reemplazados
        if ($newDesktopPath !== null && $oldDesktopPath !== $newDesktopPath) {
            Storage::disk('public')->delete($oldDesktopPath);
        }

        if ($newMobilePath !== null && $oldMobilePath !== null && $oldMobilePath !== $newMobilePath) {
            Storage::disk('public')->delete($oldMobilePath);
        }

        return $updatedSlide;
    }

    public function deleteSlide(HomeHeroSlide $slide): void
    {
        $hero         = $slide->homeHero()->firstOrFail();
        $desktopPath  = $slide->desktop_image_path;
        $mobilePath   = $slide->mobile_image_path;

        DB::transaction(function () use ($hero, $slide) {
            $orderedIds = $hero->slides()
                ->whereKeyNot($slide->id)
                ->pluck('id')
                ->all();

            $slide->delete();
            $this->reindexSlidesByIds($hero, $orderedIds);
        });

        Storage::disk('public')->delete($desktopPath);

        if ($mobilePath !== null) {
            Storage::disk('public')->delete($mobilePath);
        }
    }

    public function reorderSlides(HomeHero $hero, array $orderedIds): void
    {
        $this->reindexSlidesByIds($hero, $orderedIds);
    }

    public function toggleSlideActive(HomeHeroSlide $slide): HomeHeroSlide
    {
        $slide->is_active = ! $slide->is_active;
        $slide->save();

        return $slide->fresh();
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

    private function storeImageInDirectory(UploadedFile $image, string $directory): string
    {
        return $image->store($directory, 'public');
    }
}
