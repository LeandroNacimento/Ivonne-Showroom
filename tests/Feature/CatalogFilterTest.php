<?php

namespace Tests\Feature;

use App\Livewire\Public\CatalogPage;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CatalogFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_filters_show_sizes_and_colors_for_supported_category()
    {
        // Category supports both
        $category = Category::create([
            'name' => 'Vestidos',
            'slug' => 'vestidos',
            'supports_size' => true,
            'supports_color' => true
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Vestido Test',
            'slug' => 'vestido-test',
            'description' => 'Test',
            'price' => 1000,
            'is_featured' => true
        ]);

        ProductVariation::create(['product_id' => $product->id, 'size' => 'M', 'color' => 'Rojo', 'stock' => 10]);

        Livewire::withQueryParams(['category' => 'vestidos'])
            ->test(CatalogPage::class)
            ->assertSee('Talles')
            ->assertSee('Colores')
            ->assertSee('M')
            ->assertSee('Rojo');
    }

    public function test_filters_hide_sizes_for_unsupported_category()
    {
        // Category supports only color
        $category = Category::create([
            'name' => 'Carteras',
            'slug' => 'carteras',
            'supports_size' => false,
            'supports_color' => true
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Cartera Test',
            'slug' => 'cartera-test',
            'description' => 'Test',
            'price' => 1000,
            'is_featured' => true
        ]);

        ProductVariation::create(['product_id' => $product->id, 'size' => 'Unico', 'color' => 'Negro', 'stock' => 5]);

        Livewire::withQueryParams(['category' => 'carteras'])
            ->test(CatalogPage::class)
            ->assertDontSee('Talles') // Should not show Size filter section
            ->assertSee('Colores')
            ->assertSee('Negro');
    }

    public function test_filters_reset_when_switching_to_unsupported_category()
    {
        $category = Category::create([
            'name' => 'Carteras',
            'slug' => 'carteras',
            'supports_size' => false,
            'supports_color' => true
        ]);

        // Simulate user having a size selected from previous navigation
        Livewire::withQueryParams(['category' => 'carteras', 'sizes' => ['M']])
            ->test(CatalogPage::class)
            ->assertSet('sizes', []); // Should have been reset to empty array
    }
}
