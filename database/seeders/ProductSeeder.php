<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Paleta de colores disponible para asignar a productos.
     */
    private array $colorPool = [
        'Negro',
        'Blanco',
        'Rosa',
        'Beige',
        'Rojo',
        'Azul',
        'Verde',
        'Camel',
        'Gris',
        'Bordo',
        'Celeste',
        'Lila',
        'Nude',
        'Suela',
        'Plateado',
    ];

    /**
     * Talles disponibles para ropa.
     */
    private array $sizePool = ['XS', 'S', 'M', 'L', 'XL'];

    public function run(): void
    {
        // ── Categories with product definitions ──────────────────
        // supports_size = true
        $clothingCategories = [
            'Vestidos' => [
                ['name' => 'Vestido Floral Maxi', 'colors' => ['Rosa', 'Celeste'], 'sizes' => ['S', 'M', 'L']],
                ['name' => 'Vestido Noche Elegante', 'colors' => ['Negro', 'Bordo'], 'sizes' => ['S', 'M', 'L', 'XL']],
                ['name' => 'Vestido Cóctel Rojo', 'colors' => ['Rojo'], 'sizes' => ['XS', 'S', 'M']],
                ['name' => 'Vestido Playa Bohemio', 'colors' => ['Beige', 'Blanco'], 'sizes' => ['M', 'L']],
                ['name' => 'Vestido Lino Blanco', 'colors' => ['Blanco', 'Nude'], 'sizes' => ['S', 'M', 'L']],
            ],
            'Blusas' => [
                ['name' => 'Blusa Seda Blanca', 'colors' => ['Blanco'], 'sizes' => ['S', 'M', 'L']],
                ['name' => 'Blusa Satén Negra', 'colors' => ['Negro', 'Bordo'], 'sizes' => ['M', 'L', 'XL']],
                ['name' => 'Camisa Lino Beige', 'colors' => ['Beige', 'Blanco', 'Rosa'], 'sizes' => ['S', 'M']],
                ['name' => 'Top Encaje', 'colors' => ['Negro', 'Nude'], 'sizes' => ['XS', 'S', 'M']],
                ['name' => 'Blusa Estampada', 'colors' => ['Azul', 'Verde'], 'sizes' => ['S', 'M', 'L']],
            ],
            'Pantalones' => [
                ['name' => 'Jean Mom Fit', 'colors' => ['Celeste', 'Azul'], 'sizes' => ['S', 'M', 'L', 'XL']],
                ['name' => 'Pantalón Sastre Negro', 'colors' => ['Negro'], 'sizes' => ['S', 'M', 'L']],
                ['name' => 'Short Denim Desgastado', 'colors' => ['Celeste'], 'sizes' => ['XS', 'S', 'M']],
                ['name' => 'Pantalón Lino Ancho', 'colors' => ['Beige', 'Blanco'], 'sizes' => ['M', 'L']],
                ['name' => 'Leggings Eco Cuero', 'colors' => ['Negro', 'Bordo'], 'sizes' => ['S', 'M', 'L']],
            ],
            'Tops' => [
                ['name' => 'Top Crop Básico', 'colors' => ['Negro', 'Blanco', 'Rosa'], 'sizes' => ['XS', 'S', 'M']],
                ['name' => 'Top Asimétrico', 'colors' => ['Gris', 'Negro'], 'sizes' => ['S', 'M']],
                ['name' => 'Musculosa Algodón', 'colors' => ['Blanco', 'Beige'], 'sizes' => ['S', 'M', 'L', 'XL']],
                ['name' => 'Top Lentejuelas', 'colors' => ['Plateado', 'Negro'], 'sizes' => ['XS', 'S']],
                ['name' => 'Corset Satén', 'colors' => ['Negro', 'Rosa'], 'sizes' => ['S', 'M']],
            ],
            'Abrigos' => [
                ['name' => 'Blazer Oversize', 'colors' => ['Negro', 'Camel'], 'sizes' => ['M', 'L', 'XL']],
                ['name' => 'Chaqueta Cuero Ecológico', 'colors' => ['Negro'], 'sizes' => ['S', 'M', 'L']],
                ['name' => 'Cardigan Punto Grueso', 'colors' => ['Beige', 'Gris', 'Rosa'], 'sizes' => ['M', 'L']],
                ['name' => 'Tapado Lana Camel', 'colors' => ['Camel'], 'sizes' => ['S', 'M', 'L']],
                ['name' => 'Campera Jean Clásica', 'colors' => ['Celeste', 'Azul'], 'sizes' => ['S', 'M', 'L', 'XL']],
            ],
        ];

        // supports_size = false → size = 'Único'
        $accessoryCategories = [
            'Accesorios' => [
                ['name' => 'Cinturón Cuero', 'colors' => ['Negro', 'Suela']],
                ['name' => 'Pañuelo Seda', 'colors' => ['Rosa', 'Beige', 'Azul']],
                ['name' => 'Collar Dorado', 'colors' => ['Dorado']],
                ['name' => 'Cartera Bandolera', 'colors' => ['Negro', 'Camel']],
                ['name' => 'Sombrero Playa', 'colors' => ['Beige']],
            ],
            'Carteras' => [
                ['name' => 'Cartera Tote Negra', 'colors' => ['Negro', 'Gris']],
                ['name' => 'Bandolera Cuero Suela', 'colors' => ['Suela', 'Camel']],
                ['name' => 'Clutch Fiesta Plateado', 'colors' => ['Plateado', 'Negro']],
                ['name' => 'Mochila Urbana', 'colors' => ['Negro']],
                ['name' => 'Billetera Roja', 'colors' => ['Rojo', 'Rosa', 'Negro']],
            ],
        ];

        // ── Seed clothing (with sizes) ───────────────────────────
        foreach ($clothingCategories as $catName => $products) {
            $category = Category::updateOrCreate(
                ['slug' => Str::slug($catName)],
                ['name' => $catName]
            );

            foreach ($products as $def) {
                $product = Product::updateOrCreate(
                    ['slug' => Str::slug($def['name'])],
                    [
                        'name' => $def['name'],
                        'category_id' => $category->id,
                        'description' => "Descripción detallada de {$def['name']}. Ideal para cualquier ocasión.",
                        'is_featured' => rand(0, 1),
                    ]
                );

                if ($product->variations()->count() === 0) {
                    $basePrice = rand(15000, 85000);

                    foreach ($def['colors'] as $color) {
                        foreach ($def['sizes'] as $size) {
                            // Slight price variation per color (±5%)
                            $price = intval($basePrice * (1 + rand(-5, 5) / 100));

                            $productColor = \App\Models\ProductColor::firstOrCreate(
                                ['product_id' => $product->id, 'name' => $color],
                                ['position' => 0]
                            );

                            ProductVariation::create([
                                'product_id' => $product->id,
                                'product_color_id' => $productColor->id,
                                'size' => $size,
                                'price' => $price,
                                'stock' => rand(0, 20),
                                'sku' => null,
                            ]);
                        }
                    }
                }
            }
        }

        // ── Seed accessories (no sizes → 'Único') ────────────────
        foreach ($accessoryCategories as $catName => $products) {
            $category = Category::updateOrCreate(
                ['slug' => Str::slug($catName)],
                ['name' => $catName]
            );

            foreach ($products as $def) {
                $product = Product::updateOrCreate(
                    ['slug' => Str::slug($def['name'])],
                    [
                        'name' => $def['name'],
                        'category_id' => $category->id,
                        'description' => "Descripción detallada de {$def['name']}. Complemento ideal.",
                        'is_featured' => rand(0, 1),
                    ]
                );

                if ($product->variations()->count() === 0) {
                    $basePrice = rand(8000, 45000);

                    foreach ($def['colors'] as $color) {
                        $price = intval($basePrice * (1 + rand(-5, 5) / 100));

                        $productColor = \App\Models\ProductColor::firstOrCreate(
                            ['product_id' => $product->id, 'name' => $color],
                            ['position' => 0]
                        );

                        ProductVariation::create([
                            'product_id' => $product->id,
                            'product_color_id' => $productColor->id,
                            'size' => 'Único',
                            'price' => $price,
                            'stock' => rand(1, 15),
                            'sku' => null,
                        ]);
                    }
                }
            }
        }
    }
}
