<?php

namespace App\Livewire;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class CatalogPage extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public ?int $categoryId = null;
    public string $sort = 'latest';
    public int $perPage = 12;

    public function updatingCategoryId()
    {
        $this->resetPage();
    }

    public function updatingSort()
    {
        $this->resetPage();
    }

    protected function queryBuilder()
    {
        $query = Product::query()
            ->when($this->categoryId, function ($q) {
                $q->where('category_id', $this->categoryId);
            })
            ->whereHas('colors.variations', function ($q) {
                $q->where('stock', '>', 0);
            })
            ->withMin([
                'colors.variations as variations_min_price' => function ($q) {
                    $q->where('stock', '>', 0);
                }
            ], 'price')
            ->with([
                'colors' => function ($q) {
                    $q->whereHas('variations', function ($v) {
                        $v->where('stock', '>', 0);
                    })
                        ->select('id', 'product_id', 'name', 'hex_code')
                        ->with([
                            'variations' => function ($v) {
                                $v->where('stock', '>', 0)
                                    ->select('id', 'product_color_id', 'size');
                            },
                            'mainImage'
                        ]);
                }
            ]);

        return match ($this->sort) {
            'price_asc' => $query->orderBy('variations_min_price', 'asc'),
            'price_desc' => $query->orderBy('variations_min_price', 'desc'),
            default => $query->latest(),
        };
    }

    public function getProductsProperty()
    {
        return $this->queryBuilder()->paginate($this->perPage);
    }

    public function render()
    {
        return view('livewire.catalog-page', [
            'products' => $this->products,
        ]);
    }
}
