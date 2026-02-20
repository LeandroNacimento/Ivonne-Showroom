<?php

namespace App\Livewire\Admin\Product;

use App\Models\Product;
use Livewire\Component;

class ImagesForm extends Component
{
    public array $colors = [];
    public array $existingImages = []; // [color => [{id, url, path}]]
    public ?int $productId = null;

    public function mount(?Product $product = null): void
    {
        if ($product && $product->exists) {
            $this->productId = $product->id;
            $product->load('images');

            // Group existing images by color
            $this->existingImages = $product->images
                ->groupBy('color')
                ->map(fn($imgs) => $imgs->map(fn($img) => [
                    'id' => $img->id,
                    'url' => asset('storage/' . $img->path),
                    'path' => $img->path,
                ])->values()->toArray())
                ->toArray();

            // Extract colors from existing images
            $this->colors = array_keys($this->existingImages);
        }
    }

    /**
     * Called from parent Blade via Alpine dispatch when colors change.
     * Receives color names from VariationsForm hidden inputs.
     */
    #[\Livewire\Attributes\On('sync-image-colors')]
    public function syncColors(array $colors): void
    {
        $this->colors = array_filter(array_map('trim', $colors));
    }

    public function render()
    {
        return view('livewire.admin.product.images-form');
    }
}
