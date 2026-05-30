<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductImageUploadBugTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function getAdminUser()
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_caso_1_crear_producto_color_negro_con_imagenes()
    {
        $category = Category::factory()->create();
        $admin = $this->getAdminUser();

        $uuid = Str::uuid()->toString();

        $file1 = UploadedFile::fake()->image('foto1.jpg');
        $file2 = UploadedFile::fake()->image('foto2.jpg');

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Producto 1',
            'category_id' => $category->id,
            'size_type' => 'alpha',
            'variations' => [
                [
                    'id' => '',
                    'color_id' => '',
                    'color' => 'Negro',
                    'size' => 'M',
                    'price' => 1000,
                    'stock' => 10,
                    'uuid' => $uuid,
                ],
            ],
            'images' => [
                $uuid => [$file1, $file2],
            ],
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success');

        $product = Product::where('name', 'Producto 1')->first();
        $this->assertNotNull($product);
        $this->assertCount(1, $product->colors);
        $this->assertEquals('Negro', $product->colors->first()->name);

        $images = ProductImage::where('product_color_id', $product->colors->first()->id)->get();
        $this->assertCount(2, $images);
    }

    public function test_caso_2_renombrar_color_rosado_a_bordo_race_condition()
    {
        // En el browser, si seleccionan imagenes para "Rosado" y luego renombran a "Bordo",
        // el usuario presiona "Guardar" inmediatamente (Race condition).
        // Se envia el UUID dentro del payload sincrono de variations.
        $category = Category::factory()->create();
        $admin = $this->getAdminUser();

        $uuid = Str::uuid()->toString();
        $file1 = UploadedFile::fake()->image('rosado.jpg');

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Producto 2',
            'category_id' => $category->id,
            'size_type' => 'alpha',
            'variations' => [
                [
                    'id' => '',
                    'color_id' => '',
                    'color' => 'Bordó', // Color was renamed instantly via Alpine
                    'size' => 'M',
                    'price' => 1000,
                    'stock' => 10,
                    'uuid' => $uuid, // The same UUID that matches the image file input
                ],
            ],
            'images' => [
                $uuid => [$file1],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $product = Product::where('name', 'Producto 2')->first();
        $this->assertEquals('Bordó', $product->colors->first()->name);
        $this->assertCount(1, ProductImage::where('product_color_id', $product->colors->first()->id)->get());
    }

    public function test_caso_3_eliminar_color_azul()
    {
        $category = Category::factory()->create();
        $admin = $this->getAdminUser();

        $uuidNegro = Str::uuid()->toString();

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Producto 3',
            'category_id' => $category->id,
            'size_type' => 'alpha',
            'variations' => [
                [
                    'id' => '',
                    'color_id' => '',
                    'color' => 'Negro',
                    'size' => 'M',
                    'price' => 1000,
                    'stock' => 10,
                    'uuid' => $uuidNegro,
                ],
            ],
            'images' => [
                $uuidNegro => [UploadedFile::fake()->image('negro.jpg')],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $product = Product::where('name', 'Producto 3')->first();
        $this->assertCount(1, $product->colors);
        $this->assertCount(1, ProductImage::all());
    }

    public function test_caso_4_renombrar_todos_los_colores_inmediatamente()
    {
        $category = Category::factory()->create();
        $admin = $this->getAdminUser();

        $uuid1 = Str::uuid()->toString();
        $uuid2 = Str::uuid()->toString();
        $uuid3 = Str::uuid()->toString();

        $response = $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Producto 4',
            'category_id' => $category->id,
            'size_type' => 'alpha',
            'variations' => [
                ['id' => '', 'color_id' => '', 'color' => 'A_Renombrado', 'size' => 'M', 'price' => 10, 'stock' => 1, 'uuid' => $uuid1],
                ['id' => '', 'color_id' => '', 'color' => 'B_Renombrado', 'size' => 'M', 'price' => 10, 'stock' => 1, 'uuid' => $uuid2],
                ['id' => '', 'color_id' => '', 'color' => 'C_Renombrado', 'size' => 'M', 'price' => 10, 'stock' => 1, 'uuid' => $uuid3],
            ],
            'images' => [
                $uuid1 => [UploadedFile::fake()->image('1.jpg')],
                $uuid2 => [UploadedFile::fake()->image('2.jpg')],
                $uuid3 => [UploadedFile::fake()->image('3.jpg')],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $product = Product::where('name', 'Producto 4')->first();
        $this->assertCount(3, $product->colors);
        $this->assertCount(3, ProductImage::all());
    }

    public function test_caso_5_editar_producto_existente_agregar_color_y_renombrar()
    {
        $category = Category::factory()->create();
        $admin = $this->getAdminUser();

        // 1. Crear producto con color "Original"
        $uuidOriginal = Str::uuid()->toString();
        $this->actingAs($admin)->post(route('admin.products.store'), [
            'name' => 'Producto 5',
            'category_id' => $category->id,
            'size_type' => 'alpha',
            'variations' => [
                ['id' => '', 'color_id' => '', 'color' => 'Original', 'size' => 'M', 'price' => 10, 'stock' => 1, 'uuid' => $uuidOriginal],
            ],
            'images' => [
                $uuidOriginal => [UploadedFile::fake()->image('original.jpg')],
            ],
        ]);

        $product = Product::where('name', 'Producto 5')->first();
        $this->assertCount(1, $product->colors);

        $originalColor = $product->colors->first();
        $originalVariation = $originalColor->variations->first();

        // 2. Editar producto, agregar color "Nuevo" y renombrar el original
        $uuidNuevo = Str::uuid()->toString();
        // Durante edicion el uuid se inyecta desde la vista en el mismo form submission

        $response = $this->actingAs($admin)->put(route('admin.products.update', $product), [
            'name' => 'Producto 5',
            'category_id' => $category->id,
            'size_type' => 'alpha',
            'variations' => [
                // Renombrar color original (uuidOriginal fue reinyectado por el form de forma dinamica al hidratar)
                ['id' => $originalVariation->id, 'color_id' => $originalColor->id, 'color' => 'OriginalRenombrado', 'size' => 'M', 'price' => 10, 'stock' => 1, 'uuid' => $uuidOriginal],
                // Agregar color nuevo
                ['id' => '', 'color_id' => '', 'color' => 'Nuevo', 'size' => 'M', 'price' => 10, 'stock' => 1, 'uuid' => $uuidNuevo],
            ],
            'images' => [
                $uuidNuevo => [UploadedFile::fake()->image('nuevo.jpg')],
            ],
        ]);

        $response->assertSessionHasNoErrors();
        $product->refresh();
        $this->assertCount(2, $product->colors);
        $this->assertEquals('OriginalRenombrado', $product->colors->where('id', $originalColor->id)->first()->name);
        $this->assertCount(2, ProductImage::all()); // 1 de Original + 1 de Nuevo
    }
}
