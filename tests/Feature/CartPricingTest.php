<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductVariation;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartPricingTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_recalculates_cart_prices_from_the_current_effective_price(): void
    {
        $product = Product::factory()->create(['name' => 'Blazer']);
        $color = ProductColor::factory()->create([
            'product_id' => $product->id,
            'name' => 'Negro',
        ]);
        $variation = ProductVariation::factory()->create([
            'product_id' => $product->id,
            'product_color_id' => $color->id,
            'size' => 'M',
            'stock' => 5,
            'price' => 10000,
            'sale_price' => null,
        ]);

        $service = app(CartService::class);
        $this->assertTrue($service->addToCart($product->id, $variation->id, 2));
        $this->assertSame(20000.0, $service->getTotal());

        $variation->update(['sale_price' => 8000]);

        $cart = $service->getCart();

        $this->assertSame(16000.0, $service->getTotal());
        $this->assertSame(8000.0, (float) $cart[$product->id.'-'.$variation->id]['unit_price']);
        $this->assertStringContainsString('16.000', urldecode($service->getWhatsAppMessage()));
    }
}
