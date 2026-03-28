<?php

namespace App\Livewire;

use App\Services\CartService;
use Livewire\Attributes\On;
use Livewire\Component;

class CartBadge extends Component
{
    public int $totalItems = 0;

    public function mount(CartService $cartService)
    {
        $this->totalItems = $cartService->count();
    }

    #[On('cart-updated')]
    public function updateCart(CartService $cartService)
    {
        $this->totalItems = $cartService->count();
    }

    public function render()
    {
        return view('livewire.cart-badge');
    }
}
