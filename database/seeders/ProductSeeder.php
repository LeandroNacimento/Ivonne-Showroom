<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run()
    {
        // 1. Create Categories
        $categories = [
            'Vestidos' => [
                'Vestido Floral Maxi', 'Vestido Noche Elegante', 'Vestido Cóctel Rojo', 'Vestido Playa Bohemio', 'Vestido Lino Blanco'
            ],
            'Blusas' => [
                'Blusa Seda Blanca', 'Blusa Satén Negra', 'Camisa Lino Beige', 'Top Encaje', 'Blusa Estampada'
            ],
            'Pantalones' => [
                'Jean Mom Fit', 'Pantalón Sastre Negro', 'Short Denim Desgastado', 'Pantalón Lino Ancho', 'Leggings Eco Cuero'
            ],
            'Accesorios' => [
                'Cinturón Cuero', 'Pañuelo Seda', 'Collar Dorado', 'Cartera Bandolera', 'Sombrero Playa'
            ],
            'Carteras' => [
                'Cartera Tote Negra', 'Bandolera Cuero Suela', 'Clutch Fiesta Plateado', 'Mochila Urbana', 'Billetera Roja'
            ],
            'Tops' => [
                'Top Crop Básico', 'Top Asimétrico', 'Musculosa Algodón', 'Top Lentejuelas', 'Corset Satén'
            ],
            'Abrigos' => [
                'Blazer Oversize', 'Chaqueta Cuero Ecológico', 'Cardigan Punto Grueso', 'Tapado Lana Camel', 'Campera Jean Clásica'
            ]
        ];

        foreach ($categories as $categoryName => $productsList) {
            $categorySlug = Str::slug($categoryName);
            $category = Category::updateOrCreate(
                ['slug' => $categorySlug],
                ['name' => $categoryName]
            );

            // 2. Create Products for each Category
            foreach ($productsList as $productName) {
                $productSlug = Str::slug($productName);
                
                $product = Product::updateOrCreate(
                    ['slug' => $productSlug],
                    [
                        'name' => $productName,
                        'category_id' => $category->id,
                        'description' => "Descripción detallada del producto $productName. Ideal para cualquier ocasión.",
                        'price' => rand(15000, 85000), // Random price between 15k and 85k
                        'is_featured' => rand(0, 1)
                    ]
                );

                // 3. Create Variations (Sizes) if none exist
                if ($product->variations()->count() == 0) {
                    $sizes = ['XS', 'S', 'M', 'L', 'XL'];
                    foreach ($sizes as $size) {
                        ProductVariation::create([
                            'product_id' => $product->id,
                            'size' => $size,
                            'color' => 'Único', // Default color
                            'stock' => rand(0, 20) // Random stock including 0 for testing
                        ]);
                    }
                }
            }
        }
    }
}
