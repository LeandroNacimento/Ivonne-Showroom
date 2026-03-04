<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Validation\ValidationException;

class OrderStatusTransitionHandler
{
    /**
     * Maneja la transición de estado de un pedido y su impacto lógico en el stock de los productos.
     * Esta clase asume estar ejecutándose dentro de una transacción de BD (DB::transaction).
     *
     * @param Order $order El pedido que va a cambiar de estado y sus ítems (deben estar cargados).
     * @param string $oldStatus El estado viejo del pedido.
     * @param string $newStatus El nuevo estado al que transicionará el pedido.
     * @return void
     * @throws ValidationException
     */
    public function handle(Order $order, string $oldStatus, string $newStatus): void
    {
        // 1. Si el estado no cambia, no hacemos nada
        if (strtolower($oldStatus) === strtolower($newStatus)) {
            return;
        }

        $wasDiscounting = in_array(strtolower($oldStatus), ['confirmado', 'entregado']);
        $willDiscount = in_array(strtolower($newStatus), ['confirmado', 'entregado']);

        // 2. Definir reglas de negocio basadas en el impacto del stock

        // Caso A: No descontaba y ahora descontará -> Disminuir stock
        if (!$wasDiscounting && $willDiscount) {
            $this->decreaseOrderStock($order);
        }

        // Caso B: Descontaba y ahora dejará de descontar -> Aumentar (Devolver) stock
        elseif ($wasDiscounting && !$willDiscount) {
            $this->increaseOrderStock($order);
        }

        // Si wasDiscounting == willDiscount, el stock no se ve afectado.
    }

    /**
     * Aplica el decuento iterando por cada ítem del pedido.
     */
    private function decreaseOrderStock(Order $order): void
    {
        foreach ($order->items as $item) {
            $variation = $item->variation; // Asume eager loading o carga on-demand

            if ($variation) {
                if (!$variation->hasStock($item->quantity)) {
                    throw ValidationException::withMessages([
                        'stock' => "No hay stock suficiente para el producto: {$item->product->name} (Talle: {$item->size}, Color: {$item->color}). Requerido: {$item->quantity}, Disponible: {$variation->stock}."
                    ]);
                }

                $variation->decreaseStock($item->quantity);
            }
        }
    }

    /**
     * Devuelve el stock iterando por cada ítem.
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
