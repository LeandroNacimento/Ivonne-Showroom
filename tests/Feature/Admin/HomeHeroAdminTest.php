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
        $response->assertSee('Portada principal');
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

    public function test_admin_can_create_a_slide_with_both_images(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post(route('admin.home.hero.slides.store'), [
            'desktop_image' => UploadedFile::fake()->image('hero-desktop.jpg', 1920, 1080),
            'mobile_image'  => UploadedFile::fake()->image('hero-mobile.jpg', 1080, 1350),
            'alt_text'      => 'Portada principal',
            'link_type'     => 'none',
            'is_active'     => '1',
        ]);

        $response->assertRedirect(route('admin.home.hero.edit'));

        $slide = HomeHero::singleton()->slides()->firstOrFail();

        $this->assertSame('Portada principal', $slide->alt_text);
        $this->assertNotNull($slide->desktop_image_path);
        $this->assertNotNull($slide->mobile_image_path);
        Storage::disk('public')->assertExists($slide->desktop_image_path);
        Storage::disk('public')->assertExists($slide->mobile_image_path);
    }

    public function test_admin_can_update_slide_and_replace_images(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $hero = HomeHero::singleton();

        $slide = $hero->slides()->create([
            'desktop_image_path' => 'home-hero/desktop/old.jpg',
            'mobile_image_path'  => 'home-hero/mobile/old.jpg',
            'alt_text'           => 'Original',
            'link_type'          => 'none',
            'position'           => 0,
            'is_active'          => true,
        ]);

        // Crear los archivos en storage falso para que puedan ser eliminados
        Storage::disk('public')->put('home-hero/desktop/old.jpg', 'fake');
        Storage::disk('public')->put('home-hero/mobile/old.jpg', 'fake');

        $response = $this->actingAs($admin)->put(route('admin.home.hero.slides.update', $slide), [
            'slide_id'      => $slide->id,
            'alt_text'      => 'Portada actualizada',
            'desktop_image' => UploadedFile::fake()->image('new-desktop.jpg', 1920, 1080),
            'mobile_image'  => UploadedFile::fake()->image('new-mobile.jpg', 1080, 1350),
            'link_type'     => 'none',
            'is_active'     => '0',
        ]);

        $response->assertRedirect(route('admin.home.hero.edit'));

        $updated = $slide->fresh();

        $this->assertSame('Portada actualizada', $updated->alt_text);
        $this->assertFalse($updated->is_active);
        Storage::disk('public')->assertMissing('home-hero/desktop/old.jpg');
        Storage::disk('public')->assertMissing('home-hero/mobile/old.jpg');
        Storage::disk('public')->assertExists($updated->desktop_image_path);
        Storage::disk('public')->assertExists($updated->mobile_image_path);
    }

    public function test_admin_can_delete_a_slide_and_its_images_are_removed(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $hero = HomeHero::singleton();

        Storage::disk('public')->put('home-hero/desktop/delete-me.jpg', 'fake');
        Storage::disk('public')->put('home-hero/mobile/delete-me.jpg', 'fake');

        $slide = $hero->slides()->create([
            'desktop_image_path' => 'home-hero/desktop/delete-me.jpg',
            'mobile_image_path'  => 'home-hero/mobile/delete-me.jpg',
            'alt_text'           => 'To delete',
            'link_type'          => 'none',
            'position'           => 0,
            'is_active'          => true,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.home.hero.slides.destroy', $slide));

        $response->assertRedirect(route('admin.home.hero.edit'));
        $this->assertDatabaseMissing('home_hero_slides', ['id' => $slide->id]);
        Storage::disk('public')->assertMissing('home-hero/desktop/delete-me.jpg');
        Storage::disk('public')->assertMissing('home-hero/mobile/delete-me.jpg');
    }

    public function test_admin_can_add_a_new_slide_without_name_and_it_is_appended(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $hero = HomeHero::singleton();

        $hero->slides()->create([
            'desktop_image_path' => 'home-hero/desktop/existing.jpg',
            'alt_text'           => 'Existing slide',
            'link_type'          => 'none',
            'position'           => 0,
            'is_active'          => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.home.hero.slides.store'), [
            'desktop_image' => UploadedFile::fake()->image('new-desktop.jpg', 1920, 1080),
            'mobile_image'  => UploadedFile::fake()->image('new-mobile.jpg', 1080, 1350),
            'alt_text'      => 'Nueva slide al final',
            'link_type'     => 'none',
            'is_active'     => '1',
        ]);

        $response->assertRedirect(route('admin.home.hero.edit'));
        $response->assertSessionDoesntHaveErrors();

        $appended = $hero->fresh()->slides()->where('alt_text', 'Nueva slide al final')->first();

        $this->assertNotNull($appended);
        $this->assertSame(1, $appended->position);
    }

    public function test_create_requires_both_images_and_alt_text(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();

        $response = $this->from(route('admin.home.hero.edit'))
            ->actingAs($admin)
            ->post(route('admin.home.hero.slides.store'), [
                'link_type' => 'none',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.home.hero.edit'));
        $response->assertSessionHasErrors(['desktop_image', 'mobile_image', 'alt_text'], null, 'createSlide');
    }

    public function test_create_with_external_link_requires_url(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();

        $response = $this->from(route('admin.home.hero.edit'))
            ->actingAs($admin)
            ->post(route('admin.home.hero.slides.store'), [
                'desktop_image' => UploadedFile::fake()->image('desktop.jpg'),
                'mobile_image'  => UploadedFile::fake()->image('mobile.jpg'),
                'alt_text'      => 'Banner con link',
                'link_type'     => 'external',
                'link_url'      => '',
                'is_active'     => '1',
            ]);

        $response->assertRedirect(route('admin.home.hero.edit'));
        $response->assertSessionHasErrors(['link_url'], null, 'createSlide');
    }

    public function test_reorder_endpoint_updates_positions(): void
    {
        $admin = User::factory()->admin()->create();
        $hero = HomeHero::singleton();

        $first = $hero->slides()->create([
            'desktop_image_path' => 'home-hero/desktop/first.jpg',
            'alt_text' => 'First', 'link_type' => 'none', 'position' => 0, 'is_active' => true,
        ]);

        $second = $hero->slides()->create([
            'desktop_image_path' => 'home-hero/desktop/second.jpg',
            'alt_text' => 'Second', 'link_type' => 'none', 'position' => 1, 'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.home.hero.slides.reorder'), [
                'ids' => [$second->id, $first->id],
            ]);

        $response->assertOk()->assertJson(['ok' => true]);

        $this->assertSame(0, $second->fresh()->position);
        $this->assertSame(1, $first->fresh()->position);
    }

    public function test_toggle_endpoint_inverts_slide_active_state(): void
    {
        $admin = User::factory()->admin()->create();
        $hero = HomeHero::singleton();

        $slide = $hero->slides()->create([
            'desktop_image_path' => 'home-hero/desktop/toggle.jpg',
            'alt_text' => 'Toggle', 'link_type' => 'none', 'position' => 0, 'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->patchJson(route('admin.home.hero.slides.toggle', $slide));

        $response->assertOk()
            ->assertJson(['ok' => true, 'is_active' => false]);

        $this->assertFalse($slide->fresh()->is_active);
    }

    public function test_admin_can_deactivate_all_slides_and_fallback_is_shown(): void
    {
        $admin = User::factory()->admin()->create();
        $service = app(HomeHeroService::class);

        $hero = $service->singleton();

        $slide = $hero->slides()->create([
            'desktop_image_path' => 'home-hero/desktop/fallback.jpg',
            'alt_text'           => 'Fallback check',
            'link_type'          => 'none',
            'position'           => 0,
            'is_active'          => true,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.home.hero.slides.update', $slide), [
            'slide_id'  => $slide->id,
            'alt_text'  => 'Fallback check',
            'link_type' => 'none',
            'is_active' => '0',
        ]);

        $response->assertRedirect(route('admin.home.hero.edit'));
        $this->assertFalse($slide->fresh()->is_active);
        $this->assertNull($service->getRenderableHero());
    }
}
