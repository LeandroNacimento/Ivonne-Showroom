<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductVariation;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPendingUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->admin()->create());
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_it_rebuilds_a_pending_order_completely_when_updating_its_content(): void
    {
        $originalClient = Client::factory()->create();
        $order = Order::factory()->create([
            'client_id' => $originalClient->id,
            'status' => Order::STATUS_PENDING,
            'payment_method' => 'cash',
            'delivery_type' => 'showroom',
            'shipping_cost' => 0,
            'total' => 1000,
        ]);

        $originalVariationData = $this->createVariation('Producto original', 'Negro', 'M', 10, 1000);
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $originalVariationData['product']->id,
            'variation_id' => $originalVariationData['variation']->id,
            'color' => $originalVariationData['color']->name,
            'size' => $originalVariationData['variation']->size,
            'quantity' => 1,
            'unit_price' => 1000,
            'subtotal' => 1000,
        ]);

        $firstReplacement = $this->createVariation('Vestido lino', 'Beige', 'S', 8, 2500);
        $secondReplacement = $this->createVariation('Blazer noche', 'Azul', 'L', 6, 4800);

        $response = $this->put(route('admin.orders.update', $order), [
            'client_id' => null,
            'new_client_name' => 'Cliente Nuevo',
            'new_client_phone' => '3704000000',
            'new_client_instagram' => '@cliente_nuevo',
            'new_client_email' => 'cliente@example.com',
            'new_client_notes' => 'Pide aviso previo',
            'date' => now()->subHour()->format('Y-m-d H:i:s'),
            'status' => Order::STATUS_PENDING,
            'payment_method' => 'transfer',
            'delivery_type' => 'shipping',
            'shipping_cost' => 700,
            'items' => [
                [
                    'product_id' => $firstReplacement['product']->id,
                    'variation_id' => $firstReplacement['variation']->id,
                    'quantity' => 2,
                ],
                [
                    'product_id' => $secondReplacement['product']->id,
                    'variation_id' => $secondReplacement['variation']->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.orders.index'));

        $order->refresh()->load(['client', 'items']);

        $this->assertSame(2, Client::count());
        $this->assertSame(Order::STATUS_PENDING, $order->status);
        $this->assertNotSame($originalClient->id, $order->client_id);
        $this->assertSame('Cliente Nuevo', $order->client->name);
        $this->assertSame('transfer', $order->payment_method);
        $this->assertSame('shipping', $order->delivery_type);
        $this->assertSame(700.0, (float) $order->shipping_cost);
        $this->assertSame(10500.0, (float) $order->total);

        $this->assertCount(2, $order->items);
        $this->assertDatabaseMissing('order_items', [
            'order_id' => $order->id,
            'variation_id' => $originalVariationData['variation']->id,
        ]);

        $firstItem = $order->items->firstWhere('variation_id', $firstReplacement['variation']->id);
        $secondItem = $order->items->firstWhere('variation_id', $secondReplacement['variation']->id);

        $this->assertNotNull($firstItem);
        $this->assertSame($firstReplacement['color']->name, $firstItem->color);
        $this->assertSame('S', $firstItem->size);
        $this->assertSame(2, $firstItem->quantity);
        $this->assertSame(2500.0, (float) $firstItem->unit_price);
        $this->assertSame(5000.0, (float) $firstItem->subtotal);

        $this->assertNotNull($secondItem);
        $this->assertSame($secondReplacement['color']->name, $secondItem->color);
        $this->assertSame('L', $secondItem->size);
        $this->assertSame(1, $secondItem->quantity);
        $this->assertSame(4800.0, (float) $secondItem->unit_price);
        $this->assertSame(4800.0, (float) $secondItem->subtotal);

        $this->assertSame(10, $originalVariationData['variation']->fresh()->stock);
        $this->assertSame(8, $firstReplacement['variation']->fresh()->stock);
        $this->assertSame(6, $secondReplacement['variation']->fresh()->stock);
    }

    public function test_it_rebuilds_pending_order_items_before_transitioning_to_reserved(): void
    {
        $client = Client::factory()->create();
        $order = Order::factory()->create([
            'client_id' => $client->id,
            'status' => Order::STATUS_PENDING,
            'payment_method' => 'cash',
            'delivery_type' => 'showroom',
            'shipping_cost' => 0,
            'total' => 900,
        ]);

        $originalVariationData = $this->createVariation('Producto original', 'Negro', 'M', 10, 900);
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $originalVariationData['product']->id,
            'variation_id' => $originalVariationData['variation']->id,
            'color' => $originalVariationData['color']->name,
            'size' => $originalVariationData['variation']->size,
            'quantity' => 1,
            'unit_price' => 900,
            'subtotal' => 900,
        ]);

        $replacementVariationData = $this->createVariation('Tapado invierno', 'Camel', 'XL', 5, 3200);

        $response = $this->put(route('admin.orders.update', $order), [
            'client_id' => $client->id,
            'date' => now()->subMinutes(30)->format('Y-m-d H:i:s'),
            'status' => Order::STATUS_RESERVED,
            'payment_method' => 'cash',
            'delivery_type' => 'showroom',
            'shipping_cost' => 0,
            'items' => [
                [
                    'product_id' => $replacementVariationData['product']->id,
                    'variation_id' => $replacementVariationData['variation']->id,
                    'quantity' => 3,
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.orders.index'));

        $order->refresh()->load('items');

        $this->assertSame(Order::STATUS_RESERVED, $order->status);
        $this->assertCount(1, $order->items);
        $this->assertDatabaseMissing('order_items', [
            'order_id' => $order->id,
            'variation_id' => $originalVariationData['variation']->id,
        ]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'variation_id' => $replacementVariationData['variation']->id,
            'quantity' => 3,
            'color' => $replacementVariationData['color']->name,
            'size' => 'XL',
        ]);

        $this->assertSame(10, $originalVariationData['variation']->fresh()->stock);
        $this->assertSame(2, $replacementVariationData['variation']->fresh()->stock);
        $this->assertSame(9600.0, (float) $order->total);
    }

    public function test_it_allows_updating_a_pending_order_without_payment_method_when_it_stays_pending(): void
    {
        $client = Client::factory()->create();
        $order = Order::factory()->create([
            'client_id' => $client->id,
            'status' => Order::STATUS_PENDING,
            'payment_method' => 'cash',
            'delivery_type' => 'showroom',
            'shipping_cost' => 0,
            'total' => 900,
        ]);

        $variationData = $this->createVariation('Remera base', 'Negro', 'M', 10, 900);
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $variationData['product']->id,
            'variation_id' => $variationData['variation']->id,
            'color' => $variationData['color']->name,
            'size' => $variationData['variation']->size,
            'quantity' => 1,
            'unit_price' => 900,
            'subtotal' => 900,
        ]);

        $response = $this->put(route('admin.orders.update', $order), [
            'client_id' => $client->id,
            'date' => now()->subMinutes(10)->format('Y-m-d H:i:s'),
            'status' => Order::STATUS_PENDING,
            'payment_method' => null,
            'delivery_type' => 'showroom',
            'shipping_cost' => 0,
            'items' => [
                [
                    'product_id' => $variationData['product']->id,
                    'variation_id' => $variationData['variation']->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.orders.index'));

        $this->assertNull($order->fresh()->payment_method);
        $this->assertSame(Order::STATUS_PENDING, $order->fresh()->status);
    }

    public function test_it_can_transition_a_pending_order_to_delivered_when_payment_method_is_provided(): void
    {
        $client = Client::factory()->create();
        $order = Order::factory()->create([
            'client_id' => $client->id,
            'status' => Order::STATUS_PENDING,
            'payment_method' => null,
            'delivery_type' => 'showroom',
            'shipping_cost' => 0,
            'total' => 1200,
        ]);

        $variationData = $this->createVariation('Pantalon sastrero', 'Negro', 'M', 6, 1200);
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $variationData['product']->id,
            'variation_id' => $variationData['variation']->id,
            'color' => $variationData['color']->name,
            'size' => $variationData['variation']->size,
            'quantity' => 2,
            'unit_price' => 1200,
            'subtotal' => 2400,
        ]);

        $response = $this->put(route('admin.orders.update', $order), [
            'client_id' => $client->id,
            'date' => now()->subMinutes(20)->format('Y-m-d H:i:s'),
            'status' => Order::STATUS_DELIVERED,
            'payment_method' => Order::PAYMENT_METHOD_CASH,
            'delivery_type' => 'showroom',
            'shipping_cost' => 0,
            'items' => [
                [
                    'product_id' => $variationData['product']->id,
                    'variation_id' => $variationData['variation']->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $response->assertRedirect(route('admin.orders.index'));

        $order->refresh();

        $this->assertSame(Order::STATUS_DELIVERED, $order->status);
        $this->assertSame(Order::PAYMENT_METHOD_CASH, $order->payment_method);
        $this->assertSame(4, $variationData['variation']->fresh()->stock);
    }

    private function createVariation(string $productName, string $colorName, string $size, int $stock, float $price): array
    {
        $product = Product::factory()->create(['name' => $productName]);
        $color = ProductColor::factory()->create([
            'product_id' => $product->id,
            'name' => $colorName,
        ]);
        $variation = ProductVariation::factory()->create([
            'product_id' => $product->id,
            'product_color_id' => $color->id,
            'size' => $size,
            'stock' => $stock,
            'price' => $price,
        ]);

        return [
            'product' => $product,
            'color' => $color,
            'variation' => $variation,
        ];
    }
}
