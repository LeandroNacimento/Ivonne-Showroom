<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHomeHeroContentRequest extends FormRequest
{
    protected $errorBag = 'heroContent';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'eyebrow' => ['nullable', 'string', 'max:80'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'cta_label' => ['nullable', 'string', 'max:80', 'required_with:cta_url'],
            'cta_url' => ['nullable', 'url', 'max:2048', 'required_with:cta_label'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'eyebrow' => $this->normalizeNullableString($this->input('eyebrow')),
            'title' => $this->normalizeNullableString($this->input('title')),
            'description' => $this->normalizeNullableString($this->input('description')),
            'cta_label' => $this->normalizeNullableString($this->input('cta_label')),
            'cta_url' => $this->normalizeNullableString($this->input('cta_url')),
        ]);
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }
}
