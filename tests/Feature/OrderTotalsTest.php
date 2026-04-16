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

class OrderTotalsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_calcula_el_total_del_pedido_en_backend_ignorando_valores_del_frontend(): void
    {
        $client = Client::factory()->create();
        $product = Product::factory()->create();
        $color = ProductColor::factory()->create(['product_id' => $product->id]);
        $variation = ProductVariation::factory()->create([
            'product_id' => $product->id,
            'product_color_id' => $color->id,
            'stock' => 10,
            'price' => 1500.00,
            'sale_price' => 1200.00,
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
                    'quantity' => 2,
                    'unit_price' => 100,
                ],
            ],
        ];

        $order = app(OrderService::class)->create($orderData);

        $this->assertSame(2400.0, (float) $order->total);
        $this->assertSame(1200.0, (float) $order->items()->first()->unit_price);
    }
}
