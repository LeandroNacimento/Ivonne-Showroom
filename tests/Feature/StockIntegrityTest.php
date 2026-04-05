<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductVariation;
use App\Services\OrderService;
use App\Services\OrderStatusTransitionHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class StockIntegrityTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;

    private Product $product;

    private ProductColor $color;

    private ProductVariation $variation;

    private array $orderData;

    protected function setUp(): void
    {
        parent::setUp();

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
    }

    public function test_it_descuenta_el_stock_correctamente_al_crear_un_pedido_reservado(): void
    {
        app(OrderService::class)->create($this->orderData);

        $this->assertSame(3, $this->variation->fresh()->stock);
    }

    public function test_it_falla_al_intentar_comprar_mas_del_stock_disponible(): void
    {
        $this->orderData['items'][0]['quantity'] = 6;

        $this->expectException(\Exception::class);
        app(OrderService::class)->create($this->orderData);
    }

    public function test_it_preserves_stock_when_purchase_exceeds_availability(): void
    {
        $this->orderData['items'][0]['quantity'] = 6;

        try {
            app(OrderService::class)->create($this->orderData);
            $this->fail('Expected exception was not thrown.');
        } catch (\Exception) {
            $this->assertSame(5, $this->variation->fresh()->stock);
        }
    }

    public function test_it_no_permite_que_el_stock_quede_negativo(): void
    {
        $this->orderData['items'][0]['quantity'] = 10;

        try {
            app(OrderService::class)->create($this->orderData);
        } catch (\Exception) {
            // Expected.
        }

        $this->assertGreaterThanOrEqual(0, $this->variation->fresh()->stock);
    }

    public function test_it_restaura_el_stock_al_cancelar_un_pedido_reservado(): void
    {
        $order = app(OrderService::class)->create($this->orderData);
        $order->load(['items.variation.productColor', 'items.product']);
        $this->assertSame(3, $this->variation->fresh()->stock);

        app(OrderStatusTransitionHandler::class)->handle($order, Order::STATUS_RESERVED, Order::STATUS_CANCELLED);
        $order->update(['status' => Order::STATUS_CANCELLED]);

        $this->assertSame(5, $this->variation->fresh()->stock);
    }

    public function test_it_revierte_completamente_el_pedido_si_ocurre_un_fallo_durante_la_creacion(): void
    {
        $handlerMock = Mockery::mock(OrderStatusTransitionHandler::class);
        $handlerMock->shouldReceive('handle')->andThrow(new \Exception('Simulated Failure'));
        $this->app->instance(OrderStatusTransitionHandler::class, $handlerMock);

        try {
            app(OrderService::class)->create($this->orderData);
            $this->fail('Expected exception was not thrown.');
        } catch (\Exception $e) {
            $this->assertSame('Simulated Failure', $e->getMessage());
        }

        $this->assertSame(0, Order::count());
        $this->assertSame(0, OrderItem::count());
        $this->assertSame(5, $this->variation->fresh()->stock);
    }
}
