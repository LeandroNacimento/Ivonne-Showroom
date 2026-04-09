<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHomeHeroSlideRequest extends FormRequest
{
    protected $errorBag = 'updateSlide';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slide_id' => ['required', 'integer'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'alt_text' => ['required', 'string', 'max:255'],
            'position' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [
            'slide_id' => $this->input('slide_id'),
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
        if (! is_numeric($value)) {
            return $value;
        }

        return (int) $value;
    }
}
