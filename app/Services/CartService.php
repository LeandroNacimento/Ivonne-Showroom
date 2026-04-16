<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Support\Facades\Session;

class CartService
{
    public function getCart(): array
    {
        $cart = $this->getSessionCart();

        if ($cart === []) {
            return [];
        }

        $variations = ProductVariation::with(['product.images', 'productColor.images'])
            ->whereIn('id', collect($cart)->pluck('variation_id')->filter()->all())
            ->get()
            ->keyBy('id');

        foreach ($cart as $cartKey => &$item) {
            $variation = $variations->get($item['variation_id'] ?? null);

            if (! $variation) {
                continue;
            }

            $item = $this->buildCartItemPayload($variation, $item['quantity'] ?? 1);
        }
        unset($item);

        return $cart;
    }

    public function count(): int
    {
        return count($this->getSessionCart());
    }

    public function addToCart($productId, $variationId, $quantity = 1): bool
    {
        $cart = $this->getSessionCart();
        $product = Product::find($productId);
        $variation = ProductVariation::with(['product.images', 'productColor.images'])->find($variationId);

        if (! $product || ! $variation || $variation->stock <= 0) {
            return false;
        }

        $cartKey = $productId.'-'.$variationId;
        $currentCartQuantity = isset($cart[$cartKey]) ? (int) ($cart[$cartKey]['quantity'] ?? 0) : 0;

        if ($currentCartQuantity + $quantity > $variation->stock) {
            return false;
        }

        $cart[$cartKey] = $this->buildCartItemPayload(
            $variation,
            $currentCartQuantity + $quantity
        );

        Session::put('cart', $cart);

        return true;
    }

    public function removeFromCart($cartKey): void
    {
        $cart = $this->getSessionCart();

        if (isset($cart[$cartKey])) {
            unset($cart[$cartKey]);
            Session::put('cart', $cart);
        }
    }

    public function updateQuantity($cartKey, $quantity): void
    {
        $cart = $this->getSessionCart();

        if (! isset($cart[$cartKey])) {
            return;
        }

        $variation = ProductVariation::find($cart[$cartKey]['variation_id']);
        $maxStock = $variation?->stock ?? null;
        $safeQuantity = max(1, (int) $quantity);

        if ($maxStock !== null) {
            $safeQuantity = min($safeQuantity, $maxStock);
        }

        $cart[$cartKey]['quantity'] = $safeQuantity;
        Session::put('cart', $cart);
    }

    public function clearCart(): void
    {
        Session::forget('cart');
    }

    public function getTotal(): float
    {
        return collect($this->getCart())->sum(function (array $item) {
            return ((float) ($item['unit_price'] ?? 0)) * ((int) ($item['quantity'] ?? 0));
        });
    }

    public function getWhatsAppMessage(): string
    {
        $cart = $this->getCart();

        if ($cart === []) {
            return '';
        }

        $message = "Hola IvonneShowroom, me gustar\xC3\xADa realizar el siguiente pedido:\n\n";

        foreach ($cart as $item) {
            $lineTotal = ((float) ($item['unit_price'] ?? 0)) * ((int) ($item['quantity'] ?? 0));

            $message .= "- {$item['name']} ({$item['color']} - {$item['size']}) x {$item['quantity']} = $".number_format($lineTotal, 0, ',', '.')."\n";
        }

        $message .= "\nTotal: $".number_format($this->getTotal(), 0, ',', '.');

        return urlencode($message);
    }

    private function getSessionCart(): array
    {
        return Session::get('cart', []);
    }

    private function buildCartItemPayload(ProductVariation $variation, int $quantity): array
    {
        return [
            'product_id' => $variation->product_id,
            'variation_id' => $variation->id,
            'name' => $variation->product?->name ?? 'Producto',
            'unit_price' => (float) $variation->effective_price,
            'original_price' => (float) $variation->original_price,
            'has_active_offer' => $variation->has_active_offer,
            'color' => $variation->productColor?->name ?? 'N/A',
            'size' => $variation->size,
            'stock' => $variation->stock,
            'image' => $variation->cart_image,
            'quantity' => $quantity,
        ];
    }
}
