<?php

namespace Tests\Feature;

use App\Models\HomeHero;
use Tests\TestCase;

class ShowroomTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_home_page_is_accessible()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_home_uses_fallback_hero_when_admin_hero_is_not_renderable(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('data-home-hero-mode="fallback"', false);
        $response->assertSee('Estilo y elegancia');
    }

    public function test_home_renders_static_admin_hero_when_one_active_slide_exists(): void
    {
        $hero = HomeHero::singleton();

        $hero->slides()->create([
            'desktop_image_path' => 'home-hero/static.jpg',
            'alt_text' => 'Hero estatico',
            'position' => 0,
            'is_active' => true,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('data-home-hero-mode="static"', false);
        $response->assertSee('/storage/home-hero/static.jpg', false);
        $response->assertDontSee('Contactar');
        $response->assertDontSee('Estilo y elegancia');
    }

    public function test_home_renders_carousel_admin_hero_when_multiple_active_slides_exist(): void
    {
        $hero = HomeHero::singleton();

        $hero->slides()->create([
            'desktop_image_path' => 'home-hero/slide-1.jpg',
            'alt_text' => 'Slide uno',
            'position' => 0,
            'is_active' => true,
        ]);

        $hero->slides()->create([
            'desktop_image_path' => 'home-hero/slide-2.jpg',
            'alt_text' => 'Slide dos',
            'position' => 1,
            'is_active' => true,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('data-home-hero-mode="carousel"', false);
        $response->assertSee('x-data="homeHeroCarousel', false);
        $response->assertSee('/storage/home-hero/slide-1.jpg', false);
        $response->assertSee('/storage/home-hero/slide-2.jpg', false);
        $response->assertDontSee('class="hidden absolute inset-0', false);
        $response->assertSee('style="display: none;"', false);
        $response->assertDontSee('Contactar');
        $response->assertDontSee('Estilo y elegancia');
    }

    public function test_home_renders_admin_hero_without_text_when_only_images_exist(): void
    {
        $hero = HomeHero::singleton();

        $hero->slides()->create([
            'desktop_image_path' => 'home-hero/image-only.jpg',
            'alt_text' => 'Hero solo imagen',
            'position' => 0,
            'is_active' => true,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('data-home-hero-mode="static"', false);
        $response->assertSee('/storage/home-hero/image-only.jpg', false);
        $response->assertDontSee('Contactar');
        $response->assertDontSee('Ver Catalogo');
        $response->assertDontSee('Estilo y elegancia');
    }

    public function test_catalog_page_is_accessible()
    {
        $response = $this->get('/catalogo');
        $response->assertStatus(200);
    }

    public function test_contact_page_is_accessible()
    {
        $response = $this->get('/contacto');
        $response->assertStatus(200);
    }
}
