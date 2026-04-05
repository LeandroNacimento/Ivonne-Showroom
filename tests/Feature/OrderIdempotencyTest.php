<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_no_duplica_pedidos_ante_multiples_envios_simultaneos(): void
    {
        $this->markTestSkipped('Depende de la implementacion del token de idempotencia en la API o DB (Unique Constraint)');

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
            'idempotency_key' => 'unique-tx-12345',
        ];

        $service = app(OrderService::class);
        $order1 = $service->create($orderData);

        $order2 = null;
        try {
            $order2 = $service->create($orderData);
        } catch (\Exception) {
            // Duplicate exception expected.
        }

        $this->assertSame(1, Order::count());

        if ($order2) {
            $this->assertSame($order1->id, $order2->id);
        }
    }
}
