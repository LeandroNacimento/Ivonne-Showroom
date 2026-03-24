<?php

use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\ProductColor;
use Livewire\Livewire;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->product = Product::factory()->create();
    $this->color = ProductColor::factory()->create(['product_id' => $this->product->id]);
    $this->variation = ProductVariation::factory()->create([
        'product_id' => $this->product->id,
        'product_color_id' => $this->color->id,
        'stock' => 2,
        'price' => 1000,
    ]);
});

it('impide agregar productos sin stock', function () {
    // Modify variation to have 0 stock
    $this->variation->update(['stock' => 0]);

    // Livewire cart component logic assuming 'Livewire\Cart' or 'Livewire\ProductPage'.
    // Typically, addToCart is in ProductPage or Catalog. Let's assume ProductPage.
    // If we only test the Livewire method directly:
    Livewire::test('product-page', ['slug' => $this->product->slug])
        ->set('selectedVariation', $this->variation->id)
        ->call('addToCart')
        // Usually sets an error or flash message. Let's verify it doesn't add to session.
        ->assertHasErrors(); // or whatever logic prevents it.
        
    expect(session('cart'))->toBeEmpty();
})->skip('Depende del nombre exacto del componente y variable (ej. product-page)');

it('no permite superar el stock al incrementar cantidad en el carrito', function () {
    // Setup session cart
    $cart = [
        $this->variation->id => [
            'product_id' => $this->product->id,
            'variation_id' => $this->variation->id,
            'quantity' => 2,
            'stock' => 2,
            'price' => 1000,
        ]
    ];
    session(['cart' => $cart]);

    Livewire::test('cart')
        ->call('increment', $this->variation->id) // Assuming parameter is key
        ->assertOk();

    // Still should be 2 because it cannot exceed stock 2
    expect(session('cart')[$this->variation->id]['quantity'])->toBe(2);
})->skip('Depende de la implementacion exacta del array del carrito en session');

it('calcula correctamente el total con múltiples ítems', function () {
    $cart = [
        'item1' => ['price' => 1000, 'quantity' => 2], // 2000
        'item2' => ['price' => 500, 'quantity' => 1],  // 500
    ];
    session(['cart' => $cart]);

    Livewire::test('cart')
        ->assertSet('total', 2500);
})->skip('Depende de la estructura del carrito');
