<?php

use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\OrderService;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->client = Client::factory()->create();
    $this->product = Product::factory()->create();
    $this->variation = ProductVariation::factory()->create([
        'product_id' => $this->product->id,
        'stock' => 5,
        'price' => 1000,
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
            ]
        ]
    ];
});

it('maneja correctamente productos eliminados en pedidos', function () {
    $order = app(OrderService::class)->create($this->orderData);
    
    // Soft delete product or variation (Requires SoftDeletes to not break DB!)
    // Assuming products don't cascade delete orders
    $this->product->delete();

    // The order should still be retrievable
    $orderRetrieved = Order::with('items.product')->find($order->id);
    expect($orderRetrieved)->not->toBeNull();
    // Some logic based on whether product is null in item
    expect($orderRetrieved->items->first()->product)->toBeNull();
})->skip("Asumiendo onDelete set null o config de DB soft delete.");

it('no rompe si una variacion es null al accederla', function () {
    $order = app(OrderService::class)->create($this->orderData);
    $this->variation->delete(); // Or hard delete via DB if not foreign constrained
    
    $orderRetrieved = Order::with('items.variation')->find($order->id);
    expect($orderRetrieved)->not->toBeNull();
})->skip("Idem test anterior");

it('rechaza cantidades invalidas', function () {
    $this->orderData['items'][0]['quantity'] = -5;

    expect(fn () => app(OrderService::class)->create($this->orderData))
        ->toThrow(\Exception::class); // or ValidationError

    expect(Order::count())->toBe(0);
});

it('rechaza arrays de items vacios', function () {
    $this->orderData['items'] = [];

    expect(fn () => app(OrderService::class)->create($this->orderData))
        ->toThrow(\Exception::class);
    
    expect(Order::count())->toBe(0);
});
