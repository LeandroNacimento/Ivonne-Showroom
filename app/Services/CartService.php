<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Support\Facades\Session;

class CartService
{
    public function getCart()
    {
        return Session::get('cart', []);
    }

    public function count(): int
    {
        return count($this->getCart());
    }

    public function addToCart($productId, $variationId, $quantity = 1)
    {
        $cart = $this->getCart();
        $product = Product::with('images')->find($productId);
        $variation = ProductVariation::with('productColor')->find($variationId);

        if (!$product || !$variation || $variation->stock <= 0) {
            return false;
        }

        $cartKey = $productId . '-' . $variationId;
        $currentCartQuantity = isset($cart[$cartKey]) ? $cart[$cartKey]['quantity'] : 0;

        if ($currentCartQuantity + $quantity > $variation->stock) {
            return false; // Validar que la cantidad en carrito + nueva no supere el stock
        }

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += $quantity;
        } else {
            $cart[$cartKey] = [
                'product_id' => $product->id,
                'variation_id' => $variation->id,
                'name' => $product->name,
                'price' => $variation->price,
                'color' => $variation->productColor->name,
                'size' => $variation->size,
                'stock' => $variation->stock,
                'image' => $product->images->first() ? $product->images->first()->path : null,
                'quantity' => $quantity,
            ];
        }

        Session::put('cart', $cart);
        return true;
    }

    public function removeFromCart($cartKey)
    {
        $cart = $this->getCart();
        if (isset($cart[$cartKey])) {
            unset($cart[$cartKey]);
            Session::put('cart', $cart);
        }
    }

    public function updateQuantity($cartKey, $quantity)
    {
        $cart = $this->getCart();
        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] = max(1, $quantity);
            Session::put('cart', $cart);
        }
    }

    public function clearCart()
    {
        Session::forget('cart');
    }

    public function getTotal()
    {
        $cart = $this->getCart();
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        return $total;
    }

    public function getWhatsAppMessage()
    {
        $cart = $this->getCart();
        if (empty($cart)) {
            return '';
        }

        $message = "Hola IvonneShowroom, me gustaría realizar el siguiente pedido:\n\n";
        foreach ($cart as $item) {
            $message .= "- {$item['name']} ({$item['color']} - {$item['size']}) x {$item['quantity']} = $" . number_format($item['price'] * $item['quantity'], 0, ',', '.') . "\n";
        }
        $message .= "\nTotal: $" . number_format($this->getTotal(), 0, ',', '.');

        return urlencode($message);
    }
}
