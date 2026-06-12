<?php

namespace App\Support;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class HomeHeroValidator
{
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

    private static function slideRules(bool $create): array
    {
        return [
            'name' => ['nullable', 'string', 'max:100'],
            'desktop_image' => array_values(array_filter([
                $create ? 'required' : 'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:4096',
            ])),
            'mobile_image' => array_values(array_filter([
                $create ? 'required' : 'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:3072',
            ])),
            'alt_text' => $create
                ? ['required', 'string', 'max:255']
                : ['sometimes', 'required', 'string', 'max:255'],
            'link_type' => ['required', Rule::in(['none', 'external'])],
            'link_url' => ['nullable', 'url', 'max:2048', 'required_if:link_type,external'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    private static function normalizeSlideAttributes(array $attributes): array
    {
        if (array_key_exists('alt_text', $attributes)) {
            $attributes['alt_text'] = trim((string) $attributes['alt_text']);
        }

        if (array_key_exists('name', $attributes)) {
            $name = trim((string) $attributes['name']);
            $attributes['name'] = $name !== '' ? $name : null;
        }

        if (array_key_exists('link_url', $attributes)) {
            $url = trim((string) $attributes['link_url']);
            $attributes['link_url'] = $url !== '' ? $url : null;
        }

        return $attributes;
    }
}
