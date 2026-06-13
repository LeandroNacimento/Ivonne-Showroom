@props([
    'inputName',       // nombre del campo file (ej: 'desktop_image')
    'inputId',         // id del input (ej: 'desktop_image_new')
    'label',           // 'Imagen Desktop' | 'Imagen Mobile'
    'hint',            // '1920 × 1080 px · 16:9 · JPG/WebP · máx 3 MB'
    'currentUrl',      // URL de la imagen actual (null si no existe)
    'hasFallback',     // si true y currentUrl viene de fallback, muestra badge ámbar
    'required',        // bool
    'errorBag',        // nombre del error bag
    'errorKey',        // clave del error (ej: 'desktop_image')
])

@php
    $inputErrors = isset($errorBag)
        ? $errors->{$errorBag}
        : $errors->default;
@endphp

<div
    x-data="{
        initialUrl: @js($currentUrl),
        previewUrl: @js($currentUrl),
        objectUrl: null,
    }"
>
    <p class="text-sm font-semibold text-gray-700">{{ $label }}</p>

    <div class="relative mt-2 overflow-hidden rounded-lg border border-gray-200 bg-gray-50 {{ str_contains($inputName, 'mobile') ? 'aspect-[4/5]' : 'aspect-video' }} max-h-48">
        <template x-if="previewUrl">
            <img
                :src="previewUrl"
                alt="{{ $label }}"
                class="absolute inset-0 h-full w-full object-cover">
        </template>

        <template x-if="!previewUrl">
            <div class="absolute inset-0 flex items-center justify-center">
                <div class="text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mx-auto h-8 w-8 text-gray-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                    </svg>
                    <p class="mt-1 text-xs text-gray-500">Sin imagen</p>
                </div>
            </div>
        </template>

        @if ($hasFallback ?? false)
            <div
                x-cloak
                x-show="!objectUrl"
                class="absolute bottom-2 left-2 z-10">
                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="h-3 w-3">
                        <path fill-rule="evenodd" d="M6.701 2.25c.577-1 2.02-1 2.598 0l5.196 9a1.5 1.5 0 0 1-1.299 2.25H2.804a1.5 1.5 0 0 1-1.3-2.25l5.197-9ZM8 4a.75.75 0 0 1 .75.75v3a.75.75 0 0 1-1.5 0v-3A.75.75 0 0 1 8 4Zm0 8a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
                    </svg>
                    Usando imagen desktop
                </span>
            </div>
        @endif
    </div>

    <label for="{{ $inputId }}" class="mt-2 inline-flex cursor-pointer items-center gap-1.5 text-xs font-semibold text-brand-pink hover:text-brand-heart">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="h-3.5 w-3.5">
            <path fill-rule="evenodd" d="M8 1a.75.75 0 0 1 .75.75V6h4.25a.75.75 0 0 1 0 1.5H8.75v4.25a.75.75 0 0 1-1.5 0V7.5H3a.75.75 0 0 1 0-1.5h4.25V1.75A.75.75 0 0 1 8 1Z" clip-rule="evenodd" />
        </svg>
        {{ $currentUrl ? 'Cambiar imagen' : 'Seleccionar imagen' }}
    </label>
    <input type="file"
           name="{{ $inputName }}"
           id="{{ $inputId }}"
           accept="image/jpeg,image/png,image/webp"
           @if($required) required @endif
           @change="
                const file = $event.target.files && $event.target.files[0];

                if (!file) {
                    if (objectUrl) {
                        URL.revokeObjectURL(objectUrl);
                        objectUrl = null;
                    }

                    previewUrl = initialUrl;
                    return;
                }

                if (objectUrl) {
                    URL.revokeObjectURL(objectUrl);
                }

                objectUrl = URL.createObjectURL(file);
                previewUrl = objectUrl;
           "
           class="sr-only">

    <p class="mt-1 text-xs text-gray-400">{{ $hint }}</p>

    @if ($inputErrors->has($errorKey))
        <p class="mt-1 text-xs text-red-600">{{ $inputErrors->first($errorKey) }}</p>
    @endif
</div>
