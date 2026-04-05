<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductVariation;
use App\Services\OrderService;
use App\Services\OrderStatusTransitionHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OrderStatusTransitionTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;

    private Product $product;

    private ProductColor $color;

    private ProductVariation $variation;

    private array $orderData;

    private Order $order;

    private OrderStatusTransitionHandler $handler;

    protected function setUp(): void
    {
        parent::setUp();

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
    }

    public function test_it_reduce_el_stock_unicamente_al_pasar_a_reserved(): void
    {
        $this->assertSame(10, $this->variation->fresh()->stock);

        $this->handler->handle($this->order, Order::STATUS_PENDING, Order::STATUS_RESERVED);
        $this->order->update(['status' => Order::STATUS_RESERVED]);

        $this->assertSame(8, $this->variation->fresh()->stock);
    }

    public function test_it_no_altera_el_stock_en_transiciones_posteriores(): void
    {
        $this->handler->handle($this->order, Order::STATUS_PENDING, Order::STATUS_RESERVED);
        $this->order->update(['status' => Order::STATUS_RESERVED]);
        $this->assertSame(8, $this->variation->fresh()->stock);

        $this->handler->handle($this->order, Order::STATUS_RESERVED, Order::STATUS_DELIVERED);
        $this->order->update(['status' => Order::STATUS_DELIVERED]);

        $this->assertSame(8, $this->variation->fresh()->stock);
    }

    public function test_it_lanza_excepcion_en_transiciones_invalidas(): void
    {
        $this->handler->handle($this->order, Order::STATUS_PENDING, Order::STATUS_RESERVED);
        $this->order->update(['status' => Order::STATUS_RESERVED]);
        $this->handler->handle($this->order, Order::STATUS_RESERVED, Order::STATUS_DELIVERED);
        $this->order->update(['status' => Order::STATUS_DELIVERED]);

        $this->expectException(ValidationException::class);
        $this->handler->handle($this->order, Order::STATUS_DELIVERED, Order::STATUS_PENDING);
    }
}
