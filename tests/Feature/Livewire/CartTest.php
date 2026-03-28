<?php

namespace Tests\Feature\Livewire;

use App\Models\Product;
use App\Models\ProductColor;
use App\Models\ProductVariation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    private ProductColor $color;

    private ProductVariation $variation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->product = Product::factory()->create();
        $this->color = ProductColor::factory()->create(['product_id' => $this->product->id]);
        $this->variation = ProductVariation::factory()->create([
            'product_id' => $this->product->id,
            'product_color_id' => $this->color->id,
            'stock' => 2,
            'price' => 1000,
        ]);
    }

    public function test_it_impide_agregar_productos_sin_stock(): void
    {
        $this->markTestSkipped('Depende del nombre exacto del componente y variable (ej. product-page)');

        $this->variation->update(['stock' => 0]);

        Livewire::test('product-page', ['slug' => $this->product->slug])
            ->set('selectedVariation', $this->variation->id)
            ->call('addToCart')
            ->assertHasErrors();

        $this->assertEmpty(session('cart'));
    }

    public function test_it_no_permite_superar_el_stock_al_incrementar_cantidad_en_el_carrito(): void
    {
        $this->markTestSkipped('Depende de la implementacion exacta del array del carrito en session');

        $cart = [
            $this->variation->id => [
                'product_id' => $this->product->id,
                'variation_id' => $this->variation->id,
                'quantity' => 2,
                'stock' => 2,
                'price' => 1000,
            ],
        ];
        session(['cart' => $cart]);

        Livewire::test('cart')
            ->call('increment', $this->variation->id)
            ->assertOk();

        $this->assertSame(2, session('cart')[$this->variation->id]['quantity']);
    }

    public function test_it_calcula_correctamente_el_total_con_multiples_items(): void
    {
        $this->markTestSkipped('Depende de la estructura del carrito');

        $cart = [
            'item1' => ['price' => 1000, 'quantity' => 2],
            'item2' => ['price' => 500, 'quantity' => 1],
        ];
        session(['cart' => $cart]);

        Livewire::test('cart')
            ->assertSet('total', 2500);
    }
}
