<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductImage;
use App\Models\ProductVariation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductColorDualWriteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create an admin user to bypass auth middleware if needed
        // Assuming there's a user factory and role logic, we'll act as admin.
        $this->admin = \App\Models\User::factory()->create(['role' => 'admin']);
        $this->actingAs($this->admin);

        Storage::fake('public');
    }

    protected function createCategory()
    {
        return Category::create([
            'name' => 'T-Shirts',
            'slug' => 't-shirts',
            'supports_size' => true,
        ]);
    }

    public function test_product_creation_with_multiple_colors()
    {
        $category = $this->createCategory();

        $response = $this->post(route('admin.products.store'), [
            'name' => 'Basic Tee',
            'category_id' => $category->id,
            'description' => 'A basic tee',
            'variations' => [
                ['color' => 'Red', 'size' => 'S', 'price' => 10, 'stock' => 5],
                ['color' => 'Red', 'size' => 'M', 'price' => 10, 'stock' => 10],
                ['color' => 'Blue', 'size' => 'M', 'price' => 12, 'stock' => 8],
            ],
            'images' => [
                'Red' => [UploadedFile::fake()->image('red1.jpg')],
                'Blue' => [UploadedFile::fake()->image('blue1.jpg'), UploadedFile::fake()->image('blue2.jpg')],
            ],
        ]);

        $response->assertRedirect(route('admin.products.index'));

        $product = Product::where('name', 'Basic Tee')->first();
        $this->assertNotNull($product);

        // Assert ProductColors exist exactly for Red and Blue
        $this->assertEquals(2, $product->colors()->count());

        $redColor = $product->colors()->where('name', 'Red')->first();
        $blueColor = $product->colors()->where('name', 'Blue')->first();
        $this->assertNotNull($redColor);
        $this->assertNotNull($blueColor);

        // Assert Variations
        $this->assertEquals(3, $product->variations()->count());
        $this->assertEquals(15, $product->variations()->where('product_color_id', $redColor->id)->sum('stock'));
        $this->assertEquals(8, $product->variations()->where('product_color_id', $blueColor->id)->sum('stock'));

        // Assert product_color association in variations
        $this->assertEquals($redColor->id, $product->variations()->first()->product_color_id);

        // Assert Images
        $this->assertEquals(3, $product->images()->count());
        $this->assertEquals(1, $redColor->images()->count());
        $this->assertEquals(2, $blueColor->images()->count());

        // Assert images are linked to product color
        $this->assertEquals($blueColor->id, $blueColor->images()->first()->product_color_id);
    }

    public function test_color_renaming_maintains_integrity()
    {
        $category = $this->createCategory();

        // 1. Create product
        $this->post(route('admin.products.store'), [
            'name' => 'Shirt',
            'category_id' => $category->id,
            'variations' => [
                ['color' => 'OldRed', 'size' => 'S', 'price' => 10, 'stock' => 5],
            ],
            'images' => [
                'OldRed' => [UploadedFile::fake()->image('red.jpg')],
            ],
        ]);

        $product = Product::first();
        $color = $product->colors()->first();
        $variation = $product->variations()->first();
        $image = $product->images()->first();

        // 2. Update product (Rename color)
        $this->put(route('admin.products.update', $product), [
            'name' => 'Shirt Updated',
            'category_id' => $category->id,
            'variations' => [
                [
                    'id' => $variation->id,
                    'color_id' => $color->id, // Send the ID to tell the service it's a rename
                    'color' => 'NewRed', // The new string name
                    'size' => 'S',
                    'price' => 10,
                    'stock' => 5,
                ],
            ],
        ]);

        $product->refresh();
        $this->assertEquals(1, $product->colors()->count());

        $updatedColor = $product->colors()->first();
        // ID should be identical
        $this->assertEquals($color->id, $updatedColor->id);
        $this->assertEquals('NewRed', $updatedColor->name);

        $updatedVariation = $product->variations()->first();
        $this->assertEquals($color->id, $updatedVariation->product_color_id);

        // Images remain linked to the same ID!
        $this->assertEquals(1, $updatedColor->images()->count());
    }

    public function test_color_deletion_cascades_properly()
    {
        $category = $this->createCategory();

        // 1. Create product with Red and Blue
        $this->post(route('admin.products.store'), [
            'name' => 'Shirt',
            'category_id' => $category->id,
            'variations' => [
                ['color' => 'Red', 'size' => 'S', 'price' => 10, 'stock' => 5],
                ['color' => 'Blue', 'size' => 'M', 'price' => 10, 'stock' => 5],
            ],
            'images' => [
                'Red' => [UploadedFile::fake()->image('red.jpg')],
                'Blue' => [UploadedFile::fake()->image('blue.jpg')],
            ],
        ]);

        $product = Product::first();
        $this->assertEquals(2, $product->colors()->count());
        $this->assertEquals(2, $product->variations()->count());
        $this->assertEquals(2, $product->images()->count());

        $blueColor = $product->colors()->where('name', 'Blue')->first();

        // 2. Update product keeping only Red
        $redVar = $product->variations()->whereHas('productColor', function ($q) {
            $q->where('name', 'Red');
        })->first();
        $redColor = $product->colors()->where('name', 'Red')->first();

        $this->put(route('admin.products.update', $product), [
            'name' => 'Shirt',
            'category_id' => $category->id,
            'variations' => [
                [
                    'id' => $redVar->id,
                    'color_id' => $redColor->id,
                    'color' => 'Red',
                    'size' => 'S',
                    'price' => 10,
                    'stock' => 5,
                ],
            ],
        ]);

        // 3. Assert Blue is gone, along with its cascade records
        $this->assertEquals(1, ProductColor::count());
        $this->assertNull(ProductColor::find($blueColor->id));

        $this->assertEquals(1, ProductVariation::count());
        $this->assertEquals(0, ProductVariation::whereHas('productColor', function ($q) {
            $q->where('name', 'Blue');
        })->count());

        $this->assertEquals(1, ProductImage::count());
        // Since images cascade on DB level, they are gone
    }

    public function test_total_stock_consistency()
    {
        $category = $this->createCategory();
        $this->post(route('admin.products.store'), [
            'name' => 'Stock Shirt',
            'category_id' => $category->id,
            'variations' => [
                ['color' => 'A', 'size' => 'S', 'price' => 10, 'stock' => 10],
                ['color' => 'B', 'size' => 'M', 'price' => 10, 'stock' => 25],
            ],
        ]);

        $product = Product::first();
        $this->assertEquals(35, $product->variations()->sum('stock'));
        $this->assertEquals(35, $product->total_stock); // Accessor
    }
}
