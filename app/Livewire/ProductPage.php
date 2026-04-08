<?php

namespace App\Livewire;

use App\Models\Product;
use App\Services\CartService;
use Livewire\Component;

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
            ->with(['category', 'colors.images', 'variations.productColor'])
            ->firstOrFail();

        $this->relatedProducts = Product::where('category_id', $this->product->category_id)
            ->where('id', '!=', $this->product->id)
            ->with(['colors.images'])
            ->take(4)
            ->get();

        $this->sortedVariations = $this->product
            ->sortVariationCollectionBySize($this->product->variations->where('stock', '>', 0))
            ->map(fn ($variation) => [
                'id' => $variation->id,
                'color' => $variation->productColor->name ?? 'Único',
                'size_label' => Product::presentSize($variation->size),
                'price' => $variation->price,
                'stock' => $variation->stock,
            ])
            ->values();

        $this->imagesByColor = [];
        foreach ($this->product->colors as $color) {
            $this->imagesByColor[$color->name] = $color->public_gallery_urls;
        }

        $requestedColorSlug = request()->query('color');

        if ($requestedColorSlug) {
            $requestedVariation = $this->sortedVariations->first(function ($variation) use ($requestedColorSlug) {
                return \Illuminate\Support\Str::slug($variation['color']) === $requestedColorSlug && $variation['stock'] > 0;
            });

            if ($requestedVariation) {
                $this->initialColor = $requestedVariation['color'];
            }
        }

        if (! isset($this->initialColor)) {
            $this->initialColor = $this->product->variations->where('stock', '>', 0)->first()?->productColor?->name
                ?? ($this->product->colors->first()?->name ?? 'Único');
        }
    }

    public function addToCart($variationId, $quantity, CartService $cartService)
    {
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
