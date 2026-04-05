<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\ValidatesOrderItems;
use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
{
    use ValidatesOrderItems;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $order = $this->route('order');

        $rules = [
            'client_id' => ['nullable', 'exists:clients,id', 'required_without:new_client_name'],
            'new_client_name' => ['required_without:client_id', 'nullable', 'string', 'max:255'],
            'new_client_phone' => ['nullable', 'string', 'max:50'],
            'new_client_instagram' => ['nullable', 'string', 'max:100'],
            'new_client_email' => ['nullable', 'email', 'max:255'],
            'new_client_notes' => ['nullable', 'string', 'max:1000'],

            'date' => ['required', 'date', 'before_or_equal:now'],
            'status' => ['required', Rule::in([Order::STATUS_PENDING, Order::STATUS_RESERVED, Order::STATUS_DELIVERED, Order::STATUS_CANCELLED])],
            'payment_method' => ['required', Rule::in(['cash', 'transfer', 'mercadopago', 'other'])],
            'delivery_type' => ['required', Rule::in(['showroom', 'shipping'])],
            'shipping_cost' => ['required_if:delivery_type,shipping', 'nullable', 'numeric', 'min:0'],
        ];

        if (! $order || $order->status === Order::STATUS_PENDING) {
            $rules['items'] = ['required', 'array', 'min:1'];
            $rules['items.*.product_id'] = ['required', 'exists:products,id'];
            $rules['items.*.variation_id'] = ['required', 'exists:product_variations,id'];
            $rules['items.*.quantity'] = ['required', 'integer', 'min:1'];
            $rules['items.*.unit_price'] = ['required', 'numeric', 'gt:0'];
        }

        return $rules;
    }
}
