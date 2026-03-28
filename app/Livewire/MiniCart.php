<?php

namespace App\Livewire;

use App\Services\CartService;
use Livewire\Attributes\On;
use Livewire\Component;

class MiniCart extends Component
{
    public $itemCount = 0;

    public $total = 0;

    public function mount(CartService $cartService)
    {
        $this->updateCartData($cartService);
    }

    #[On('cart-updated')]
    public function updateCartData(CartService $cartService)
    {
        $cart = $cartService->getCart();
        $this->itemCount = collect($cart)->sum('quantity');
        $this->total = $cartService->getTotal();
    }

    public function render()
    {
        return view('livewire.mini-cart');
    }
}
