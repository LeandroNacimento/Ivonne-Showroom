<?php

namespace Tests\Feature\Admin;

use App\Livewire\CatalogPage;
use App\Livewire\ProductPage;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductVariation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class ProductSizeTypeTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($this->admin);
    }

    public function test_it_persists_numeric_size_type_and_sorts_sizes_in_catalog_and_pdp(): void
    {
        $category = $this->createCategory();

        $response = $this->post(route('admin.products.store'), [
            'name' => 'Jean Numerico',
            'category_id' => $category->id,
            'description' => 'Test numerico',
            'size_type' => 'numeric_36_48',
            'variations' => [
                ['color' => 'Negro', 'size' => '44', 'price' => 1000, 'stock' => 3],
                ['color' => 'Negro', 'size' => '36', 'price' => 1000, 'stock' => 3],
                ['color' => 'Negro', 'size' => '40', 'price' => 1000, 'stock' => 3],
            ],
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $product = Product::with('variations')->where('name', 'Jean Numerico')->firstOrFail();

        $this->assertSame('numeric_36_48', $product->size_type);
        $this->assertSame(['36', '40', '44'], $product->available_sizes);

        Livewire::test(CatalogPage::class)
            ->assertSee('Jean Numerico')
            ->assertSee('Disponible en 36 - 40 - 44');

        Livewire::test(ProductPage::class, ['slug' => $product->slug])
            ->assertSeeInOrder(['36', '40', '44']);
    }

    public function test_it_persists_alpha_size_type_and_sorts_sizes_in_catalog_and_pdp(): void
    {
        $category = $this->createCategory();

        $response = $this->post(route('admin.products.store'), [
            'name' => 'Blazer Alpha',
            'category_id' => $category->id,
            'description' => 'Test alpha',
            'size_type' => 'alpha',
            'variations' => [
                ['color' => 'Negro', 'size' => 'L', 'price' => 1000, 'stock' => 3],
                ['color' => 'Negro', 'size' => 'XS', 'price' => 1000, 'stock' => 3],
                ['color' => 'Negro', 'size' => 'M', 'price' => 1000, 'stock' => 3],
            ],
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $product = Product::with('variations')->where('name', 'Blazer Alpha')->firstOrFail();

        $this->assertSame('alpha', $product->size_type);
        $this->assertSame(['XS', 'M', 'L'], $product->available_sizes);

        Livewire::test(CatalogPage::class)
            ->assertSee('Blazer Alpha')
            ->assertSee('Disponible en XS - M - L');

        Livewire::test(ProductPage::class, ['slug' => $product->slug])
            ->assertSeeInOrder(['XS', 'M', 'L']);
    }

    public function test_it_persists_numeric_1_5_size_type_and_sorts_sizes_in_catalog_and_pdp(): void
    {
        $category = $this->createCategory();

        $response = $this->post(route('admin.products.store'), [
            'name' => 'Pantalon Escalado',
            'category_id' => $category->id,
            'description' => 'Test numeric 1 5',
            'size_type' => 'numeric_1_5',
            'variations' => [
                ['color' => 'Negro', 'size' => '5', 'price' => 1000, 'stock' => 3],
                ['color' => 'Negro', 'size' => '1', 'price' => 1000, 'stock' => 3],
                ['color' => 'Negro', 'size' => '3', 'price' => 1000, 'stock' => 3],
            ],
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $product = Product::with('variations')->where('name', 'Pantalon Escalado')->firstOrFail();

        $this->assertSame('numeric_1_5', $product->size_type);
        $this->assertSame(['1', '3', '5'], $product->available_sizes);

        Livewire::test(CatalogPage::class)
            ->assertSee('Pantalon Escalado')
            ->assertSee('Disponible en 1 - 3 - 5');

        Livewire::test(ProductPage::class, ['slug' => $product->slug])
            ->assertSeeInOrder(['1', '3', '5']);
    }

    public function test_it_rejects_invalid_sizes_for_the_selected_size_type(): void
    {
        $category = $this->createCategory();

        $response = $this->from(route('admin.products.create'))->post(route('admin.products.store'), [
            'name' => 'Producto Invalido',
            'category_id' => $category->id,
            'description' => 'Test',
            'size_type' => 'numeric_1_5',
            'variations' => [
                ['color' => 'Negro', 'size' => 'M', 'price' => 1000, 'stock' => 3],
            ],
        ]);

        $response->assertRedirect(route('admin.products.create'));
        $response->assertSessionHasErrors(['variations.0.size']);
        $this->assertDatabaseMissing('products', ['name' => 'Producto Invalido']);
    }

    public function test_it_persists_one_size_canonically_and_hides_the_size_selector_in_pdp(): void
    {
        $category = $this->createCategory();

        $response = $this->post(route('admin.products.store'), [
            'name' => 'Cartera Unica',
            'category_id' => $category->id,
            'description' => 'Test one size',
            'size_type' => 'one_size',
            'variations' => [
                ['color' => 'Negro', 'size' => 'Único', 'price' => 1000, 'stock' => 3],
            ],
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $product = Product::with('variations')->where('name', 'Cartera Unica')->firstOrFail();

        $this->assertSame('one_size', $product->size_type);
        $this->assertFalse($product->has_sizes);
        $this->assertSame('Talle único', $product->availability_label);
        $this->assertSame(['Único'], $product->available_sizes);
        $this->assertSame(
            [Product::ONE_SIZE_VALUE],
            $product->variations->pluck('size')->unique()->values()->all()
        );

        $response = $this->get(route('product.show', $product->slug));

        $response->assertOk();
        $response->assertDontSee('Seleccioná tu talle:');
    }

    public function test_it_rejects_invalid_size_type_changes_on_update(): void
    {
        $category = $this->createCategory();

        $product = Product::create([
            'name' => 'Producto Editable',
            'slug' => 'producto-editable',
            'description' => 'Test',
            'category_id' => $category->id,
            'size_type' => 'alpha',
            'is_featured' => false,
        ]);

        $color = ProductColor::create([
            'product_id' => $product->id,
            'name' => 'Negro',
            'position' => 0,
        ]);

        $variation = ProductVariation::create([
            'product_id' => $product->id,
            'product_color_id' => $color->id,
            'size' => 'M',
            'price' => 1000,
            'stock' => 3,
        ]);

        $response = $this->from(route('admin.products.edit', $product))->put(route('admin.products.update', $product), [
            'name' => $product->name,
            'category_id' => $category->id,
            'description' => $product->description,
            'size_type' => 'numeric_1_5',
            'variations' => [
                [
                    'id' => $variation->id,
                    'color_id' => $color->id,
                    'color' => 'Negro',
                    'size' => 'M',
                    'price' => 1000,
                    'stock' => 3,
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.products.edit', $product));
        $response->assertSessionHasErrors(['variations.0.size']);

        $product->refresh();
        $this->assertSame('alpha', $product->size_type);
    }

    public function test_migration_backfills_legacy_products_using_variations_before_category_flags(): void
    {
        $categoryWithoutSizes = $this->createCategory('Accesorios Legacy', 'accesorios-legacy', false);
        $categoryWithSizes = $this->createCategory('Ropa Legacy', 'ropa-legacy', true);

        $numericProduct = Product::create([
            'name' => 'Producto Legacy Numerico',
            'slug' => 'producto-legacy-numerico',
            'description' => 'Test',
            'category_id' => $categoryWithoutSizes->id,
            'size_type' => Product::DEFAULT_SIZE_TYPE,
            'is_featured' => false,
        ]);

        $numericColor = ProductColor::create([
            'product_id' => $numericProduct->id,
            'name' => 'Negro',
            'position' => 0,
        ]);

        ProductVariation::create([
            'product_id' => $numericProduct->id,
            'product_color_id' => $numericColor->id,
            'size' => '1',
            'price' => 1000,
            'stock' => 3,
        ]);

        ProductVariation::create([
            'product_id' => $numericProduct->id,
            'product_color_id' => $numericColor->id,
            'size' => '5',
            'price' => 1000,
            'stock' => 3,
        ]);

        $oneSizeProduct = Product::create([
            'name' => 'Producto Legacy Unico',
            'slug' => 'producto-legacy-unico',
            'description' => 'Test',
            'category_id' => $categoryWithoutSizes->id,
            'size_type' => Product::DEFAULT_SIZE_TYPE,
            'is_featured' => false,
        ]);

        $oneSizeColor = ProductColor::create([
            'product_id' => $oneSizeProduct->id,
            'name' => 'Negro',
            'position' => 0,
        ]);

        $oneSizeVariation = ProductVariation::create([
            'product_id' => $oneSizeProduct->id,
            'product_color_id' => $oneSizeColor->id,
            'size' => Product::ONE_SIZE_VALUE,
            'price' => 1000,
            'stock' => 3,
        ]);

        $emptyProduct = Product::create([
            'name' => 'Producto Legacy Vacio',
            'slug' => 'producto-legacy-vacio',
            'description' => 'Test',
            'category_id' => $categoryWithSizes->id,
            'size_type' => Product::DEFAULT_SIZE_TYPE,
            'is_featured' => false,
        ]);

        DB::table('product_variations')
            ->where('id', $oneSizeVariation->id)
            ->update(['size' => 'Único']);

        Schema::table('products', function ($table) {
            $table->dropColumn('size_type');
        });

        $migration = require base_path('database/migrations/2026_04_08_000001_add_size_type_to_products_table.php');
        $migration->up();

        $this->assertSame('numeric_1_5', DB::table('products')->where('id', $numericProduct->id)->value('size_type'));
        $this->assertSame('one_size', DB::table('products')->where('id', $oneSizeProduct->id)->value('size_type'));
        $this->assertSame('alpha', DB::table('products')->where('id', $emptyProduct->id)->value('size_type'));
        $this->assertSame(
            Product::ONE_SIZE_VALUE,
            DB::table('product_variations')->where('id', $oneSizeVariation->id)->value('size')
        );
    }

    private function createCategory(
        string $name = 'Vestidos',
        string $slug = 'vestidos',
        bool $supportsSize = true,
    ): Category
    {
        return Category::create([
            'name' => $name,
            'slug' => $slug,
            'supports_size' => $supportsSize,
            'supports_color' => true,
        ]);
    }
}
