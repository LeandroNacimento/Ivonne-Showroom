<?php

namespace App\Livewire\Admin\Orders;

use App\Models\Product;
use Livewire\Component;

class OrderProductSelector extends Component
{
    public $search = '';

    /**
     * El componente emite product-selected con los datos elegidos.
     * Al ser un evento nativo, burbujea y puede ser capturado por Alpine en el scope de la fila.
     */
    public function selectProduct($productId)
    {
        $product = Product::with([
            'variations' => function ($q) {
                $q->where('stock', '>', 0);
            },
            'variations.productColor'
        ])->find($productId);

        if ($product) {
            $variationsPayload = $product->variations->map(function ($v) use ($product) {
                return [
                    'id' => $v->id,
                    'color' => $v->productColor->name ?? 'N/A',
                    'size' => $v->size,
                    'stock' => $v->stock,
                    'price' => $v->price ?? $product->price,
                ];
            })->values()->toArray();

            $this->dispatch(
                'product-selected',
                product: [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->price
                ],
                variations: $variationsPayload
            );

            $this->search = '';
        }
    }

    public function render()
    {
        $products = [];

        if (strlen($this->search) >= 2) {
            $products = Product::where('name', 'like', '%' . $this->search . '%')
                ->whereHas('variations', function ($query) {
                    $query->where('stock', '>', 0);
                })
                ->with([
                    'variations' => function ($query) {
                        $query->where('stock', '>', 0);
                    }
                ])
                ->orderBy('name', 'asc')
                ->take(10)
                ->get();
        }

        return view('livewire.admin.orders.order-product-selector', [
            'products' => $products
        ]);
    }
}
