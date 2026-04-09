<?php

namespace Tests\Feature;

use App\Models\HomeHero;
use App\Models\HomeHeroSlide;
use App\Services\HomeHeroService;
use App\Support\HomeHeroValidator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class HomeHeroPhaseOneTest extends TestCase
{
    use RefreshDatabase;

    public function test_singleton_hero_only_becomes_renderable_when_minimum_public_requirements_are_met(): void
    {
        Storage::fake('public');

        $service = app(HomeHeroService::class);
        $hero = $service->singleton();

        self::assertSame(HomeHero::SINGLETON_KEY, $hero->singleton_key);
        self::assertFalse($hero->is_renderable);
        self::assertNull($service->getRenderableHero());

        $hero = $service->updateContent([
            'eyebrow' => 'Nueva temporada',
            'title' => 'Coleccion principal',
            'description' => 'Una portada principal para la home.',
        ]);

        self::assertFalse($hero->is_renderable);

        $slide = $service->createSlide($hero, [
            'image' => UploadedFile::fake()->image('hero.jpg', 1600, 900),
            'alt_text' => 'Portada principal',
            'is_active' => false,
        ]);

        self::assertFalse($service->singleton()->fresh()->is_renderable);
        self::assertNull($service->getRenderableHero());

        $service->updateSlide($slide, [
            'is_active' => true,
        ]);

        $renderableHero = $service->getRenderableHero();

        self::assertNotNull($renderableHero);
        self::assertTrue($renderableHero->is_renderable);
    }

    public function test_active_slides_are_read_in_position_then_id_order(): void
    {
        $hero = HomeHero::singleton();

        $first = $hero->slides()->create([
            'image_path' => 'home-hero/first.jpg',
            'alt_text' => 'First',
            'position' => 2,
            'is_active' => true,
        ]);

        $second = $hero->slides()->create([
            'image_path' => 'home-hero/second.jpg',
            'alt_text' => 'Second',
            'position' => 1,
            'is_active' => true,
        ]);

        $third = $hero->slides()->create([
            'image_path' => 'home-hero/third.jpg',
            'alt_text' => 'Third',
            'position' => 1,
            'is_active' => true,
        ]);

        $hero->slides()->create([
            'image_path' => 'home-hero/inactive.jpg',
            'alt_text' => 'Inactive',
            'position' => 0,
            'is_active' => false,
        ]);

        self::assertSame(
            [$second->id, $third->id, $first->id],
            $hero->activeSlides()->pluck('id')->all()
        );
    }

    public function test_position_normalization_reindexes_existing_slides_in_backend(): void
    {
        $hero = HomeHero::singleton();

        $first = $hero->slides()->create([
            'image_path' => 'home-hero/ten.jpg',
            'alt_text' => 'Ten',
            'position' => 10,
            'is_active' => true,
        ]);

        $second = $hero->slides()->create([
            'image_path' => 'home-hero/three-a.jpg',
            'alt_text' => 'Three A',
            'position' => 3,
            'is_active' => true,
        ]);

        $third = $hero->slides()->create([
            'image_path' => 'home-hero/three-b.jpg',
            'alt_text' => 'Three B',
            'position' => 3,
            'is_active' => true,
        ]);

        app(HomeHeroService::class)->normalizePositions($hero);

        self::assertSame([0, 1, 2], [
            $second->fresh()->position,
            $third->fresh()->position,
            $first->fresh()->position,
        ]);
    }

    public function test_service_inserts_moves_and_deletes_slides_without_leaving_position_gaps(): void
    {
        Storage::fake('public');

        $service = app(HomeHeroService::class);
        $hero = $service->singleton();

        $first = $service->createSlide($hero, [
            'image' => UploadedFile::fake()->image('first.jpg'),
            'alt_text' => 'First slide',
        ]);

        $second = $service->createSlide($hero, [
            'image' => UploadedFile::fake()->image('second.jpg'),
            'alt_text' => 'Second slide',
            'position' => 0,
        ]);

        $third = $service->createSlide($hero, [
            'image' => UploadedFile::fake()->image('third.jpg'),
            'alt_text' => 'Third slide',
            'position' => 1,
        ]);

        self::assertSame(
            [$second->id, $third->id, $first->id],
            $hero->fresh()->slides()->pluck('id')->all()
        );

        $service->updateSlide($first->fresh(), [
            'position' => 0,
        ]);

        self::assertSame(
            [$first->id, $second->id, $third->id],
            $hero->fresh()->slides()->pluck('id')->all()
        );

        $service->deleteSlide($second->fresh());

        self::assertSame(
            [0, 1],
            $hero->fresh()->slides()->pluck('position')->all()
        );
    }

    public function test_replacing_and_deleting_slide_images_cleans_up_files_in_normal_flow(): void
    {
        Storage::fake('public');

        $service = app(HomeHeroService::class);
        $hero = $service->singleton();

        $slide = $service->createSlide($hero, [
            'image' => UploadedFile::fake()->image('initial.jpg'),
            'alt_text' => 'Initial slide',
        ]);

        $originalPath = $slide->image_path;
        Storage::disk('public')->assertExists($originalPath);

        $updatedSlide = $service->updateSlide($slide, [
            'image' => UploadedFile::fake()->image('replacement.jpg'),
        ]);

        Storage::disk('public')->assertMissing($originalPath);
        Storage::disk('public')->assertExists($updatedSlide->image_path);

        $replacementPath = $updatedSlide->image_path;

        $service->deleteSlide($updatedSlide);

        Storage::disk('public')->assertMissing($replacementPath);
        $this->assertDatabaseMissing('home_hero_slides', [
            'id' => $updatedSlide->id,
        ]);
    }

    public function test_validation_enforces_cta_consistency_and_slide_requirements(): void
    {
        try {
            HomeHeroValidator::validateContent([
                'cta_label' => 'Comprar ahora',
            ]);

            $this->fail('Expected CTA validation to fail when URL is missing.');
        } catch (ValidationException $e) {
            self::assertArrayHasKey('cta_url', $e->errors());
        }

        try {
            HomeHeroValidator::validateSlideCreate([
                'position' => 0,
            ]);

            $this->fail('Expected slide validation to fail when image and alt text are missing.');
        } catch (ValidationException $e) {
            self::assertArrayHasKey('image', $e->errors());
            self::assertArrayHasKey('alt_text', $e->errors());
        }
    }

    public function test_hero_with_inconsistent_cta_is_not_renderable(): void
    {
        $hero = HomeHero::singleton();

        $hero->forceFill([
            'title' => 'Coleccion principal',
            'description' => 'Portada principal de la home.',
            'cta_label' => 'Ver catalogo',
            'cta_url' => null,
        ])->save();

        $hero->slides()->create([
            'image_path' => 'home-hero/renderable-check.jpg',
            'alt_text' => 'Portada principal',
            'position' => 0,
            'is_active' => true,
        ]);

        self::assertFalse($hero->fresh()->is_renderable);
        self::assertNull(app(HomeHeroService::class)->getRenderableHero());
    }
}
