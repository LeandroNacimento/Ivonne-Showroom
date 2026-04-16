<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Order;
use App\Models\ProductVariation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
                $order = $this->transitionStatus($order, Order::STATUS_RESERVED);
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
            'payment_method' => $data['payment_method'] ?? $order->payment_method,
            'delivery_type' => $data['delivery_type'],
            'shipping_cost' => $shippingCost,
            'total' => $itemsTotal + $shippingCost,
        ]);

        return $order;
    }

    public function transitionStatus(Order $order, string $newStatus, array $context = []): Order
    {
        return DB::transaction(function () use ($order, $newStatus, $context) {
            $order = Order::query()->findOrFail($order->id);
            $oldStatus = $order->status;
            $paymentMethod = $this->normalizePaymentMethod($context['payment_method'] ?? null);

            if (! in_array($newStatus, Order::STATUSES, true)) {
                throw ValidationException::withMessages([
                    'status' => 'El estado seleccionado no es válido.',
                ]);
            }

            if ($paymentMethod !== null && ! in_array($paymentMethod, Order::PAYMENT_METHODS, true)) {
                throw ValidationException::withMessages([
                    'payment_method' => 'Debe seleccionar un metodo de pago válido.',
                ]);
            }

            if ($oldStatus === $newStatus) {
                return $order->fresh();
            }

            $effectivePaymentMethod = $paymentMethod ?? $order->payment_method;

            if (Order::statusRequiresPaymentMethod($newStatus) && blank($effectivePaymentMethod)) {
                throw ValidationException::withMessages([
                    'payment_method' => 'Debe seleccionar un metodo de pago.',
                ]);
            }

            $order->load(['items.variation.productColor', 'items.product']);
            $this->handler->handle($order, $oldStatus, $newStatus);

            $updates = [
                'status' => $newStatus,
            ];

            if (Order::statusRequiresPaymentMethod($newStatus) && $paymentMethod !== null) {
                $updates['payment_method'] = $paymentMethod;
            }

            $order->update($updates);

            return $order->fresh(['client', 'items.variation.productColor', 'items.product']);
        });
    }

    public function availableStatusesFor(Order|string $order): array
    {
        $status = $order instanceof Order ? $order->status : $order;

        return $this->handler->availableStatusesFor($status);
    }

    public function statusOptionsFor(Order $order): array
    {
        return collect($this->availableStatusesFor($order))
            ->map(function (string $status) use ($order) {
                return [
                    'value' => $status,
                    'label' => Order::statusLabel($status),
                    'requires_payment_method' => $this->transitionRequiresPaymentMethod($order, $status),
                ];
            })
            ->values()
            ->all();
    }

    public function transitionRequiresPaymentMethod(Order $order, string $newStatus): bool
    {
        if ($order->status === $newStatus) {
            return false;
        }

        return Order::statusRequiresPaymentMethod($newStatus) && blank($order->payment_method);
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
                'payment_method' => $this->normalizePaymentMethod($data['payment_method'] ?? null),
                'delivery_type' => $data['delivery_type'],
                'shipping_cost' => $shippingCost,
                'total' => $total + $shippingCost,
            ],
            'items' => $itemsData,
        ];
    }

    private function normalizePaymentMethod(?string $paymentMethod): ?string
    {
        if ($paymentMethod === null) {
            return null;
        }

        $paymentMethod = trim($paymentMethod);

        return $paymentMethod === '' ? null : $paymentMethod;
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
