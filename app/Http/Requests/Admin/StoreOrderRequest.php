<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\ProductVariation;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'client_id' => ['nullable', 'exists:clients,id', 'required_without:new_client_name'],
            'new_client_name' => ['required_without:client_id', 'nullable', 'string', 'max:255'],
            'new_client_phone' => ['nullable', 'string', 'max:50'],
            'new_client_instagram' => ['nullable', 'string', 'max:100'],
            'new_client_email' => ['nullable', 'email', 'max:255'],
            'new_client_notes' => ['nullable', 'string', 'max:1000'],

            'date' => ['required', 'date', 'before_or_equal:now'],
            'status' => ['required', Rule::in(['pendiente', 'reservado'])],
            'payment_method' => ['required', Rule::in(['cash', 'transfer', 'mercadopago', 'other'])],
            'delivery_type' => ['required', Rule::in(['showroom', 'shipping'])],
            'shipping_cost' => ['required_if:delivery_type,shipping', 'nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.variation_id' => ['required', 'exists:product_variations,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'gt:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $items = $this->input('items', []);

            $this->validateNoDuplicateVariations($validator, $items);
            $this->validateVariationBelongsToProduct($validator, $items);
            $this->validateStockAvailability($validator, $items);
        });
    }

    private function validateStockAvailability($validator, array $items): void
    {
        foreach ($items as $index => $item) {
            $variationId = $item['variation_id'] ?? null;
            $quantity = (int) ($item['quantity'] ?? 0);

            if ($variationId && $quantity > 0) {
                $variation = ProductVariation::find($variationId);
                // Si la variación existe, comprobamos el stock
                if ($variation && $quantity > $variation->stock) {
                    $validator->errors()->add(
                        "items.{$index}.quantity",
                        "La cantidad solicitada ({$quantity}) supera el stock disponible ({$variation->stock}) para esta variante."
                    );
                }
            }
        }
    }

    private function validateNoDuplicateVariations($validator, array $items): void
    {
        $variationIds = array_column($items, 'variation_id');
        if (count($variationIds) !== count(array_unique($variationIds))) {
            $validator->errors()->add('items', 'No se puede repetir la misma variación en dos ítems del pedido.');
        }
    }

    private function validateVariationBelongsToProduct($validator, array $items): void
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
            'status.in' => 'El estado debe ser Pendiente o Reservado al crear un pedido.',
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
