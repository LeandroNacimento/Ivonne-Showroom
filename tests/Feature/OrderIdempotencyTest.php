<?php

use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('no duplica pedidos ante multiples envios simultaneos', function () {
    $client = Client::factory()->create();
    $product = Product::factory()->create();
    $variation = ProductVariation::factory()->create([
        'product_id' => $product->id,
        'stock' => 5,
        'price' => 1000,
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
            ],
        ],
        // Mock idempotency key from header or payload
        'idempotency_key' => 'unique-tx-12345',
    ];

    $service = app(OrderService::class);

    // First request
    $order1 = $service->create($orderData);

    // Second simultaneous (or duplicate) request
    $order2 = null;
    try {
        // If your idempotency logic is implemented, it should return the same order or throw an error.
        $order2 = $service->create($orderData);
    } catch (\Exception $e) {
        // Duplicate exception expected
    }

    // Verify only 1 order exists for this client today depending on logic.
    expect(Order::count())->toBe(1);

    // Optionally:
    if ($order2) {
        expect($order1->id)->toBe($order2->id);
    }
})->skip('Depende de la implementacion del token de idempotencia en la API o DB (Unique Constraint)');
