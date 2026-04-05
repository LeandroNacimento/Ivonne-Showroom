<?php

namespace Tests\Feature;

use App\Livewire\CatalogPage;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductImageSourceTruthTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_color_prefers_canonical_product_images_over_legacy_image(): void
    {
        $product = Product::factory()->create();
        $color = ProductColor::factory()->create([
            'product_id' => $product->id,
            'image' => 'https://legacy.example.com/legacy.jpg',
        ]);

        ProductImage::create([
            'product_color_id' => $color->id,
            'path' => 'products/canonical-2.jpg',
            'position' => 1,
        ]);

        ProductImage::create([
            'product_color_id' => $color->id,
            'path' => 'products/canonical-1.jpg',
            'position' => 0,
        ]);

        $color->load('images');

        self::assertSame(
            \Illuminate\Support\Facades\Storage::url('products/canonical-1.jpg'),
            $color->public_primary_image_url
        );

        self::assertSame(
            [
                \Illuminate\Support\Facades\Storage::url('products/canonical-1.jpg'),
                \Illuminate\Support\Facades\Storage::url('products/canonical-2.jpg'),
            ],
            $color->public_gallery_urls
        );
    }

    public function test_product_color_falls_back_to_legacy_image_when_no_canonical_images_exist(): void
    {
        $product = Product::factory()->create();
        $color = ProductColor::factory()->create([
            'product_id' => $product->id,
            'image' => 'https://legacy.example.com/legacy.jpg',
        ]);

        self::assertSame('https://legacy.example.com/legacy.jpg', $color->public_primary_image_url);
        self::assertSame(['https://legacy.example.com/legacy.jpg'], $color->public_gallery_urls);
    }

    public function test_catalog_card_renders_canonical_color_image_instead_of_legacy_image(): void
    {
        $category = Category::factory()->create([
            'supports_size' => true,
            'supports_color' => true,
        ]);

        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);

        $color = ProductColor::factory()->create([
            'product_id' => $product->id,
            'name' => 'Rojo',
            'image' => 'https://legacy.example.com/legacy.jpg',
            'position' => 0,
        ]);

        ProductVariation::factory()->create([
            'product_id' => $product->id,
            'product_color_id' => $color->id,
            'stock' => 5,
            'price' => 1000,
            'size' => 'M',
        ]);

        ProductImage::create([
            'product_color_id' => $color->id,
            'path' => 'products/catalog-canonical.jpg',
            'position' => 0,
        ]);

        Livewire::test(CatalogPage::class)
            ->assertSee(\Illuminate\Support\Facades\Storage::url('products/catalog-canonical.jpg'))
            ->assertDontSee('https://legacy.example.com/legacy.jpg');
    }

    public function test_product_detail_builds_gallery_from_canonical_images_with_legacy_fallback_per_color(): void
    {
        $category = Category::factory()->create([
            'supports_size' => true,
            'supports_color' => true,
        ]);

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'slug' => 'producto-galeria-test',
        ]);

        $red = ProductColor::factory()->create([
            'product_id' => $product->id,
            'name' => 'Rojo',
            'position' => 0,
            'image' => 'https://legacy.example.com/red-legacy.jpg',
        ]);

        $blue = ProductColor::factory()->create([
            'product_id' => $product->id,
            'name' => 'Azul',
            'position' => 1,
            'image' => 'https://legacy.example.com/blue-legacy.jpg',
        ]);

        ProductVariation::factory()->create([
            'product_id' => $product->id,
            'product_color_id' => $red->id,
            'stock' => 5,
            'price' => 1000,
            'size' => 'M',
        ]);

        ProductVariation::factory()->create([
            'product_id' => $product->id,
            'product_color_id' => $blue->id,
            'stock' => 3,
            'price' => 1200,
            'size' => 'L',
        ]);

        ProductImage::create([
            'product_color_id' => $red->id,
            'path' => 'products/red-1.jpg',
            'position' => 0,
        ]);

        ProductImage::create([
            'product_color_id' => $red->id,
            'path' => 'products/red-2.jpg',
            'position' => 1,
        ]);

        $response = $this->get(route('product.show', $product->slug));

        $response->assertOk();
        $response->assertSee('products\/red-1.jpg', false);
        $response->assertSee('products\/red-2.jpg', false);
        $response->assertSee('https:\/\/legacy.example.com\/blue-legacy.jpg', false);
        $response->assertDontSee('https:\/\/legacy.example.com\/red-legacy.jpg', false);
    }

    public function test_home_featured_product_uses_canonical_public_image_with_legacy_fallback(): void
    {
        $category = Category::factory()->create();

        $product = Product::factory()->create([
            'category_id' => $category->id,
            'is_featured' => true,
        ]);

        $color = ProductColor::factory()->create([
            'product_id' => $product->id,
            'name' => 'Negro',
            'position' => 0,
            'image' => 'https://legacy.example.com/home-legacy.jpg',
        ]);

        ProductVariation::factory()->create([
            'product_id' => $product->id,
            'product_color_id' => $color->id,
            'stock' => 2,
            'price' => 1500,
            'size' => 'M',
        ]);

        ProductImage::create([
            'product_color_id' => $color->id,
            'path' => 'products/home-canonical.jpg',
            'position' => 0,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('/storage/products/home-canonical.jpg', false);
        $response->assertDontSee('https://legacy.example.com/home-legacy.jpg');
    }
}
