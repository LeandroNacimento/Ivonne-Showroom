<?php

use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\OrderService;
use App\Services\OrderStatusTransitionHandler;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->client = Client::factory()->create();
    $this->product = Product::factory()->create();
    $this->variation = ProductVariation::factory()->create([
        'product_id' => $this->product->id,
        'stock' => 10,
        'price' => 1000,
    ]);

    // Create an order in PENDING status
    $this->orderData = [
        'client_id' => $this->client->id,
        'date' => now()->format('Y-m-d'),
        'status' => Order::STATUS_PENDING,
        'payment_method' => 'cash',
        'delivery_type' => 'showroom',
        'items' => [
            [
                'product_id' => $this->product->id,
                'variation_id' => $this->variation->id,
                'quantity' => 2,
            ]
        ]
    ];

    $this->order = app(OrderService::class)->create($this->orderData);
    $this->handler = app(OrderStatusTransitionHandler::class);
});

it('reduce el stock unicamente al pasar a RESERVED', function () {
    // Initial stock was 10, order pending should not affect it.
    expect($this->variation->fresh()->stock)->toBe(10);

    // Transition to RESERVED
    $this->handler->handle($this->order, Order::STATUS_PENDING, Order::STATUS_RESERVED);
    $this->order->update(['status' => Order::STATUS_RESERVED]);

    // Stock should be 8
    expect($this->variation->fresh()->stock)->toBe(8);
});

it('no altera el stock en transiciones posteriores', function () {
    // Transition to RESERVED
    $this->handler->handle($this->order, Order::STATUS_PENDING, Order::STATUS_RESERVED);
    $this->order->update(['status' => Order::STATUS_RESERVED]);
    
    // Stock is 8
    expect($this->variation->fresh()->stock)->toBe(8);

    // Transition RESERVED -> DELIVERED
    $this->handler->handle($this->order, Order::STATUS_RESERVED, Order::STATUS_DELIVERED);
    $this->order->update(['status' => Order::STATUS_DELIVERED]);

    // Stock should remain 8
    expect($this->variation->fresh()->stock)->toBe(8);
});

it('lanza excepcion en transiciones invalidas', function () {
    // Transitioning from DELIVERED is terminal, it should throw
    $this->order->update(['status' => Order::STATUS_DELIVERED]);
    
    expect(fn () => $this->handler->handle($this->order, Order::STATUS_DELIVERED, Order::STATUS_PENDING))
        ->toThrow(ValidationException::class);
});
