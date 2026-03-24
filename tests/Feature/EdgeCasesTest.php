<?php

use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductVariation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\OrderService;

uses(RefreshDatabase::class);

it('rechaza cantidades negativas', function () {
    $client = Client::factory()->create();
    $product = Product::factory()->create();
    $color = ProductColor::factory()->create(['product_id' => $product->id]);
    $variation = ProductVariation::factory()->create([
        'product_id' => $product->id,
        'product_color_id' => $color->id,
        'stock' => 5,
        'price' => 1000,
        'size' => 'M',
    ]);

    $orderData = [
        'client_id' => $client->id,
        'date' => now()->format('Y-m-d'),
        'status' => Order::STATUS_RESERVED,
        'payment_method' => 'cash',
        'delivery_type' => 'showroom',
        'items' => [
            [
                'product_id' => $product->id,
                'variation_id' => $variation->id,
                'quantity' => -5,
            ]
        ]
    ];

    // Negative quantity should cause stock to go below allowed, or be rejected
    expect(fn () => app(OrderService::class)->create($orderData))
        ->toThrow(\Exception::class);

    expect(Order::count())->toBe(0);
    expect($variation->fresh()->stock)->toBe(5);
});

it('rechaza cantidad cero', function () {
    $client = Client::factory()->create();
    $product = Product::factory()->create();
    $color = ProductColor::factory()->create(['product_id' => $product->id]);
    $variation = ProductVariation::factory()->create([
        'product_id' => $product->id,
        'product_color_id' => $color->id,
        'stock' => 5,
        'price' => 1000,
        'size' => 'M',
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
                'quantity' => 0,
            ]
        ]
    ];

    // Zero quantity should be rejected
    expect(fn () => app(OrderService::class)->create($orderData))
        ->toThrow(\Exception::class);
});

it('rechaza arrays de items vacios', function () {
    $client = Client::factory()->create();

    $orderData = [
        'client_id' => $client->id,
        'date' => now()->format('Y-m-d'),
        'status' => Order::STATUS_PENDING,
        'payment_method' => 'cash',
        'delivery_type' => 'showroom',
        'items' => [],
    ];

    // Empty items should be rejected natively by service
    expect(fn () => app(OrderService::class)->create($orderData))
        ->toThrow(\Exception::class);
});
