<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductVariation;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EdgeCasesTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_rechaza_cantidades_negativas(): void
    {
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
                ],
            ],
        ];

        $this->expectException(\Exception::class);
        app(OrderService::class)->create($orderData);
    }

    public function test_it_preserves_stock_when_negative_quantities_are_rejected(): void
    {
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
                ],
            ],
        ];

        try {
            app(OrderService::class)->create($orderData);
            $this->fail('Expected exception was not thrown.');
        } catch (\Exception) {
            $this->assertSame(0, Order::count());
            $this->assertSame(5, $variation->fresh()->stock);
        }
    }

    public function test_it_rechaza_cantidad_cero(): void
    {
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
                ],
            ],
        ];

        $this->expectException(\Exception::class);
        app(OrderService::class)->create($orderData);
    }

    public function test_it_rechaza_arrays_de_items_vacios(): void
    {
        $client = Client::factory()->create();

        $orderData = [
            'client_id' => $client->id,
            'date' => now()->format('Y-m-d'),
            'status' => Order::STATUS_PENDING,
            'payment_method' => 'cash',
            'delivery_type' => 'showroom',
            'items' => [],
        ];

        $this->expectException(\Exception::class);
        app(OrderService::class)->create($orderData);
    }
}
