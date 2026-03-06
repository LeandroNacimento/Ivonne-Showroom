<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Validation\ValidationException;

class OrderStatusTransitionHandler
{
    /**
     * Estados terminales que no permiten ninguna transición.
     */
    private const TERMINAL_STATES = ['entregado', 'cancelado'];

    /**
     * Transiciones permitidas: [oldStatus => [allowedNewStatuses]]
     */
    private const ALLOWED_TRANSITIONS = [
        'pendiente' => ['reservado', 'cancelado'],
        'reservado' => ['entregado', 'cancelado'],
    ];

    /**
     * Maneja la transición de estado de un pedido y su impacto en el stock.
     * Debe ejecutarse dentro de una DB::transaction().
     *
     * @throws ValidationException si la transición no está permitida o no hay stock.
     */
    public function handle(Order $order, string $oldStatus, string $newStatus): void
    {
        // Si el estado no cambia, no hacemos nada.
        if ($oldStatus === $newStatus) {
            return;
        }

        // Bloquear transiciones desde estados terminales.
        if (in_array($oldStatus, self::TERMINAL_STATES)) {
            throw ValidationException::withMessages([
                'status' => "El pedido está en estado '{$oldStatus}' y no puede ser modificado.",
            ]);
        }

        // Validar que la transición sea permitida.
        $allowed = self::ALLOWED_TRANSITIONS[$oldStatus] ?? [];
        if (!in_array($newStatus, $allowed)) {
            throw ValidationException::withMessages([
                'status' => "La transición de '{$oldStatus}' a '{$newStatus}' no está permitida.",
            ]);
        }

        // pendiente → reservado: descontar stock
        if ($oldStatus === 'pendiente' && $newStatus === 'reservado') {
            $this->decreaseOrderStock($order);
        }

        // reservado → cancelado: devolver stock
        if ($oldStatus === 'reservado' && $newStatus === 'cancelado') {
            $this->increaseOrderStock($order);
        }

        // pendiente → cancelado: sin cambio de stock
        // reservado → entregado: sin cambio de stock
    }

    /**
     * Descuenta el stock de cada ítem. Lanza excepción si no hay stock suficiente.
     */
    private function decreaseOrderStock(Order $order): void
    {
        foreach ($order->items as $item) {
            // Aplicar bloqueo pesimista (row-level lock) para evitar condiciones de carrera (ventas simultáneas)
            $variation = \App\Models\ProductVariation::lockForUpdate()->find($item->variation_id);

            if (!$variation) {
                throw ValidationException::withMessages([
                    'items' => "La variación del producto '{$item->product?->name}' ya no existe.",
                ]);
            }

            if (!$variation->hasStock($item->quantity)) {
                $colorName = $variation->productColor?->name ?? 'N/A';
                throw ValidationException::withMessages([
                    'items' => "Stock insuficiente para '{$item->product?->name}' (Talle: {$variation->size}, Color: {$colorName}).",
                ]);
            }

            $variation->decreaseStock($item->quantity);
        }
    }

    /**
     * Devuelve el stock de cada ítem.
     */
    private function increaseOrderStock(Order $order): void
    {
        foreach ($order->items as $item) {
            $variation = $item->variation;

            if ($variation) {
                $variation->increaseStock($item->quantity);
            }
        }
    }
}
