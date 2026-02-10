<?php

namespace App\Livewire\Public;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariation;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class CatalogPage extends Component
{
    use WithPagination;

    #[Url]
    public ?string $category = null;

    #[Url]
    public array $sizes = [];

    #[Url]
    public array $colors = [];

    #[Url]
    public string $sort = 'newest';

    public function updatedCategory()
    {
        $this->resetPage();
    }

    public function updatedSizes()
    {
        $this->resetPage();
    }

    public function updatedColors()
    {
        $this->resetPage();
    }

    public function updatedSort()
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        $this->reset(['category', 'sizes', 'colors', 'sort']);
        $this->resetPage();
    }

    public function render()
    {
        $query = Product::with('category', 'images', 'variations');

        // Filter by Category
        if ($this->category) {
            $query->whereHas('category', function ($q) {
                $q->where('slug', $this->category);
            });
        }

        // Filter by Sizes
        if (!empty($this->sizes)) {
            $query->whereHas('variations', function ($q) {
                $q->whereIn('size', $this->sizes)->where('stock', '>', 0);
            });
        }

        // Filter by Colors
        if (!empty($this->colors)) {
            $query->whereHas('variations', function ($q) {
                $q->whereIn('color', $this->colors)->where('stock', '>', 0);
            });
        }

        // Sorting
        $query = match ($this->sort) {
            'price_asc' => $query->orderBy('price', 'asc')->orderBy('id', 'desc'),
            'price_desc' => $query->orderBy('price', 'desc')->orderBy('id', 'desc'),
            default => $query->orderBy('created_at', 'desc')->orderBy('id', 'desc'),
        };

        $products = $query->paginate(12);

        // Sidebar data
        $categories = Category::withCount('products')->get();

        $baseVarQuery = ProductVariation::where('stock', '>', 0);

        if ($this->category) {
            $baseVarQuery->whereHas('product.category', function ($q) {
                $q->where('slug', $this->category);
            });
        }

        $availableSizes = (clone $baseVarQuery)->distinct()->orderBy('size')->pluck('size');
        $availableColors = (clone $baseVarQuery)->distinct()->orderBy('color')->pluck('color');

        return view('livewire.public.catalog-page', [
            'products' => $products,
            'categories' => $categories,
            'availableSizes' => $availableSizes,
            'availableColors' => $availableColors,
        ]);
    }
}
