<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'slug' => fake()->slug(),
            'description' => fake()->sentence(),
            'category_id' => Category::factory(),
            'is_featured' => false,
        ];
    }
}
