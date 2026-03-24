<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductColor;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVariationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'product_color_id' => ProductColor::factory(),
            'size' => fake()->randomElement(['S', 'M', 'L', 'XL']),
            'stock' => fake()->numberBetween(1, 10),
            'price' => fake()->randomFloat(2, 100, 10000),
            'sku' => null,
        ];
    }
}
