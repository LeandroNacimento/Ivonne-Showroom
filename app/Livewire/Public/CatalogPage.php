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

        $query = Product::with([
            'category',
            'images',
            'variations'
        ])->withMin('variations', 'price')->withSum('variations', 'stock');

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
                $q->whereHas('productColor', function ($qColor) {
                    $qColor->whereIn('name', $this->colors);
                })->where('stock', '>', 0);
            });
        } elseif (!$supportsColor) {
            $this->colors = [];
        }

        // Sorting — use the dynamically added aggregate column variations_min_price
        $query = match ($this->sort) {
            'price_asc' => $query->orderByRaw('COALESCE(variations_min_price, 0) ASC')->orderBy('id', 'desc'),
            'price_desc' => $query->orderByRaw('COALESCE(variations_min_price, 0) DESC')->orderBy('id', 'desc'),
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
            $availableColors = \App\Models\ProductColor::whereHas('variations', function ($q) use ($currentCategory) {
                $q->where('stock', '>', 0);
                if ($currentCategory) {
                    $q->whereHas('product', function ($q2) use ($currentCategory) {
                        $q2->where('category_id', $currentCategory->id);
                    });
                }
            })
                ->select('name')
                ->distinct()
                ->orderBy('name')
                ->pluck('name');
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
