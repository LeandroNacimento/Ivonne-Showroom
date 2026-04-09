<?php

namespace Tests\Feature\Admin;

use App\Models\HomeHero;
use App\Models\User;
use App\Services\HomeHeroService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HomeHeroAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_home_hero_admin_screen(): void
    {
        $response = $this->get(route('admin.home.hero.edit'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_home_hero_admin_screen(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get(route('admin.home.hero.edit'));

        $response->assertOk();
        $response->assertSee('Portada principal de la tienda');
        $this->assertDatabaseHas('home_heroes', [
            'singleton_key' => HomeHero::SINGLETON_KEY,
        ]);
    }

    public function test_authenticated_non_admin_cannot_access_home_hero_admin_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.home.hero.edit'));

        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_update_home_hero_content(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->put(route('admin.home.hero.update'), [
            'eyebrow' => 'Nueva temporada',
            'title' => 'Colección Otoño',
            'description' => 'Una portada principal simple y clara.',
            'cta_label' => 'Ver catálogo',
            'cta_url' => 'https://example.com/catalogo',
        ]);

        $response->assertRedirect(route('admin.home.hero.edit'));
        $this->assertDatabaseHas('home_heroes', [
            'singleton_key' => HomeHero::SINGLETON_KEY,
            'title' => 'Colección Otoño',
            'cta_label' => 'Ver catálogo',
        ]);
    }

    public function test_admin_can_create_update_replace_and_delete_slides_from_admin(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();

        $createResponse = $this->actingAs($admin)->post(route('admin.home.hero.slides.store'), [
            'image' => UploadedFile::fake()->image('hero-slide.jpg', 1600, 900),
            'alt_text' => 'Portada principal',
            'position' => 0,
            'is_active' => '1',
        ]);

        $createResponse->assertRedirect(route('admin.home.hero.edit'));

        $slide = HomeHero::singleton()->slides()->firstOrFail();
        $originalPath = $slide->image_path;
        Storage::disk('public')->assertExists($originalPath);

        $updateResponse = $this->actingAs($admin)->put(route('admin.home.hero.slides.update', $slide), [
            'slide_id' => $slide->id,
            'alt_text' => 'Portada principal actualizada',
            'position' => 0,
            'is_active' => '0',
            'image' => UploadedFile::fake()->image('hero-slide-updated.jpg', 1600, 900),
        ]);

        $updateResponse->assertRedirect(route('admin.home.hero.edit'));

        $updatedSlide = $slide->fresh();

        $this->assertSame('Portada principal actualizada', $updatedSlide->alt_text);
        $this->assertFalse($updatedSlide->is_active);
        Storage::disk('public')->assertMissing($originalPath);
        Storage::disk('public')->assertExists($updatedSlide->image_path);

        $replacementPath = $updatedSlide->image_path;

        $deleteResponse = $this->actingAs($admin)->delete(route('admin.home.hero.slides.destroy', $updatedSlide));

        $deleteResponse->assertRedirect(route('admin.home.hero.edit'));
        $this->assertDatabaseMissing('home_hero_slides', [
            'id' => $updatedSlide->id,
        ]);
        Storage::disk('public')->assertMissing($replacementPath);
    }

    public function test_admin_requests_validate_home_hero_constraints(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();

        $contentResponse = $this->from(route('admin.home.hero.edit'))
            ->actingAs($admin)
            ->put(route('admin.home.hero.update'), [
                'eyebrow' => '',
                'title' => '',
                'description' => '',
                'cta_label' => 'Ver catálogo',
                'cta_url' => '',
            ]);

        $contentResponse->assertRedirect(route('admin.home.hero.edit'));
        $contentResponse->assertSessionHasErrors(['title', 'description', 'cta_url'], null, 'heroContent');

        $slideResponse = $this->from(route('admin.home.hero.edit'))
            ->actingAs($admin)
            ->post(route('admin.home.hero.slides.store'), [
                'alt_text' => '',
                'position' => -1,
            ]);

        $slideResponse->assertRedirect(route('admin.home.hero.edit'));
        $slideResponse->assertSessionHasErrors(['image', 'alt_text', 'position', 'is_active'], null, 'createSlide');

        $hero = HomeHero::singleton();
        $slide = $hero->slides()->create([
            'image_path' => 'home-hero/existing.jpg',
            'alt_text' => 'Existing slide',
            'position' => 0,
            'is_active' => true,
        ]);

        $updateResponse = $this->from(route('admin.home.hero.edit'))
            ->actingAs($admin)
            ->put(route('admin.home.hero.slides.update', $slide), [
                'slide_id' => $slide->id,
                'alt_text' => '',
                'position' => 0,
            ]);

        $updateResponse->assertRedirect(route('admin.home.hero.edit'));
        $updateResponse->assertSessionHasErrors(['alt_text', 'is_active'], null, 'updateSlide');
        $updateResponse->assertSessionHasInput('slide_id', (string) $slide->id);
    }

    public function test_admin_can_deactivate_all_slides_and_service_keeps_safe_fallback_state(): void
    {
        $admin = User::factory()->admin()->create();
        $service = app(HomeHeroService::class);

        $hero = $service->updateContent([
            'title' => 'Colección Otoño',
            'description' => 'Portada principal simple y clara.',
        ]);

        $slide = $hero->slides()->create([
            'image_path' => 'home-hero/admin-fallback.jpg',
            'alt_text' => 'Fallback check',
            'position' => 0,
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.home.hero.slides.update', $slide), [
            'slide_id' => $slide->id,
            'alt_text' => 'Fallback check',
            'position' => 0,
            'is_active' => '0',
        ]);

        $response->assertRedirect(route('admin.home.hero.edit'));
        $this->assertFalse($slide->fresh()->is_active);
        $this->assertNull($service->getRenderableHero());
    }
}
