<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Services\CartService;

class ProductPage extends Component
{
    public Product $product;
    public $relatedProducts;
    public $imagesByColor;
    public $sortedVariations;
    public $initialColor;

    public function mount($slug)
    {
        $this->product = Product::where('slug', $slug)
            ->with(['category', 'colors', 'variations.productColor'])
            ->firstOrFail();

        $this->relatedProducts = Product::where('category_id', $this->product->category_id)
            ->where('id', '!=', $this->product->id)
            ->with(['colors'])
            ->take(4)
            ->get();

        // Prepare sorted variations for Alpine
        $this->sortedVariations = $this->product->variations
            ->where('stock', '>', 0)
            ->sortBy(fn($v) => Product::SIZE_ORDER[strtoupper($v->size)] ?? 99)
            ->map(fn($v) => [
                'id' => $v->id,
                'color' => $v->productColor->name ?? 'Único',
                'size' => $v->size,
                'price' => $v->price,
                'stock' => $v->stock,
            ])
            ->values();

        // Group images by color for Alpine.js dynamic gallery
        $this->imagesByColor = [];
        foreach ($this->product->colors as $color) {
            if ($color->image) {
                $this->imagesByColor[$color->name] = [$color->image_url];
            }
        }

        // Initial active color
        $this->initialColor = $this->product->variations->where('stock', '>', 0)->first()?->productColor?->name
            ?? ($this->product->colors->first()?->name ?? 'Único');
    }

    public function addToCart($variationId, $quantity, CartService $cartService)
    {
        // Add to cart using the injected service
        $success = $cartService->addToCart($this->product->id, $variationId, $quantity);

        if ($success) {
            $this->dispatch('product-added', count: $cartService->getCart() ? count($cartService->getCart()) : 0);
            $this->dispatch('cart-updated');
        }
    }

    public function render()
    {
        return view('livewire.product-page');
    }
}
