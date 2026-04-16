<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductVariation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductOfferPricingTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_variation_exposes_offer_accessors(): void
    {
        $product = Product::factory()->create();
        $color = ProductColor::factory()->create(['product_id' => $product->id]);
        $variation = ProductVariation::factory()->create([
            'product_id' => $product->id,
            'product_color_id' => $color->id,
            'price' => 10000,
            'sale_price' => 7500,
        ]);

        $this->assertSame(10000.0, (float) $variation->original_price);
        $this->assertSame(7500.0, (float) $variation->effective_price);
        $this->assertTrue($variation->has_active_offer);
    }

    public function test_product_uses_storefront_display_aggregates_from_available_variations(): void
    {
        $product = Product::factory()->create();
        $color = ProductColor::factory()->create(['product_id' => $product->id]);

        ProductVariation::factory()->create([
            'product_id' => $product->id,
            'product_color_id' => $color->id,
            'stock' => 2,
            'price' => 12000,
            'sale_price' => 9000,
            'size' => 'S',
        ]);

        ProductVariation::factory()->create([
            'product_id' => $product->id,
            'product_color_id' => $color->id,
            'stock' => 3,
            'price' => 10000,
            'sale_price' => null,
            'size' => 'M',
        ]);

        ProductVariation::factory()->create([
            'product_id' => $product->id,
            'product_color_id' => $color->id,
            'stock' => 0,
            'price' => 8000,
            'sale_price' => 5000,
            'size' => 'L',
        ]);

        $product->load('variations');

        $this->assertSame(9000.0, $product->display_price);
        $this->assertSame(12000.0, $product->display_original_price);
        $this->assertTrue($product->display_has_active_offer);
    }

    public function test_product_returns_null_storefront_prices_when_no_available_variations_exist(): void
    {
        $product = Product::factory()->create();
        $color = ProductColor::factory()->create(['product_id' => $product->id]);

        ProductVariation::factory()->create([
            'product_id' => $product->id,
            'product_color_id' => $color->id,
            'stock' => 0,
            'price' => 12000,
            'sale_price' => 9000,
        ]);

        $product->load('variations');

        $this->assertNull($product->display_price);
        $this->assertNull($product->display_original_price);
        $this->assertFalse($product->display_has_active_offer);
    }
}
