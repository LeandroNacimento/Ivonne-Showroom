<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'size_type' => ['required', 'string', Rule::in(array_keys(Product::sizeTypeOptions()))],
            'images' => 'nullable|array',
            'images.*' => 'nullable|array',
            'images.*.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'variations' => 'required|array|min:1',
            'variations.*.color' => 'required|string',
            'variations.*.size' => 'required|string',
            'variations.*.price' => 'required|numeric|min:0',
            'variations.*.sale_price' => 'nullable|numeric|gt:0',
            'variations.*.stock' => 'required|integer|min:0',
            'variations.*.sku' => 'nullable|string|max:100',
        ];
    }

    protected function prepareForValidation(): void
    {
        $variations = collect($this->input('variations', []))
            ->map(function ($variation) {
                if (! is_array($variation)) {
                    return $variation;
                }

                $variation['size'] = Product::normalizeSize($variation['size'] ?? null);

                return $variation;
            })
            ->all();

        $this->merge([
            'size_type' => is_string($this->input('size_type')) ? trim($this->input('size_type')) : $this->input('size_type'),
            'variations' => $variations,
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $sizeType = $this->input('size_type');

            if (! Product::isValidSizeType($sizeType)) {
                return;
            }

            $allowedSizes = Product::getAllowedSizes($sizeType);
            $seen = [];

            foreach ($this->input('variations', []) as $index => $variation) {
                $size = $variation['size'] ?? '';
                $color = mb_strtolower(trim((string) ($variation['color'] ?? '')), 'UTF-8');

                if ($size === null || $size === '') {
                    $validator->errors()->add("variations.{$index}.size", 'Debe seleccionar un talle válido.');

                    continue;
                }

                if (! in_array($size, $allowedSizes, true)) {
                    $validator->errors()->add("variations.{$index}.size", 'El talle no es válido para el tipo de talles seleccionado.');
                }

                if ($color === '') {
                    continue;
                }

                $salePrice = $variation['sale_price'] ?? null;

                if ($salePrice !== null && $salePrice !== '' && (float) $salePrice >= (float) ($variation['price'] ?? 0)) {
                    $validator->errors()->add("variations.{$index}.sale_price", 'El precio de oferta debe ser menor al precio base.');
                }

                $key = "{$color}|{$size}";

                if (isset($seen[$key])) {
                    $validator->errors()->add("variations.{$index}.size", 'Combinación color + talle duplicada.');

                    continue;
                }

                $seen[$key] = true;
            }
        });
    }
}
