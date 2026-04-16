<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use App\Services\CartService;
use Livewire\Component;
use Livewire\WithPagination;

class CatalogPage extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public ?int $categoryId = null;

    public string $sort = 'latest';

    public bool $offerOnly = false;

    public int $perPage = 12;

    public $categories;

    public function mount()
    {
        $this->categories = \Illuminate\Support\Facades\Cache::remember('categories', 3600, function () {
            return Category::select('id', 'name')->orderBy('name')->get();
        });
    }

    public function updatingCategoryId()
    {
        $this->resetPage();
    }

    public function updatingSort()
    {
        $this->resetPage();
    }

    public function updatingOfferOnly()
    {
        $this->resetPage();
    }

    protected function queryBuilder()
    {
        $query = Product::query()
            ->withStorefrontPricing()
            ->when($this->categoryId, function ($q) {
                $q->where('category_id', $this->categoryId);
            })
            ->when($this->offerOnly, function ($q) {
                $q->whereHas('variations', function ($variationQuery) {
                    $variationQuery
                        ->where('stock', '>', 0)
                        ->whereNotNull('sale_price')
                        ->whereColumn('sale_price', '<', 'price');
                });
            })
            ->whereHas('colors.variations', function ($q) {
                $q->where('stock', '>', 0);
            })
            ->with([
                'category',
                'variations:product_variations.id,product_variations.product_id,product_variations.product_color_id,product_variations.size,product_variations.stock,product_variations.price,product_variations.sale_price',
                'colors' => function ($q) {
                    $q->whereHas('variations', function ($v) {
                        $v->where('stock', '>', 0);
                    })
                        ->select('product_colors.id', 'product_colors.product_id', 'product_colors.name', 'product_colors.image', 'product_colors.position')
                        ->with([
                            'images:id,product_color_id,path,position',
                            'variations' => function ($v) {
                                $v->where('stock', '>', 0)
                                    ->select('product_variations.id', 'product_variations.product_color_id', 'product_variations.size');
                            },
                        ]);
                },
            ]);

        return match ($this->sort) {
            'price_asc' => $query->orderBy('storefront_display_price', 'asc'),
            'price_desc' => $query->orderBy('storefront_display_price', 'desc'),
            default => $query->latest(),
        };
    }

    public function getProductsProperty()
    {
        $products = $this->queryBuilder()->paginate($this->perPage);

        return $products;
    }

    public function render()
    {
        return view('livewire.catalog-page', [
            'products' => $this->products,
            'categories' => $this->categories,
        ]);
    }

    public function addToCart($productId, $variationId, CartService $cartService)
    {
        $success = $cartService->addToCart($productId, $variationId, 1);

        if ($success) {
            $this->dispatch('product-added');
            $this->dispatch('cart-updated');
        } else {
            $this->dispatch('product-add-error');
        }
    }
}
