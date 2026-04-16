<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOrderRequest;
use App\Http\Requests\Admin\UpdateOrderRequest;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderService;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        return view('admin.orders.index');
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get();

        return view('admin.orders.create', compact('clients'));
    }

    public function store(StoreOrderRequest $request, OrderService $orderService)
    {
        $orderService->create($request->validated());

        return redirect()->route('admin.orders.index')->with('success', 'Pedido creado con éxito.');
    }

    public function show(Order $order)
    {
        $order->load(['client', 'items.product']);

        return view('admin.orders.show', compact('order'));
    }

    public function edit(Order $order, OrderService $orderService)
    {
        // Bloquear edición si el pedido está en estado terminal
        if ($order->isTerminal()) {
            return redirect()->route('admin.orders.show', $order)
                ->with('error', "El pedido está en estado '{$order->status}' y no puede editarse.");
        }

        $clients = Client::orderBy('name')->get();
        $order->load(['items.product', 'items.variation.productColor']);
        $existingItems = $order->items->map(function (OrderItem $item) {
            $initialVariationOption = null;

            if ($item->variation) {
                $color = $item->variation->productColor?->name ?? 'N/A';
                $size = $item->variation->size ?? 'ÚNICO';
                $stock = $item->variation->stock ?? 0;
                $separator = $color !== 'N/A' && $size !== 'ÚNICO' ? ' - ' : '';
                $sizeLabel = $size !== 'ÚNICO' ? $size : '';

                $initialVariationOption = [
                    'id' => $item->variation->id,
                    'color' => $color,
                    'size' => $size,
                    'stock' => $stock,
                    'effective_price' => (float) $item->variation->effective_price,
                    'label' => "{$color}{$separator}{$sizeLabel} (Stock: {$stock})",
                    'missing' => false,
                ];
            } elseif ($item->variation_id) {
                $color = $item->color ?? 'N/A';
                $size = $item->size ?? 'ÚNICO';
                $separator = $color !== 'N/A' && $size !== 'ÚNICO' ? ' - ' : '';
                $sizeLabel = $size !== 'ÚNICO' ? $size : '';

                $initialVariationOption = [
                    'id' => null,
                    'color' => $color,
                    'size' => $size,
                    'stock' => null,
                    'effective_price' => (float) $item->unit_price,
                    'label' => "{$color}{$separator}{$sizeLabel}",
                    'missing' => true,
                ];
            }

            return [
                'product_id' => $item->product_id,
                'product' => $item->product ? [
                    'id' => $item->product->id,
                    'name' => $item->product->name,
                ] : null,
                'variation_id' => $item->variation_id,
                'variation' => $item->variation ? [
                    'id' => $item->variation->id,
                    'size' => $item->variation->size,
                    'stock' => $item->variation->stock,
                    'effective_price' => (float) $item->variation->effective_price,
                    'original_price' => (float) $item->variation->original_price,
                    'has_active_offer' => $item->variation->has_active_offer,
                    'product_color' => $item->variation->productColor ? [
                        'name' => $item->variation->productColor->name,
                    ] : null,
                ] : null,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
                'initial_variation_option' => $initialVariationOption,
            ];
        })->values();

        $statusOptions = $orderService->availableStatusesFor($order);

        return view('admin.orders.edit', compact('order', 'clients', 'existingItems', 'statusOptions'));
    }

    public function update(UpdateOrderRequest $request, Order $order, OrderService $orderService)
    {
        // Bloquear actualización si ya está en estado terminal
        if ($order->isTerminal()) {
            abort(403, "No se puede modificar un pedido en estado '{$order->status}'.");
        }

        DB::transaction(function () use ($request, $order, $orderService) {
            $oldStatus = $order->status;
            $newStatus = $request->status;

            if ($oldStatus === Order::STATUS_PENDING) {
                // Edición completa permitida: re-crear cliente/ítems/snapshots/total
                $orderService->rebuildPendingOrder($order, $request->validated());

                // Ejecutar transición de estado si cambió
                if ($oldStatus !== $newStatus) {
                    $orderService->transitionStatus($order, $newStatus);
                }
            } elseif ($oldStatus === Order::STATUS_RESERVED) {
                // Ítems bloqueados: solo actualizar cabecera; la transición sigue separada.
                $orderService->updateReservedOrderHeader($order, $request->validated());

                // Ejecutar transición de estado si cambió
                if ($oldStatus !== $newStatus) {
                    $orderService->transitionStatus($order, $newStatus);
                }
            }
        });

        return redirect()->route('admin.orders.index')->with('success', 'Pedido actualizado con éxito.');
    }

    public function destroy(Order $order)
    {
        // Bloquear eliminación si está en estado terminal
        if ($order->isTerminal()) {
            return redirect()->route('admin.orders.index')
                ->with('error', "No se puede eliminar un pedido en estado '{$order->status}'.");
        }

        // Bloquear eliminación si está en reservado (exigir cancelar primero)
        if ($order->status === Order::STATUS_RESERVED) {
            return redirect()->route('admin.orders.index')
                ->with('error', 'Para eliminar un pedido reservado primero debe cancelarlo (así se devuelve el stock).');
        }

        // Solo se elimina si está en pendiente (sin stock comprometido)
        DB::transaction(function () use ($order) {
            $order->items()->delete();
            $order->delete();
        });

        return redirect()->route('admin.orders.index')->with('success', 'Pedido eliminado con éxito.');
    }
}
