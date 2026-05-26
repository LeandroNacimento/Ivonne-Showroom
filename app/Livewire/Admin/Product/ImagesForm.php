<?php

namespace App\Livewire\Admin\Product;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ImagesForm extends Component
{
    public array $colors = [];

    public array $existingImages = []; // [color => [{id, url, path}]]
    
    public array $persistedColorIds = []; // [color => id]

    public ?int $productId = null;

    public function mount(?Product $product = null): void
    {
        if ($product && $product->exists) {
            $this->productId = $product->id;
            $this->loadImages();
        }
    }

    protected function loadImages(): void
    {
        $product = Product::with('images.productColor', 'colors')->find($this->productId);
        if (!$product) return;

        // Group existing images by color name from the relation
        $this->existingImages = $product->images
            ->groupBy(fn ($img) => $img->productColor?->name ?? 'Sin color')
            ->map(fn ($imgs) => $imgs->map(fn ($img) => [
                'id' => $img->id,
                'url' => asset('storage/'.$img->path),
                'path' => $img->path,
            ])->values()->toArray())
            ->toArray();

        // Populate persistedColorIds mapping (normalized name => id)
        $this->persistedColorIds = $product->colors->mapWithKeys(function ($color) {
            return [mb_strtolower(trim($color->name), 'UTF-8') => $color->id];
        })->toArray();

        // Initialize colors from existing images if not already populated by sync
        if (empty($this->colors)) {
            $this->colors = array_keys($this->existingImages);
        }
    }

    /**
     * Called from the variations component whenever visible color names change.
     */
    #[\Livewire\Attributes\On('sync-image-colors')]
    public function syncColors(array $colors): void
    {
        $this->colors = array_filter(array_map('trim', $colors));
    }

    public function moveImage(int $imageId, string $direction): void
    {
        DB::transaction(function () use ($imageId, $direction) {
            $image = ProductImage::find($imageId);
            if (!$image) return;

            // 1. Get ordered collection
            $images = ProductImage::where('product_color_id', $image->product_color_id)
                ->orderBy('position')
                ->orderBy('id')
                ->get();

            $currentIndex = $images->search(fn($img) => $img->id === $image->id);
            if ($currentIndex === false) return;

            $targetIndex = $direction === 'left' ? $currentIndex - 1 : $currentIndex + 1;

            if ($targetIndex < 0 || $targetIndex >= $images->count()) return;

            // 2. Reorder in memory
            $imagesArray = $images->values()->all();
            $temp = $imagesArray[$currentIndex];
            $imagesArray[$currentIndex] = $imagesArray[$targetIndex];
            $imagesArray[$targetIndex] = $temp;

            // 3. Reindex sequentially and persist
            foreach ($imagesArray as $index => $img) {
                $img->position = $index;
                $img->save();
            }
        });

        $this->loadImages();
    }

    public function render()
    {
        return view('livewire.admin.product.images-form');
    }
}
