<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariation;
use Illuminate\Support\Facades\DB;

class OrderService
{
    protected OrderStatusTransitionHandler $handler;

    public function __construct(OrderStatusTransitionHandler $handler)
    {
        $this->handler = $handler;
    }

    public function create(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $clientId = $data['client_id'] ?? null;

            if (!$clientId && !empty($data['new_client_name'])) {
                $client = Client::create([
                    'name' => $data['new_client_name'],
                    'phone' => $data['new_client_phone'] ?? null,
                    'instagram' => $data['new_client_instagram'] ?? null,
                    'email' => $data['new_client_email'] ?? null,
                    'notes' => $data['new_client_notes'] ?? null,
                ]);
                $clientId = $client->id;
            }

            $total = 0;
            $itemsData = [];

            foreach ($data['items'] as $item) {
                // Lock row to prevent race conditions when checking stock
                $variation = ProductVariation::with(['productColor', 'product'])->lockForUpdate()->findOrFail($item['variation_id']);

                if ($item['quantity'] > $variation->stock) {
                    throw new \Exception("Stock insuficiente para el producto seleccionado.");
                }

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

            $total += (float) ($data['shipping_cost'] ?? 0);

            // Crear el pedido siempre como pendiente
            $order = Order::create([
                'client_id' => $clientId,
                'date' => $data['date'],
                'status' => Order::STATUS_PENDING,
                'payment_method' => $data['payment_method'],
                'delivery_type' => $data['delivery_type'],
                'shipping_cost' => (float) ($data['shipping_cost'] ?? 0),
                'total' => $total,
            ]);

            // Persistir relacion items
            foreach ($itemsData as $itemData) {
                $itemData['order_id'] = $order->id;
                OrderItem::create($itemData);
            }

            // Transicionar estado de ser necesario
            if ($data['status'] === Order::STATUS_RESERVED) {
                // Reload para tener todo instanciado antes del handler
                $order->load(['items.variation.productColor', 'items.product']);
                $this->handler->handle($order, Order::STATUS_PENDING, Order::STATUS_RESERVED);
                $order->update(['status' => Order::STATUS_RESERVED]);
            }

            return $order;
        });
    }
}
