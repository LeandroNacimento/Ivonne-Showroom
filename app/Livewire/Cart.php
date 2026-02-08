<?php

namespace App\Livewire;

use App\Services\CartService;
use Livewire\Component;

class Cart extends Component
{
    public $cart = [];
    public $total = 0;
    public $whatsappMessage = '';

    protected $listeners = ['cartUpdated' => 'loadCart'];

    public function mount(CartService $cartService)
    {
        $this->loadCart($cartService);
    }

    public function loadCart(CartService $cartService)
    {
        $this->cart = $cartService->getCart();
        $this->total = $cartService->getTotal();
        $this->whatsappMessage = $cartService->getWhatsAppMessage();
    }

    public function updateQuantity($cartKey, $quantity, CartService $cartService)
    {
        if ($quantity < 1) {
            return;
        }
        $cartService->updateQuantity($cartKey, $quantity);
        $this->loadCart($cartService);
        $this->dispatch('cart-updated'); // Optional: for other components
    }

    public function increment($cartKey, CartService $cartService)
    {
        if (isset($this->cart[$cartKey])) {
            $this->updateQuantity($cartKey, $this->cart[$cartKey]['quantity'] + 1, $cartService);
        }
    }

    public function decrement($cartKey, CartService $cartService)
    {
        if (isset($this->cart[$cartKey]) && $this->cart[$cartKey]['quantity'] > 1) {
            $this->updateQuantity($cartKey, $this->cart[$cartKey]['quantity'] - 1, $cartService);
        }
    }

    public function removeFromCart($cartKey, CartService $cartService)
    {
        $cartService->removeFromCart($cartKey);
        $this->loadCart($cartService);
        $this->dispatch('cart-updated');
    }

    public function render()
    {
        return view('livewire.cart');
    }
}
