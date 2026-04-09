<?php

namespace App\Support;

use Illuminate\Support\Facades\Validator;

class HomeHeroValidator
{
    public static function validateContent(array $attributes): array
    {
        return Validator::make(
            self::normalizeContent($attributes),
            [
                'eyebrow' => ['nullable', 'string', 'max:80'],
                'title' => ['nullable', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:2000'],
                'cta_label' => ['nullable', 'string', 'max:80', 'required_with:cta_url'],
                'cta_url' => ['nullable', 'url', 'max:2048', 'required_with:cta_label'],
            ]
        )->validate();
    }

    public static function validateSlideCreate(array $attributes): array
    {
        return Validator::make(
            self::normalizeSlideAttributes($attributes),
            self::slideRules(true)
        )->validate();
    }

    public static function validateSlideUpdate(array $attributes): array
    {
        return Validator::make(
            self::normalizeSlideAttributes($attributes),
            self::slideRules(false)
        )->validate();
    }

    private static function slideRules(bool $requireImage): array
    {
        return [
            'image' => array_filter([
                $requireImage ? 'required' : 'sometimes',
                $requireImage ? null : 'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:5120',
            ]),
            'alt_text' => $requireImage
                ? ['required', 'string', 'max:255']
                : ['sometimes', 'required', 'string', 'max:255'],
            'position' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    private static function normalizeContent(array $attributes): array
    {
        foreach (['eyebrow', 'title', 'description', 'cta_label', 'cta_url'] as $field) {
            if (! array_key_exists($field, $attributes)) {
                continue;
            }

            $attributes[$field] = self::normalizeNullableString($attributes[$field]);
        }

        return $attributes;
    }

    private static function normalizeSlideAttributes(array $attributes): array
    {
        if (array_key_exists('alt_text', $attributes)) {
            $attributes['alt_text'] = trim((string) $attributes['alt_text']);
        }

        return $attributes;
    }

    private static function normalizeNullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }
}
