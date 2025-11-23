<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create Categories
        $categories = [
            ['name' => 'Vestidos', 'slug' => 'vestidos'],
            ['name' => 'Blusas', 'slug' => 'blusas'],
            ['name' => 'Pantalones', 'slug' => 'pantalones'],
            ['name' => 'Accesorios', 'slug' => 'accesorios'],
        ];

        foreach ($categories as $cat) {
            Category::create($cat);
        }

        // Create Products
        $vestidos = Category::where('slug', 'vestidos')->first();
        
        $product1 = Product::create([
            'category_id' => $vestidos->id,
            'name' => 'Vestido Floral Primavera',
            'slug' => 'vestido-floral-primavera',
            'description' => 'Hermoso vestido con estampado floral, ideal para eventos de día. Tela fresca y cómoda.',
            'price' => 15000.00,
            'is_featured' => true,
        ]);

        ProductVariation::create(['product_id' => $product1->id, 'color' => 'Rosa', 'size' => 'S', 'stock' => 5]);
        ProductVariation::create(['product_id' => $product1->id, 'color' => 'Rosa', 'size' => 'M', 'stock' => 3]);
        ProductVariation::create(['product_id' => $product1->id, 'color' => 'Azul', 'size' => 'S', 'stock' => 2]);

        $product2 = Product::create([
            'category_id' => $vestidos->id,
            'name' => 'Vestido de Noche Elegante',
            'slug' => 'vestido-noche-elegante',
            'description' => 'Vestido largo negro, corte sirena. Perfecto para fiestas y galas.',
            'price' => 25000.00,
            'is_featured' => true,
        ]);

        ProductVariation::create(['product_id' => $product2->id, 'color' => 'Negro', 'size' => 'M', 'stock' => 4]);
        ProductVariation::create(['product_id' => $product2->id, 'color' => 'Negro', 'size' => 'L', 'stock' => 2]);

        $blusas = Category::where('slug', 'blusas')->first();

        $product3 = Product::create([
            'category_id' => $blusas->id,
            'name' => 'Blusa de Seda',
            'slug' => 'blusa-seda',
            'description' => 'Blusa suave de seda con botones perlados.',
            'price' => 8500.00,
            'is_featured' => false,
        ]);

        ProductVariation::create(['product_id' => $product3->id, 'color' => 'Blanco', 'size' => 'S', 'stock' => 10]);
        ProductVariation::create(['product_id' => $product3->id, 'color' => 'Beige', 'size' => 'M', 'stock' => 8]);
    }
}
