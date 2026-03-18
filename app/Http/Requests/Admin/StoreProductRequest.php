<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'images' => 'nullable|array',
            'images.*' => 'nullable|array',
            'images.*.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'variations' => 'required|array|min:1',
            'variations.*.color' => 'required|string',
            'variations.*.size' => 'required|string',
            'variations.*.price' => 'required|numeric|min:0',
            'variations.*.stock' => 'required|integer|min:0',
            'variations.*.sku' => 'nullable|string|max:100',
        ];
    }
}
