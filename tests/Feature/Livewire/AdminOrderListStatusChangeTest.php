<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Admin\Orders\OrderList;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductVariation;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminOrderListStatusChangeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->admin()->create());
    }

    public function test_it_changes_a_pending_order_to_reserved_inline_and_discounts_stock(): void
    {
        $variation = $this->createVariation(stock: 5, price: 1800);
        $order = $this->createPendingOrder($variation, 2);

        Livewire::test(OrderList::class)
            ->call('changeStatus', $order->id, Order::STATUS_RESERVED)
            ->assertSet('feedbackType', 'success');

        $this->assertSame(Order::STATUS_RESERVED, $order->fresh()->status);
        $this->assertSame(3, $variation->fresh()->stock);
    }

    public function test_it_can_deliver_a_pending_order_inline_when_payment_method_is_sent(): void
    {
        $variation = $this->createVariation(stock: 5, price: 1800);
        $order = $this->createPendingOrder($variation, 2, null);

        Livewire::test(OrderList::class)
            ->call('changeStatus', $order->id, Order::STATUS_DELIVERED, Order::PAYMENT_METHOD_CASH)
            ->assertSet('feedbackType', 'success');

        $this->assertSame(Order::STATUS_DELIVERED, $order->fresh()->status);
        $this->assertSame(Order::PAYMENT_METHOD_CASH, $order->fresh()->payment_method);
        $this->assertSame(3, $variation->fresh()->stock);
    }

    public function test_it_cancels_a_reserved_order_inline_and_restores_stock(): void
    {
        $variation = $this->createVariation(stock: 5, price: 1800);
        $order = $this->createReservedOrder($variation, 2);

        $this->assertSame(3, $variation->fresh()->stock);

        Livewire::test(OrderList::class)
            ->call('changeStatus', $order->id, Order::STATUS_CANCELLED)
            ->assertSet('feedbackType', 'success');

        $this->assertSame(Order::STATUS_CANCELLED, $order->fresh()->status);
        $this->assertSame(5, $variation->fresh()->stock);
    }

    public function test_it_marks_a_reserved_order_as_delivered_inline_without_changing_stock(): void
    {
        $variation = $this->createVariation(stock: 5, price: 1800);
        $order = $this->createReservedOrder($variation, 2);

        $this->assertSame(3, $variation->fresh()->stock);

        Livewire::test(OrderList::class)
            ->call('changeStatus', $order->id, Order::STATUS_DELIVERED)
            ->assertSet('feedbackType', 'success');

        $this->assertSame(Order::STATUS_DELIVERED, $order->fresh()->status);
        $this->assertSame(3, $variation->fresh()->stock);
    }

    public function test_it_cancels_a_pending_order_inline_without_changing_stock(): void
    {
        $variation = $this->createVariation(stock: 5, price: 1800);
        $order = $this->createPendingOrder($variation, 2);

        Livewire::test(OrderList::class)
            ->call('changeStatus', $order->id, Order::STATUS_CANCELLED)
            ->assertSet('feedbackType', 'success');

        $this->assertSame(Order::STATUS_CANCELLED, $order->fresh()->status);
        $this->assertSame(5, $variation->fresh()->stock);
    }

    public function test_it_requires_payment_method_for_pending_to_delivered_inline_transitions(): void
    {
        $variation = $this->createVariation(stock: 5, price: 1800);
        $order = $this->createPendingOrder($variation, 2, null);

        Livewire::test(OrderList::class)
            ->call('changeStatus', $order->id, Order::STATUS_DELIVERED)
            ->assertSet('feedbackType', 'error');

        $this->assertSame(Order::STATUS_PENDING, $order->fresh()->status);
        $this->assertSame(5, $variation->fresh()->stock);
    }

    public function test_it_rejects_invalid_inline_transitions_without_changing_the_order(): void
    {
        $variation = $this->createVariation(stock: 5, price: 1800);
        $order = $this->createReservedOrder($variation, 2);

        Livewire::test(OrderList::class)
            ->call('changeStatus', $order->id, Order::STATUS_PENDING)
            ->assertSet('feedbackType', 'error');

        $this->assertSame(Order::STATUS_RESERVED, $order->fresh()->status);
        $this->assertSame(3, $variation->fresh()->stock);
    }

    public function test_it_keeps_the_current_page_after_an_inline_status_change(): void
    {
        $client = Client::factory()->create();

        $orderOnSecondPage = Order::factory()->create([
            'client_id' => $client->id,
            'status' => Order::STATUS_PENDING,
            'payment_method' => 'cash',
            'delivery_type' => 'showroom',
            'shipping_cost' => 0,
            'total' => 1000,
            'created_at' => now()->subMinutes(20),
        ]);

        for ($i = 1; $i <= 10; $i++) {
            Order::factory()->create([
                'client_id' => $client->id,
                'status' => Order::STATUS_PENDING,
                'payment_method' => 'cash',
                'delivery_type' => 'showroom',
                'shipping_cost' => 0,
                'total' => 1000 + $i,
                'created_at' => now()->subMinutes($i),
            ]);
        }

        Livewire::withQueryParams(['page' => 2])
            ->test(OrderList::class)
            ->assertSet('paginators.page', 2)
            ->call('changeStatus', $orderOnSecondPage->id, Order::STATUS_CANCELLED)
            ->assertSet('paginators.page', 2)
            ->assertSet('feedbackType', 'success');

        $this->assertSame(Order::STATUS_CANCELLED, $orderOnSecondPage->fresh()->status);
    }

    public function test_terminal_orders_do_not_render_the_inline_status_select(): void
    {
        $client = Client::factory()->create();
        Order::factory()->create([
            'client_id' => $client->id,
            'status' => Order::STATUS_DELIVERED,
            'payment_method' => 'cash',
            'delivery_type' => 'showroom',
            'shipping_cost' => 0,
            'total' => 1000,
        ]);

        $response = $this->get(route('admin.orders.index'));
        $response->assertOk();

        $html = $response->getContent();

        $this->assertStringContainsString('Entregado', $html);
        $this->assertStringNotContainsString('x-model="selectedStatus"', $html);
    }

    private function createVariation(int $stock, float $price): ProductVariation
    {
        $product = Product::factory()->create();
        $color = ProductColor::factory()->create([
            'product_id' => $product->id,
        ]);

        return ProductVariation::factory()->create([
            'product_id' => $product->id,
            'product_color_id' => $color->id,
            'stock' => $stock,
            'price' => $price,
            'size' => 'M',
        ]);
    }

    private function createPendingOrder(ProductVariation $variation, int $quantity, ?string $paymentMethod = Order::PAYMENT_METHOD_CASH): Order
    {
        $client = Client::factory()->create();

        $order = Order::factory()->create([
            'client_id' => $client->id,
            'status' => Order::STATUS_PENDING,
            'payment_method' => $paymentMethod,
            'delivery_type' => 'showroom',
            'shipping_cost' => 0,
            'total' => $variation->price * $quantity,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $variation->product_id,
            'variation_id' => $variation->id,
            'color' => $variation->productColor?->name ?? 'N/A',
            'size' => $variation->size,
            'quantity' => $quantity,
            'unit_price' => $variation->price,
            'subtotal' => $variation->price * $quantity,
        ]);

        return $order;
    }

    private function createReservedOrder(ProductVariation $variation, int $quantity): Order
    {
        $client = Client::factory()->create();

        return app(OrderService::class)->create([
            'client_id' => $client->id,
            'date' => now()->format('Y-m-d H:i:s'),
            'status' => Order::STATUS_RESERVED,
            'payment_method' => 'cash',
            'delivery_type' => 'showroom',
            'shipping_cost' => 0,
            'items' => [
                [
                    'product_id' => $variation->product_id,
                    'variation_id' => $variation->id,
                    'quantity' => $quantity,
                ],
            ],
        ]);
    }
}
