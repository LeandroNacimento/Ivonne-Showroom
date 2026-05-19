<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use App\Services\CartService;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class CatalogPage extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    #[Url(as: 'category', except: '')]
    public ?string $category = null;

    public string $sort = 'latest';

    public bool $offerOnly = false;

    public int $perPage = 12;

    public $categories;

    public function mount()
    {
        $this->categories = \Illuminate\Support\Facades\Cache::remember('categories', 3600, function () {
            return Category::select('id', 'name', 'slug')->orderBy('name')->get();
        });

        $this->category = $this->normalizeCategorySlug($this->category);
    }

    public function updatingCategory()
    {
        $this->resetPage();
    }

    public function updatedCategory($value)
    {
        $this->category = $this->normalizeCategorySlug($value);
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
        $categoryId = $this->resolveCategoryId();

        $query = Product::query()
            ->withStorefrontPricing()
            ->when($categoryId, function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
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
                                    ->select('product_variations.id', 'product_variations.product_color_id', 'product_variations.size', 'product_variations.price', 'product_variations.sale_price', 'product_variations.stock');
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

    protected function normalizeCategorySlug(?string $slug): ?string
    {
        if ($slug === null) {
            return null;
        }

        $slug = trim($slug);

        if ($slug === '') {
            return null;
        }

        return $this->categories->contains('slug', $slug) ? $slug : null;
    }

    protected function resolveCategoryId(): ?int
    {
        $slug = $this->normalizeCategorySlug($this->category);

        if ($slug !== $this->category) {
            $this->category = $slug;
        }

        return $this->categories->firstWhere('slug', $slug)?->id;
    }
}
