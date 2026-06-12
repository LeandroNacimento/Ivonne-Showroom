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
            'name'           => ['nullable', 'string', 'max:100'],
            'desktop_image'  => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:4096'],
            'mobile_image'   => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:3072'],
            'alt_text'       => ['required', 'string', 'max:255'],
            'link_type'      => ['required', 'in:none,external'],
            'link_url'       => ['nullable', 'url', 'max:2048', 'required_if:link_type,external'],
            'is_active'      => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = [
            'alt_text'  => trim((string) $this->input('alt_text', '')),
            'link_type' => $this->input('link_type', 'none'),
        ];

        $name = trim((string) $this->input('name', ''));
        $data['name'] = $name !== '' ? $name : null;

        $linkUrl = trim((string) $this->input('link_url', ''));
        $data['link_url'] = $linkUrl !== '' ? $linkUrl : null;

        if ($this->has('is_active')) {
            $data['is_active'] = $this->boolean('is_active');
        }

        $this->merge($data);
    }
}
