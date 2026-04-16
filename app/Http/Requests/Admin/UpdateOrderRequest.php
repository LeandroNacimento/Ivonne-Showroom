<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Concerns\ValidatesOrderItems;
use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
{
    use ValidatesOrderItems {
        withValidator as withOrderItemValidator;
    }

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
            'payment_method' => ['nullable', Rule::in(Order::PAYMENT_METHODS)],
            'delivery_type' => ['required', Rule::in(['showroom', 'shipping'])],
            'shipping_cost' => ['required_if:delivery_type,shipping', 'nullable', 'numeric', 'min:0'],
        ];

        if (! $order || $order->status === Order::STATUS_PENDING) {
            $rules['items'] = ['required', 'array', 'min:1'];
            $rules['items.*.product_id'] = ['required', 'exists:products,id'];
            $rules['items.*.variation_id'] = ['required', 'exists:product_variations,id'];
            $rules['items.*.quantity'] = ['required', 'integer', 'min:1'];
        }

        return $rules;
    }

    public function withValidator($validator): void
    {
        $this->withOrderItemValidator($validator);

        $validator->after(function ($validator) {
            $order = $this->route('order');

            if (! $order) {
                return;
            }

            $targetStatus = $this->input('status', $order->status);
            $incomingPaymentMethod = $this->normalizePaymentMethod($this->input('payment_method'));
            $effectivePaymentMethod = $incomingPaymentMethod ?? $order->payment_method;

            if (Order::statusRequiresPaymentMethod($targetStatus) && blank($effectivePaymentMethod)) {
                $validator->errors()->add('payment_method', 'Debe seleccionar un metodo de pago.');
            }
        });
    }

    private function normalizePaymentMethod($paymentMethod): ?string
    {
        if (! is_string($paymentMethod)) {
            return null;
        }

        $paymentMethod = trim($paymentMethod);

        return $paymentMethod === '' ? null : $paymentMethod;
    }
}
