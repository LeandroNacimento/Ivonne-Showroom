<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreHomeHeroSlideRequest extends FormRequest
{
    protected $errorBag = 'createSlide';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'alt_text' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [
            'alt_text' => trim((string) $this->input('alt_text', '')),
            'position' => $this->normalizePosition($this->input('position')),
        ];

        if ($this->has('is_active')) {
            $data['is_active'] = $this->boolean('is_active');
        }

        $this->merge($data);
    }

    private function normalizePosition(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return $value;
        }

        return (int) $value;
    }
}
