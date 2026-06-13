<?php

namespace Tests\Feature;

use App\Models\HomeHero;
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

    public function test_singleton_hero_becomes_renderable_when_at_least_one_active_slide_exists(): void
    {
        Storage::fake('public');

        $service = app(HomeHeroService::class);
        $hero = $service->singleton();

        self::assertSame(HomeHero::SINGLETON_KEY, $hero->singleton_key);
        self::assertFalse($hero->is_renderable);
        self::assertNull($service->getRenderableHero());

        $slide = $service->createSlide($hero, [
            'desktop_image' => UploadedFile::fake()->image('hero-desktop.jpg', 1920, 1080),
            'mobile_image' => UploadedFile::fake()->image('hero-mobile.jpg', 1080, 1350),
            'alt_text' => 'Portada principal',
            'link_type' => 'none',
            'is_active' => false,
        ]);

        self::assertFalse($service->singleton()->fresh()->is_renderable);
        self::assertNull($service->getRenderableHero());

        $service->updateSlide($slide, [
            'alt_text' => 'Portada principal',
            'link_type' => 'none',
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
            'desktop_image_path' => 'home-hero/desktop/first.jpg',
            'alt_text' => 'First',
            'link_type' => 'none',
            'position' => 2,
            'is_active' => true,
        ]);

        $second = $hero->slides()->create([
            'desktop_image_path' => 'home-hero/desktop/second.jpg',
            'alt_text' => 'Second',
            'link_type' => 'none',
            'position' => 1,
            'is_active' => true,
        ]);

        $third = $hero->slides()->create([
            'desktop_image_path' => 'home-hero/desktop/third.jpg',
            'alt_text' => 'Third',
            'link_type' => 'none',
            'position' => 1,
            'is_active' => true,
        ]);

        $hero->slides()->create([
            'desktop_image_path' => 'home-hero/desktop/inactive.jpg',
            'alt_text' => 'Inactive',
            'link_type' => 'none',
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
            'desktop_image_path' => 'home-hero/desktop/ten.jpg',
            'alt_text' => 'Ten',
            'link_type' => 'none',
            'position' => 10,
            'is_active' => true,
        ]);

        $second = $hero->slides()->create([
            'desktop_image_path' => 'home-hero/desktop/three-a.jpg',
            'alt_text' => 'Three A',
            'link_type' => 'none',
            'position' => 3,
            'is_active' => true,
        ]);

        $third = $hero->slides()->create([
            'desktop_image_path' => 'home-hero/desktop/three-b.jpg',
            'alt_text' => 'Three B',
            'link_type' => 'none',
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

    public function test_service_inserts_and_deletes_slides_without_leaving_position_gaps(): void
    {
        Storage::fake('public');

        $service = app(HomeHeroService::class);
        $hero = $service->singleton();

        $first = $service->createSlide($hero, [
            'desktop_image' => UploadedFile::fake()->image('first-desktop.jpg'),
            'mobile_image' => UploadedFile::fake()->image('first-mobile.jpg'),
            'alt_text' => 'First slide',
            'link_type' => 'none',
        ]);

        $second = $service->createSlide($hero, [
            'desktop_image' => UploadedFile::fake()->image('second-desktop.jpg'),
            'mobile_image' => UploadedFile::fake()->image('second-mobile.jpg'),
            'alt_text' => 'Second slide',
            'link_type' => 'none',
        ]);

        $third = $service->createSlide($hero, [
            'desktop_image' => UploadedFile::fake()->image('third-desktop.jpg'),
            'mobile_image' => UploadedFile::fake()->image('third-mobile.jpg'),
            'alt_text' => 'Third slide',
            'link_type' => 'none',
        ]);

        // El reorder asigna posiciones 0, 1, 2 en el orden dado
        $service->reorderSlides($hero, [$second->id, $third->id, $first->id]);

        self::assertSame(
            [$second->id, $third->id, $first->id],
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
            'desktop_image' => UploadedFile::fake()->image('initial-desktop.jpg'),
            'mobile_image' => UploadedFile::fake()->image('initial-mobile.jpg'),
            'alt_text' => 'Initial slide',
            'link_type' => 'none',
        ]);

        $originalDesktopPath = $slide->desktop_image_path;
        $originalMobilePath = $slide->mobile_image_path;
        Storage::disk('public')->assertExists($originalDesktopPath);
        Storage::disk('public')->assertExists($originalMobilePath);

        $updatedSlide = $service->updateSlide($slide, [
            'desktop_image' => UploadedFile::fake()->image('replacement-desktop.jpg'),
            'mobile_image' => UploadedFile::fake()->image('replacement-mobile.jpg'),
            'alt_text' => 'Replaced slide',
            'link_type' => 'none',
        ]);

        Storage::disk('public')->assertMissing($originalDesktopPath);
        Storage::disk('public')->assertMissing($originalMobilePath);
        Storage::disk('public')->assertExists($updatedSlide->desktop_image_path);
        Storage::disk('public')->assertExists($updatedSlide->mobile_image_path);

        $replacementDesktopPath = $updatedSlide->desktop_image_path;
        $replacementMobilePath = $updatedSlide->mobile_image_path;

        $service->deleteSlide($updatedSlide);

        Storage::disk('public')->assertMissing($replacementDesktopPath);
        Storage::disk('public')->assertMissing($replacementMobilePath);
        $this->assertDatabaseMissing('home_hero_slides', [
            'id' => $updatedSlide->id,
        ]);
    }

    public function test_validation_requires_both_images_and_alt_text_on_create(): void
    {
        try {
            HomeHeroValidator::validateSlideCreate([
                'link_type' => 'none',
            ]);

            $this->fail('Expected slide validation to fail when images and alt text are missing.');
        } catch (ValidationException $e) {
            self::assertArrayHasKey('desktop_image', $e->errors());
            self::assertArrayHasKey('mobile_image', $e->errors());
            self::assertArrayHasKey('alt_text', $e->errors());
        }
    }

    public function test_validation_requires_link_url_when_link_type_is_external(): void
    {
        try {
            HomeHeroValidator::validateSlideCreate([
                'desktop_image' => UploadedFile::fake()->image('desktop.jpg'),
                'mobile_image' => UploadedFile::fake()->image('mobile.jpg'),
                'alt_text' => 'Banner',
                'link_type' => 'external',
                'link_url' => '',
            ]);

            $this->fail('Expected validation to fail when link_url is empty with link_type=external.');
        } catch (ValidationException $e) {
            self::assertArrayHasKey('link_url', $e->errors());
        }
    }

    public function test_hero_is_renderable_only_when_active_slides_exist(): void
    {
        $hero = HomeHero::singleton();

        self::assertFalse($hero->fresh()->is_renderable);

        $hero->slides()->create([
            'desktop_image_path' => 'home-hero/desktop/renderable-check.jpg',
            'alt_text' => 'Portada principal',
            'link_type' => 'none',
            'position' => 0,
            'is_active' => true,
        ]);

        self::assertTrue($hero->fresh()->is_renderable);
        self::assertNotNull(app(HomeHeroService::class)->getRenderableHero());
    }

    public function test_toggle_slide_inverts_is_active(): void
    {
        Storage::fake('public');

        $service = app(HomeHeroService::class);
        $hero = $service->singleton();

        $slide = $service->createSlide($hero, [
            'desktop_image' => UploadedFile::fake()->image('desktop.jpg'),
            'mobile_image' => UploadedFile::fake()->image('mobile.jpg'),
            'alt_text' => 'Toggle test',
            'link_type' => 'none',
            'is_active' => true,
        ]);

        self::assertTrue($slide->is_active);

        $toggled = $service->toggleSlideActive($slide);
        self::assertFalse($toggled->is_active);

        $toggledBack = $service->toggleSlideActive($toggled);
        self::assertTrue($toggledBack->is_active);
    }

    public function test_mobile_image_falls_back_to_desktop_when_not_present(): void
    {
        $hero = HomeHero::singleton();

        $slide = $hero->slides()->create([
            'desktop_image_path' => 'home-hero/desktop/only-desktop.jpg',
            'mobile_image_path' => null,
            'alt_text' => 'Solo desktop',
            'link_type' => 'none',
            'position' => 0,
            'is_active' => true,
        ]);

        self::assertFalse($slide->has_mobile_image);
        self::assertStringContainsString('only-desktop.jpg', $slide->public_mobile_image_url);
    }
}
