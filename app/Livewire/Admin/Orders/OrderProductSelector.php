<?php

namespace App\Livewire\Admin\Orders;

use App\Models\ProductVariation;
use Livewire\Component;

class OrderProductSelector extends Component
{
    public $search = '';
    public $index;

    /**
     * El componente emite productSelected con el ID elegido hacia el componente padre.
     */
    public function selectProduct($variationId)
    {
        $variation = ProductVariation::with(['product', 'productColor'])->find($variationId);

        if ($variation) {
            $this->dispatch('productSelected', [
                'index' => $this->index,
                'product' => [
                    'id' => $variation->product->id,
                    'name' => $variation->product->name,
                ],
                'variation' => [
                    'id' => $variation->id,
                    'color' => $variation->productColor->name ?? 'N/A',
                    'size' => $variation->size,
                ],
                'price' => $variation->product->price
            ]);
            $this->search = '';
        }
    }

    public function render()
    {
        $variations = [];

        if (strlen($this->search) >= 2) {
            $variations = ProductVariation::with(['product', 'productColor'])
                ->whereHas('product', function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%');
                })
                ->where('stock', '>', 0)
                ->get()
                ->sortBy(function ($variation) {
                    return $variation->product->name;
                })
                ->take(10);
        }

        return view('livewire.admin.orders.order-product-selector', [
            'variations' => $variations
        ]);
    }
}
