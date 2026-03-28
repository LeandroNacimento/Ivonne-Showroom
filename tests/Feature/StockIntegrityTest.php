<?php

use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductVariation;
use App\Services\OrderService;
use App\Services\OrderStatusTransitionHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->client = Client::factory()->create();
    $this->product = Product::factory()->create();
    $this->color = ProductColor::factory()->create(['product_id' => $this->product->id]);
    $this->variation = ProductVariation::factory()->create([
        'product_id' => $this->product->id,
        'product_color_id' => $this->color->id,
        'stock' => 5,
        'price' => 1000,
        'size' => 'M',
    ]);

    $this->orderData = [
        'client_id' => $this->client->id,
        'date' => now()->format('Y-m-d'),
        'status' => Order::STATUS_RESERVED,
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
});

it('descuenta el stock correctamente al crear un pedido reservado', function () {
    app(OrderService::class)->create($this->orderData);
    expect($this->variation->fresh()->stock)->toBe(3);
});

it('falla al intentar comprar más del stock disponible', function () {
    $this->orderData['items'][0]['quantity'] = 6;

    expect(fn () => app(OrderService::class)->create($this->orderData))
        ->toThrow(\Exception::class);

    expect($this->variation->fresh()->stock)->toBe(5);
});

it('no permite que el stock quede negativo', function () {
    $this->orderData['items'][0]['quantity'] = 10;

    try {
        app(OrderService::class)->create($this->orderData);
    } catch (\Exception $e) {
        // Expected
    }

    expect($this->variation->fresh()->stock)->toBeGreaterThanOrEqual(0);
});

it('restaura el stock al cancelar un pedido reservado', function () {
    $order = app(OrderService::class)->create($this->orderData);
    $order->load(['items.variation.productColor', 'items.product']);
    expect($this->variation->fresh()->stock)->toBe(3);

    app(OrderStatusTransitionHandler::class)->handle($order, Order::STATUS_RESERVED, Order::STATUS_CANCELLED);
    $order->update(['status' => Order::STATUS_CANCELLED]);

    expect($this->variation->fresh()->stock)->toBe(5);
});

it('revierte completamente el pedido si ocurre un fallo durante la creación', function () {
    // Mock the handler to throw during the transition
    $handlerMock = Mockery::mock(OrderStatusTransitionHandler::class);
    $handlerMock->shouldReceive('handle')->andThrow(new \Exception('Simulated Failure'));
    $this->app->instance(OrderStatusTransitionHandler::class, $handlerMock);

    try {
        app(OrderService::class)->create($this->orderData);
    } catch (\Exception $e) {
        expect($e->getMessage())->toBe('Simulated Failure');
    }

    // Transaction should have rolled back everything
    expect(Order::count())->toBe(0)
        ->and(OrderItem::count())->toBe(0)
        ->and($this->variation->fresh()->stock)->toBe(5);
});
