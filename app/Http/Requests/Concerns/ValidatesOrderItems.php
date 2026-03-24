<?php

namespace App\Http\Requests\Concerns;

use App\Models\Order;
use App\Models\ProductVariation;

trait ValidatesOrderItems
{
    /**
     * Add validation hooks to the validator.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $order = $this->route('order');

            // Solo validar items si el pedido está en estado pending
            if ($order && $order->status !== Order::STATUS_PENDING) {
                return;
            }

            $items = $this->input('items', []);

            $this->validateNoDuplicateVariations($validator, $items);
            $this->validateVariationBelongsToProduct($validator, $items);
            $this->validateStockAvailability($validator, $items);
        });
    }

    protected function validateStockAvailability($validator, array $items): void
    {
        $order = $this->route('order');

        // Si el pedido ya está en estado reservado, el stock ya fue descontado.
        // Los ítems vienen readonly en el form, por lo que no validaremos contra el stock *restante*.
        if ($order && in_array($order->status, [Order::STATUS_RESERVED, Order::STATUS_DELIVERED])) {
            return;
        }

        foreach ($items as $index => $item) {
            $variationId = $item['variation_id'] ?? null;
            $quantity = (int) ($item['quantity'] ?? 0);

            if ($variationId && $quantity > 0) {
                $variation = ProductVariation::find($variationId);
                if ($variation && $quantity > $variation->stock) {
                    $validator->errors()->add(
                        "items.{$index}.quantity",
                        "La cantidad solicitada ({$quantity}) supera el stock disponible ({$variation->stock}) para esta variante."
                    );
                }
            }
        }
    }

    protected function validateNoDuplicateVariations($validator, array $items): void
    {
        $variationIds = array_column($items, 'variation_id');
        if (count($variationIds) !== count(array_unique($variationIds))) {
            $validator->errors()->add('items', 'No se puede repetir la misma variación en dos ítems del pedido.');
        }
    }

    protected function validateVariationBelongsToProduct($validator, array $items): void
    {
        foreach ($items as $index => $item) {
            $variationId = $item['variation_id'] ?? null;
            $productId = $item['product_id'] ?? null;

            if (!$variationId || !$productId) {
                continue;
            }

            $variation = ProductVariation::find($variationId);
            if ($variation && (int) $variation->product_id !== (int) $productId) {
                $validator->errors()->add(
                    "items.{$index}.variation_id",
                    "La variación seleccionada no pertenece al producto indicado."
                );
            }
        }
    }

    public function messages(): array
    {
        return [
            'client_id.required_without' => 'Debe seleccionar un cliente o crear uno nuevo.',
            'new_client_name.required_without' => 'El nombre del cliente nuevo es obligatorio si no selecciona uno.',
            'client_id.exists' => 'El cliente seleccionado no existe.',
            'status.in' => 'El estado indicado no es válido.',
            'items.required' => 'El pedido debe tener al menos un ítem.',
            'items.min' => 'El pedido debe tener al menos un ítem.',
            'items.*.variation_id.required' => 'Debe seleccionar una variación para cada ítem.',
            'items.*.product_id.required' => 'Debe seleccionar un producto válido usando el buscador.',
            'items.*.product_id.exists' => 'El producto seleccionado ya no existe en la base de datos.',
            'items.*.quantity.required' => 'Debe indicar la cantidad solicitada.',
            'items.*.quantity.min' => 'La cantidad mínima es 1.',
            'date.before_or_equal' => 'La fecha del pedido no puede ser futura.',
            'payment_method.required' => 'Debe seleccionar un método de pago.',
            'delivery_type.required' => 'Debe seleccionar un tipo de entrega.',
            'shipping_cost.required_if' => 'Debe indicar el costo de envío si el método de entrega es Envío (puede ser 0).',
            'items.*.unit_price.required' => 'El precio del producto no puede quedar vacío.',
            'items.*.unit_price.gt' => 'El precio del producto debe ser mayor a 0.',
        ];
    }
}
