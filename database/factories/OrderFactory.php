<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'date' => fake()->date(),
            'status' => 'pending',
            'payment_method' => 'cash',
            'delivery_type' => 'showroom',
            'shipping_cost' => 0,
            'total' => fake()->randomFloat(2, 100, 10000),
        ];
    }
}
