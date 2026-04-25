<?php

namespace Tests\Feature;

use App\Livewire\CatalogPage;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductVariation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CatalogFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_page_renders_products_for_selected_category()
    {
        $category = Category::create([
            'name' => 'Vestidos',
            'slug' => 'vestidos',
            'supports_size' => true,
            'supports_color' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Vestido Test',
            'slug' => 'vestido-test',
            'description' => 'Test',
            'is_featured' => true,
        ]);

        $color = ProductColor::create([
            'product_id' => $product->id,
            'name' => 'Rojo',
            'position' => 1,
        ]);

        ProductVariation::create([
            'product_id' => $product->id,
            'product_color_id' => $color->id,
            'size' => 'M',
            'stock' => 10,
            'price' => 1000,
        ]);

        Livewire::test(CatalogPage::class)
            ->set('category', $category->slug)
            ->assertSee('Vestido Test')
            ->assertSee('Rojo')
            ->assertSee('M')
            ->assertSee('Novedades');
    }

    public function test_catalog_page_hides_products_from_other_categories()
    {
        $category = Category::create([
            'name' => 'Vestidos',
            'slug' => 'vestidos',
            'supports_size' => true,
            'supports_color' => true,
        ]);

        $otherCategory = Category::create([
            'name' => 'Carteras',
            'slug' => 'carteras',
            'supports_size' => false,
            'supports_color' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Vestido Test',
            'slug' => 'vestido-test',
            'description' => 'Test',
            'is_featured' => true,
        ]);

        $otherProduct = Product::create([
            'category_id' => $otherCategory->id,
            'name' => 'Cartera Test',
            'slug' => 'cartera-test',
            'description' => 'Test',
            'is_featured' => true,
        ]);

        $color = ProductColor::create([
            'product_id' => $product->id,
            'name' => 'Rojo',
            'position' => 1,
        ]);

        $otherColor = ProductColor::create([
            'product_id' => $otherProduct->id,
            'name' => 'Negro',
            'position' => 1,
        ]);

        ProductVariation::create([
            'product_id' => $product->id,
            'product_color_id' => $color->id,
            'size' => 'M',
            'stock' => 10,
            'price' => 1000,
        ]);

        ProductVariation::create([
            'product_id' => $otherProduct->id,
            'product_color_id' => $otherColor->id,
            'size' => 'ÚNICO',
            'stock' => 5,
            'price' => 1000,
        ]);

        Livewire::test(CatalogPage::class)
            ->set('category', $category->slug)
            ->assertSee('Vestido Test')
            ->assertDontSee('Cartera Test');
    }

    public function test_catalog_page_resets_pagination_when_category_changes()
    {
        $targetCategory = Category::create([
            'name' => 'Vestidos',
            'slug' => 'vestidos',
            'supports_size' => true,
            'supports_color' => true,
        ]);

        for ($i = 1; $i <= 13; $i++) {
            $product = Product::create([
                'category_id' => $targetCategory->id,
                'name' => 'Producto '.$i,
                'slug' => 'producto-'.$i,
                'description' => 'Test',
                'is_featured' => true,
            ]);

            $color = ProductColor::create([
                'product_id' => $product->id,
                'name' => 'Color '.$i,
                'position' => 1,
            ]);

            ProductVariation::create([
                'product_id' => $product->id,
                'product_color_id' => $color->id,
                'size' => 'M',
                'stock' => 5,
                'price' => 1000,
            ]);
        }

        Livewire::withQueryParams(['page' => 2])
            ->test(CatalogPage::class)
            ->assertSet('paginators.page', 2)
            ->set('category', $targetCategory->slug)
            ->assertSet('paginators.page', 1);
    }

    public function test_catalog_page_reads_category_slug_from_query_string(): void
    {
        $category = Category::create([
            'name' => 'Vestidos',
            'slug' => 'vestidos',
            'supports_size' => true,
            'supports_color' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Vestido Query String',
            'slug' => 'vestido-query-string',
            'description' => 'Test',
            'is_featured' => true,
        ]);

        $color = ProductColor::create([
            'product_id' => $product->id,
            'name' => 'Negro',
            'position' => 1,
        ]);

        ProductVariation::create([
            'product_id' => $product->id,
            'product_color_id' => $color->id,
            'size' => 'M',
            'stock' => 10,
            'price' => 1000,
        ]);

        Livewire::withQueryParams(['category' => $category->slug])
            ->test(CatalogPage::class)
            ->assertSet('category', $category->slug)
            ->assertSee('Vestido Query String');
    }

    public function test_catalog_page_clears_category_filter_when_all_products_is_selected(): void
    {
        $category = Category::create([
            'name' => 'Vestidos',
            'slug' => 'vestidos',
            'supports_size' => true,
            'supports_color' => true,
        ]);

        $otherCategory = Category::create([
            'name' => 'Carteras',
            'slug' => 'carteras',
            'supports_size' => false,
            'supports_color' => true,
        ]);

        $dress = Product::create([
            'category_id' => $category->id,
            'name' => 'Vestido Limpieza',
            'slug' => 'vestido-limpieza',
            'description' => 'Test',
            'is_featured' => true,
        ]);

        $bag = Product::create([
            'category_id' => $otherCategory->id,
            'name' => 'Cartera Limpieza',
            'slug' => 'cartera-limpieza',
            'description' => 'Test',
            'is_featured' => true,
        ]);

        $dressColor = ProductColor::create([
            'product_id' => $dress->id,
            'name' => 'Rojo',
            'position' => 1,
        ]);

        $bagColor = ProductColor::create([
            'product_id' => $bag->id,
            'name' => 'Negro',
            'position' => 1,
        ]);

        ProductVariation::create([
            'product_id' => $dress->id,
            'product_color_id' => $dressColor->id,
            'size' => 'M',
            'stock' => 10,
            'price' => 1000,
        ]);

        ProductVariation::create([
            'product_id' => $bag->id,
            'product_color_id' => $bagColor->id,
            'size' => 'UNICO',
            'stock' => 10,
            'price' => 1000,
        ]);

        Livewire::test(CatalogPage::class)
            ->set('category', $category->slug)
            ->assertDontSee('Cartera Limpieza')
            ->set('category', '')
            ->assertSet('category', null)
            ->assertSee('Vestido Limpieza')
            ->assertSee('Cartera Limpieza');
    }

    public function test_catalog_page_can_filter_products_that_have_active_offers(): void
    {
        $category = Category::factory()->create();

        $offeredProduct = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Producto Oferta',
        ]);
        $offeredColor = ProductColor::factory()->create(['product_id' => $offeredProduct->id]);
        ProductVariation::factory()->create([
            'product_id' => $offeredProduct->id,
            'product_color_id' => $offeredColor->id,
            'stock' => 5,
            'price' => 1000,
            'sale_price' => 800,
        ]);

        $regularProduct = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Producto Regular',
        ]);
        $regularColor = ProductColor::factory()->create(['product_id' => $regularProduct->id]);
        ProductVariation::factory()->create([
            'product_id' => $regularProduct->id,
            'product_color_id' => $regularColor->id,
            'stock' => 5,
            'price' => 1200,
            'sale_price' => null,
        ]);

        Livewire::test(CatalogPage::class)
            ->set('offerOnly', true)
            ->assertSee('Producto Oferta')
            ->assertDontSee('Producto Regular');
    }
}
