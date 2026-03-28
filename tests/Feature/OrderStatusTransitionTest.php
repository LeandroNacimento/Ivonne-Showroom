<?php

use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductVariation;
use App\Services\OrderService;
use App\Services\OrderStatusTransitionHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->client = Client::factory()->create();
    $this->product = Product::factory()->create();
    $this->color = ProductColor::factory()->create(['product_id' => $this->product->id]);
    $this->variation = ProductVariation::factory()->create([
        'product_id' => $this->product->id,
        'product_color_id' => $this->color->id,
        'stock' => 10,
        'price' => 1000,
        'size' => 'L',
    ]);

    // Create an order in PENDING status (no stock change yet)
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
            ],
        ],
    ];

    $this->order = app(OrderService::class)->create($this->orderData);
    $this->order->load(['items.variation.productColor', 'items.product']);
    $this->handler = app(OrderStatusTransitionHandler::class);
});

it('reduce el stock unicamente al pasar a RESERVED', function () {
    // Pending order should not affect stock
    expect($this->variation->fresh()->stock)->toBe(10);

    // Transition to RESERVED
    $this->handler->handle($this->order, Order::STATUS_PENDING, Order::STATUS_RESERVED);
    $this->order->update(['status' => Order::STATUS_RESERVED]);

    expect($this->variation->fresh()->stock)->toBe(8);
});

it('no altera el stock en transiciones posteriores', function () {
    // Transition to RESERVED
    $this->handler->handle($this->order, Order::STATUS_PENDING, Order::STATUS_RESERVED);
    $this->order->update(['status' => Order::STATUS_RESERVED]);
    expect($this->variation->fresh()->stock)->toBe(8);

    // Transition RESERVED -> DELIVERED
    $this->handler->handle($this->order, Order::STATUS_RESERVED, Order::STATUS_DELIVERED);
    $this->order->update(['status' => Order::STATUS_DELIVERED]);

    // Stock should remain 8
    expect($this->variation->fresh()->stock)->toBe(8);
});

it('lanza excepcion en transiciones invalidas', function () {
    // First deliver the order properly
    $this->handler->handle($this->order, Order::STATUS_PENDING, Order::STATUS_RESERVED);
    $this->order->update(['status' => Order::STATUS_RESERVED]);
    $this->handler->handle($this->order, Order::STATUS_RESERVED, Order::STATUS_DELIVERED);
    $this->order->update(['status' => Order::STATUS_DELIVERED]);

    // Transitioning from DELIVERED (terminal state) should throw
    expect(fn () => $this->handler->handle($this->order, Order::STATUS_DELIVERED, Order::STATUS_PENDING))
        ->toThrow(ValidationException::class);
});
