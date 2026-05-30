<?php

namespace App\Livewire\Admin\Products;

use App\Models\Product;
use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;

class ProductList extends Component
{
    use WithPagination;

    public $search = '';
    public $categoryFilter = '';
    public $stockFilter = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter()
    {
        $this->resetPage();
    }

    public function updatingStockFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $products = Product::query()
            ->with(['category', 'images', 'variations'])
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%'.$this->search.'%');
            })
            ->when($this->categoryFilter, function ($query) {
                $query->where('category_id', $this->categoryFilter);
            })
            ->when($this->stockFilter, function ($query) {
                if ($this->stockFilter === 'in_stock') {
                    $query->whereHas('variations', fn($q) => $q->where('stock', '>', 0));
                } elseif ($this->stockFilter === 'out_of_stock') {
                    $query->whereDoesntHave('variations', fn($q) => $q->where('stock', '>', 0));
                } elseif ($this->stockFilter === 'low_stock') {
                    $query->whereHas('variations', fn($q) => $q->where('stock', '>', 0)->where('stock', '<=', 5));
                }
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('livewire.admin.products.product-list', [
            'products' => $products,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }
}
