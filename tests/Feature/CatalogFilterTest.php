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
            ->set('categoryId', $category->id)
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
            ->set('categoryId', $category->id)
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
            ->set('categoryId', $targetCategory->id)
            ->assertSet('paginators.page', 1);
    }
}
