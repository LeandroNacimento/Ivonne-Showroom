<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOrderRequest;
use App\Http\Requests\Admin\UpdateOrderRequest;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderStatusTransitionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{


    public function index(Request $request)
    {
        return view('admin.orders.index');
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get();
        return view('admin.orders.create', compact('clients'));
    }

    public function store(StoreOrderRequest $request, OrderStatusTransitionHandler $handler)
    {
        DB::transaction(function () use ($request, $handler) {
            $clientId = $request->client_id;

            if (!$clientId && $request->filled('new_client_name')) {
                $client = \App\Models\Client::create([
                    'name' => $request->new_client_name,
                    'phone' => $request->new_client_phone,
                    'instagram' => $request->new_client_instagram,
                    'email' => $request->new_client_email,
                    'notes' => $request->new_client_notes,
                ]);
                $clientId = $client->id;
            }

            // Asegurar precios usando el backend
            $total = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $variation = \App\Models\ProductVariation::with(['productColor', 'product'])->findOrFail($item['variation_id']);

                $price = collect([$variation->price, $variation->product?->price])->filter()->first() ?? 0;
                $subtotal = $price * $item['quantity'];

                $itemsData[] = [
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variation_id'],
                    'color' => $variation->productColor?->name ?? 'N/A',
                    'size' => $variation->size,
                    'quantity' => $item['quantity'],
                    'unit_price' => $price,
                    'subtotal' => $subtotal,
                ];

                $total += $subtotal;
            }

            $total += (float) ($request->shipping_cost ?? 0);

            // Crear el pedido siempre como "pendiente" primero
            $order = Order::create([
                'client_id' => $clientId,
                'date' => $request->date,
                'status' => Order::STATUS_PENDING,
                'payment_method' => $request->payment_method,
                'delivery_type' => $request->delivery_type,
                'shipping_cost' => (float) ($request->shipping_cost ?? 0),
                'total' => $total,
            ]);

            // Persistir ítems
            foreach ($itemsData as $data) {
                $data['order_id'] = $order->id;
                OrderItem::create($data);
            }

            // Si el estado deseado es "reservado", ejecutar la transición vía handler
            if ($request->status === Order::STATUS_RESERVED) {
                $order->load(['items.variation.productColor', 'items.product']);
                $handler->handle($order, Order::STATUS_PENDING, Order::STATUS_RESERVED);
                $order->update(['status' => Order::STATUS_RESERVED]);
            }
        });

        return redirect()->route('admin.orders.index')->with('success', 'Pedido creado con éxito.');
    }

    public function show(Order $order)
    {
        $order->load(['client', 'items.product']);
        return view('admin.orders.show', compact('order'));
    }

    public function edit(Order $order)
    {
        // Bloquear edición si el pedido está en estado terminal
        if (in_array($order->status, Order::TERMINAL_STATES)) {
            return redirect()->route('admin.orders.show', $order)
                ->with('error', "El pedido está en estado '{$order->status}' y no puede editarse.");
        }

        $clients = Client::orderBy('name')->get();
        $order->load(['items.product', 'items.variation']);

        return view('admin.orders.edit', compact('order', 'clients'));
    }

    public function update(UpdateOrderRequest $request, Order $order, OrderStatusTransitionHandler $handler)
    {
        // Bloquear actualización si ya está en estado terminal
        if (in_array($order->status, Order::TERMINAL_STATES)) {
            abort(403, "No se puede modificar un pedido en estado '{$order->status}'.");
        }

        DB::transaction(function () use ($request, $order, $handler) {
            $clientId = $request->client_id;

            if (!$clientId && $request->filled('new_client_name')) {
                $client = \App\Models\Client::create([
                    'name' => $request->new_client_name,
                    'phone' => $request->new_client_phone,
                    'instagram' => $request->new_client_instagram,
                    'email' => $request->new_client_email,
                    'notes' => $request->new_client_notes,
                ]);
                $clientId = $client->id;
            }

            $oldStatus = $order->status;
            $newStatus = $request->status;

            if ($oldStatus === Order::STATUS_PENDING) {
                // Edición completa permitida: re-crear ítems y gestionar transición de estado
                $order->load(['items.variation.productColor', 'items.product']);
                $order->items()->delete();
                $order->unsetRelation('items');

                $total = 0;
                $itemsData = [];

                foreach ($request->items as $item) {
                    $variation = \App\Models\ProductVariation::with(['productColor', 'product'])->findOrFail($item['variation_id']);

                    $price = collect([$variation->price, $variation->product?->price])->filter()->first() ?? 0;
                    $subtotal = $price * $item['quantity'];

                    $itemsData[] = [
                        'product_id' => $item['product_id'],
                        'variation_id' => $item['variation_id'],
                        'color' => $variation->productColor?->name ?? 'N/A',
                        'size' => $variation->size,
                        'quantity' => $item['quantity'],
                        'unit_price' => $price,
                        'subtotal' => $subtotal,
                    ];

                    $total += $subtotal;
                }

                $total += (float) ($request->shipping_cost ?? 0);

                $order->update([
                    'client_id' => $clientId,
                    'date' => $request->date,
                    'status' => Order::STATUS_PENDING, // Se mantiene pendiente hasta pasar por handler
                    'payment_method' => $request->payment_method,
                    'delivery_type' => $request->delivery_type,
                    'shipping_cost' => (float) ($request->shipping_cost ?? 0),
                    'total' => $total,
                ]);

                foreach ($itemsData as $data) {
                    $data['order_id'] = $order->id;
                    OrderItem::create($data);
                }

                // Ejecutar transición de estado si cambió
                if ($oldStatus !== $newStatus) {
                    $order->load(['items.variation.productColor', 'items.product']);
                    $handler->handle($order, Order::STATUS_PENDING, $newStatus);
                    $order->update(['status' => $newStatus]);
                }
            } elseif ($oldStatus === Order::STATUS_RESERVED) {
                // Ítems bloqueados: solo actualizar campos de cabecera y estado
                $total = $order->items->sum('subtotal') + (float) ($request->shipping_cost ?? 0);

                $order->update([
                    'client_id' => $clientId,
                    'date' => $request->date,
                    'payment_method' => $request->payment_method,
                    'delivery_type' => $request->delivery_type,
                    'shipping_cost' => (float) ($request->shipping_cost ?? 0),
                    'total' => $total,
                ]);

                // Ejecutar transición de estado si cambió
                if ($oldStatus !== $newStatus) {
                    $order->load(['items.variation.productColor', 'items.product']);
                    $handler->handle($order, Order::STATUS_RESERVED, $newStatus);
                    $order->update(['status' => $newStatus]);
                }
            }
        });

        return redirect()->route('admin.orders.index')->with('success', 'Pedido actualizado con éxito.');
    }

    public function destroy(Order $order, OrderStatusTransitionHandler $handler)
    {
        // Bloquear eliminación si está en estado terminal
        if (in_array($order->status, Order::TERMINAL_STATES)) {
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
