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
        $currentCategory = null;

        $query = Product::with('category', 'images', 'variations');

        // Filter by Category
        if ($this->category) {
            $currentCategory = Category::where('slug', $this->category)->first();
            if ($currentCategory) {
                $query->where('category_id', $currentCategory->id);
            }
        }

        // Determine supported attributes
        $supportsSize = $currentCategory ? $currentCategory->supports_size : true;
        $supportsColor = $currentCategory ? $currentCategory->supports_color : true;

        // Filter by Sizes (only if supported)
        if ($supportsSize && !empty($this->sizes)) {
            $query->whereHas('variations', function ($q) {
                $q->whereIn('size', $this->sizes)->where('stock', '>', 0);
            });
        } elseif (!$supportsSize) {
            $this->sizes = [];
        }

        // Filter by Colors (only if supported)
        if ($supportsColor && !empty($this->colors)) {
            $query->whereHas('variations', function ($q) {
                $q->whereIn('color', $this->colors)->where('stock', '>', 0);
            });
        } elseif (!$supportsColor) {
            $this->colors = [];
        }

        // Sorting — price now lives in product_variations, use subquery
        $minPriceSub = ProductVariation::selectRaw('COALESCE(MIN(price), 0)')
            ->whereColumn('product_id', 'products.id');

        $query = match ($this->sort) {
            'price_asc' => $query->orderBy($minPriceSub)->orderBy('id', 'desc'),
            'price_desc' => $query->orderByDesc($minPriceSub)->orderBy('id', 'desc'),
            default => $query->orderBy('created_at', 'desc')->orderBy('id', 'desc'),
        };

        $products = $query->paginate(12);

        // Sidebar data
        $categories = Category::withCount('products')->get();

        // Load available filters dynamically based on current selection and support
        $availableSizes = collect();
        $availableColors = collect();

        // Base query for variations, scoped to current category if selected
        $baseVarQuery = ProductVariation::query()
            ->where('stock', '>', 0);

        if ($currentCategory) {
            $baseVarQuery->whereHas('product', function ($q) use ($currentCategory) {
                $q->where('category_id', $currentCategory->id);
            });
        }

        if ($supportsSize) {
            $availableSizes = (clone $baseVarQuery)
                ->select('size')
                ->distinct()
                ->whereNotNull('size')
                ->where('size', '!=', '')
                ->orderBy('size')
                ->pluck('size');
        }

        if ($supportsColor) {
            $availableColors = (clone $baseVarQuery)
                ->select('color')
                ->distinct()
                ->whereNotNull('color')
                ->where('color', '!=', '')
                ->orderBy('color')
                ->pluck('color');
        }

        return view('livewire.public.catalog-page', [
            'products' => $products,
            'categories' => $categories,
            'availableSizes' => $availableSizes,
            'availableColors' => $availableColors,
            'currentCategory' => $currentCategory,
        ]);
    }
}
