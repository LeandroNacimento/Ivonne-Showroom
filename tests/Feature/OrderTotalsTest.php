<?php

use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\OrderService;

uses(RefreshDatabase::class);

it('calcula el total del pedido en backend ignorando valores del frontend', function () {
    $client = Client::factory()->create();
    $product = Product::factory()->create();
    $variation = ProductVariation::factory()->create([
        'product_id' => $product->id,
        'stock' => 10,
        'price' => 1500, // True price
    ]);

    $orderData = [
        'client_id' => $client->id,
        'date' => now()->format('Y-m-d'),
        'status' => Order::STATUS_PENDING,
        'payment_method' => 'cash',
        'delivery_type' => 'showroom',
        'items' => [
            [
                'product_id' => $product->id,
                'variation_id' => $variation->id,
                'quantity' => 2,
                'unit_price' => 100, // Fake price from frontend trying to cheat
            ]
        ]
    ];

    $order = app(OrderService::class)->create($orderData);

    // True total should be 1500 * 2 = 3000
    expect($order->total)->toBe(3000.0);
});
