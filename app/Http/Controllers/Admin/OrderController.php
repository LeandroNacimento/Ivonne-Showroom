<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('client')->latest('date');

        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from') && $request->date_from != '') {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to != '') {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $orders = $query->paginate(10);

        return view('admin.orders.index', compact('orders'));
    }

    public function create()
    {
        $clients = Client::orderBy('name')->get();


        return view('admin.orders.create', compact('clients'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'date' => 'required|date',
            'status' => 'required|string',
            'payment_method' => 'nullable|string',
            'delivery_type' => 'nullable|string',
            'shipping_cost' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variation_id' => 'required|exists:product_variations,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $total = 0;
            foreach ($request->items as $item) {
                $total += $item['quantity'] * $item['unit_price'];
            }
            $total += $request->shipping_cost ?? 0;

            $order = Order::create([
                'client_id' => $request->client_id,
                'date' => $request->date,
                'status' => $request->status,
                'payment_method' => $request->payment_method,
                'delivery_type' => $request->delivery_type,
                'shipping_cost' => $request->shipping_cost ?? 0,
                'total' => $total,
            ]);

            foreach ($request->items as $item) {
                // Fetch variation to get color/size info
                $variation = \App\Models\ProductVariation::find($item['variation_id']);
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variation_id'],
                    'color' => $variation->color,
                    'size' => $variation->size,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['quantity'] * $item['unit_price'],
                ]);

                // Deduct stock
                $variation->decrement('stock', $item['quantity']);
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
        $clients = Client::orderBy('name')->get();
        $order->load(['items.product']); // Load product for existing items to show name

        return view('admin.orders.edit', compact('order', 'clients'));
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'date' => 'required|date',
            'status' => 'required|string',
            'payment_method' => 'nullable|string',
            'delivery_type' => 'nullable|string',
            'shipping_cost' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.variation_id' => 'required|exists:product_variations,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $order) {
            // Restore stock from old items
            foreach ($order->items as $item) {
                if ($item->variation_id) {
                    \App\Models\ProductVariation::find($item->variation_id)->increment('stock', $item->quantity);
                }
            }
            
            // Delete old items
            $order->items()->delete();

            // Calculate new total
            $total = 0;
            foreach ($request->items as $item) {
                $total += $item['quantity'] * $item['unit_price'];
            }
            $total += $request->shipping_cost ?? 0;

            // Update Order
            $order->update([
                'client_id' => $request->client_id,
                'date' => $request->date,
                'status' => $request->status,
                'payment_method' => $request->payment_method,
                'delivery_type' => $request->delivery_type,
                'shipping_cost' => $request->shipping_cost ?? 0,
                'total' => $total,
            ]);

            // Create new items and deduct stock
            foreach ($request->items as $item) {
                $variation = \App\Models\ProductVariation::find($item['variation_id']);
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'variation_id' => $item['variation_id'],
                    'color' => $variation->color,
                    'size' => $variation->size,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['quantity'] * $item['unit_price'],
                ]);

                $variation->decrement('stock', $item['quantity']);
            }
        });

        return redirect()->route('admin.orders.index')->with('success', 'Pedido actualizado con éxito.');
    }

    public function destroy(Order $order)
    {
        // Restore stock
        foreach ($order->items as $item) {
            if ($item->variation_id) {
                \App\Models\ProductVariation::find($item->variation_id)->increment('stock', $item->quantity);
            }
        }
        
        $order->delete();
        return redirect()->route('admin.orders.index')->with('success', 'Pedido eliminado con éxito.');
    }
}
