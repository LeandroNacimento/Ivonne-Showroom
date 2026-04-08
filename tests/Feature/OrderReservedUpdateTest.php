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

class OrderReservedUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->admin()->create());
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_it_updates_only_the_reserved_order_header_and_recalculates_total_from_persisted_items(): void
    {
        $originalClient = Client::factory()->create();
        $order = Order::factory()->create([
            'client_id' => $originalClient->id,
            'status' => Order::STATUS_RESERVED,
            'payment_method' => 'cash',
            'delivery_type' => 'showroom',
            'shipping_cost' => 0,
            'total' => 999,
        ]);

        $firstVariationData = $this->createVariation('Vestido gala', 'Negro', 'M', 10, 2000);
        $secondVariationData = $this->createVariation('Blazer sastrero', 'Beige', 'L', 8, 3500);

        $firstItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $firstVariationData['product']->id,
            'variation_id' => $firstVariationData['variation']->id,
            'color' => $firstVariationData['color']->name,
            'size' => $firstVariationData['variation']->size,
            'quantity' => 1,
            'unit_price' => 2000,
            'subtotal' => 2000,
        ]);

        $secondItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $secondVariationData['product']->id,
            'variation_id' => $secondVariationData['variation']->id,
            'color' => $secondVariationData['color']->name,
            'size' => $secondVariationData['variation']->size,
            'quantity' => 1,
            'unit_price' => 3500,
            'subtotal' => 3500,
        ]);

        $response = $this->put(route('admin.orders.update', $order), [
            'client_id' => null,
            'new_client_name' => 'Clienta Reservada',
            'new_client_phone' => '3704555555',
            'new_client_instagram' => '@clienta.reservada',
            'new_client_email' => 'reservada@example.com',
            'new_client_notes' => 'Prefiere coordinar por WhatsApp',
            'date' => now()->subMinutes(15)->format('Y-m-d H:i:s'),
            'status' => Order::STATUS_RESERVED,
            'payment_method' => 'transfer',
            'delivery_type' => 'shipping',
            'shipping_cost' => 750,
        ]);

        $response->assertRedirect(route('admin.orders.index'));

        $order->refresh()->load(['client', 'items']);

        $this->assertSame(2, Client::count());
        $this->assertSame(Order::STATUS_RESERVED, $order->status);
        $this->assertNotSame($originalClient->id, $order->client_id);
        $this->assertSame('Clienta Reservada', $order->client->name);
        $this->assertSame('transfer', $order->payment_method);
        $this->assertSame('shipping', $order->delivery_type);
        $this->assertSame(750.0, (float) $order->shipping_cost);
        $this->assertSame(6250.0, (float) $order->total);

        $this->assertCount(2, $order->items);
        $this->assertDatabaseHas('order_items', [
            'id' => $firstItem->id,
            'order_id' => $order->id,
            'variation_id' => $firstVariationData['variation']->id,
            'quantity' => 1,
            'unit_price' => 2000,
            'subtotal' => 2000,
        ]);
        $this->assertDatabaseHas('order_items', [
            'id' => $secondItem->id,
            'order_id' => $order->id,
            'variation_id' => $secondVariationData['variation']->id,
            'quantity' => 1,
            'unit_price' => 3500,
            'subtotal' => 3500,
        ]);
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
