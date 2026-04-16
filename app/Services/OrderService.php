<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Order;
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
            $rebuiltOrder = $this->buildPendingOrderPayload($data);

            // Crear el pedido siempre como pendiente
            $order = Order::create([
                ...$rebuiltOrder['attributes'],
                'status' => Order::STATUS_PENDING,
            ]);

            $this->persistOrderItems($order, $rebuiltOrder['items']);

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

    /**
     * Rebuild the editable content of a pending order.
     * Status transitions must be handled separately by the caller.
     */
    public function rebuildPendingOrder(Order $order, array $data): Order
    {
        $rebuiltOrder = $this->buildPendingOrderPayload($data);

        $order->items()->delete();
        $order->unsetRelation('items');
        $order->update($rebuiltOrder['attributes']);
        $this->persistOrderItems($order, $rebuiltOrder['items']);

        return $order;
    }

    /**
     * Update the editable header fields of a reserved order.
     * Item mutations and status transitions must be handled separately by the caller.
     */
    public function updateReservedOrderHeader(Order $order, array $data): Order
    {
        $shippingCost = (float) ($data['shipping_cost'] ?? 0);
        $itemsTotal = (float) $order->items()->sum('subtotal');

        $order->update([
            'client_id' => $this->resolveClientId($data),
            'date' => $data['date'],
            'payment_method' => $data['payment_method'],
            'delivery_type' => $data['delivery_type'],
            'shipping_cost' => $shippingCost,
            'total' => $itemsTotal + $shippingCost,
        ]);

        return $order;
    }

    private function buildPendingOrderPayload(array $data): array
    {
        if (empty($data['items'])) {
            throw new \Exception('El pedido debe contener al menos un producto.');
        }

        $itemsData = [];
        $total = 0;

        foreach ($data['items'] as $item) {
            // Lock row to prevent race conditions when checking stock
            $variation = ProductVariation::with(['productColor', 'product'])
                ->lockForUpdate()
                ->findOrFail($item['variation_id']);

            if ($item['quantity'] <= 0) {
                throw new \Exception('La cantidad del producto debe ser mayor a 0.');
            }

            if ($item['quantity'] > $variation->stock) {
                throw new \Exception('Stock insuficiente para el producto seleccionado.');
            }

            $price = (float) $variation->effective_price;
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

        $shippingCost = (float) ($data['shipping_cost'] ?? 0);

        return [
            'attributes' => [
                'client_id' => $this->resolveClientId($data),
                'date' => $data['date'],
                'payment_method' => $data['payment_method'],
                'delivery_type' => $data['delivery_type'],
                'shipping_cost' => $shippingCost,
                'total' => $total + $shippingCost,
            ],
            'items' => $itemsData,
        ];
    }

    private function resolveClientId(array $data): ?int
    {
        $clientId = $data['client_id'] ?? null;

        if ($clientId || empty($data['new_client_name'])) {
            return $clientId;
        }

        $client = Client::create([
            'name' => $data['new_client_name'],
            'phone' => $data['new_client_phone'] ?? null,
            'instagram' => $data['new_client_instagram'] ?? null,
            'email' => $data['new_client_email'] ?? null,
            'notes' => $data['new_client_notes'] ?? null,
        ]);

        return $client->id;
    }

    private function persistOrderItems(Order $order, array $itemsData): void
    {
        $order->items()->createMany($itemsData);
    }
}
