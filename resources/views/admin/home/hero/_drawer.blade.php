{{--
    Drawer de alta y edición de banners.
    Variables esperadas en el contexto:
      $editingSlide  — HomeHeroSlide|null (null = modo alta)
      $hero          — HomeHero
--}}

@php
    $isEditing   = $editingSlide !== null;
    $title       = $isEditing ? 'Editar banner' : 'Agregar banner';
    $action      = $isEditing
        ? route('admin.home.hero.slides.update', $editingSlide)
        : route('admin.home.hero.slides.store');
    $method      = $isEditing ? 'PUT' : 'POST';
    $errorBag    = $isEditing ? 'updateSlide' : 'createSlide';
    $old         = fn (string $key, $default = null) => old($key, $default);

    // Valores actuales
    $name       = $isEditing ? ($old('name', $editingSlide->name) ?? '') : ($old('name') ?? '');
    $altText    = $isEditing ? $old('alt_text', $editingSlide->alt_text) : $old('alt_text', '');
    $linkType   = $isEditing ? $old('link_type', $editingSlide->link_type) : $old('link_type', 'none');
    $linkUrl    = $isEditing ? $old('link_url', $editingSlide->link_url ?? '') : $old('link_url', '');
    $isActive   = $isEditing
        ? (old('is_active') !== null ? (bool) old('is_active') : $editingSlide->is_active)
        : (bool) old('is_active', true);

    // URLs de preview
    $desktopUrl = $isEditing ? $editingSlide->public_desktop_image_url : null;
    $mobileUrl  = $isEditing ? $editingSlide->public_mobile_image_url : null;
    $hasMobile  = $isEditing && $editingSlide->has_mobile_image;
@endphp

<div
    x-show="drawerOpen"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-40 bg-black/30 backdrop-blur-sm"
    @click="drawerOpen = false"
    style="display: none;">
</div>

<div
    x-show="drawerOpen"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="translate-x-full"
    @keydown.escape.window="drawerOpen = false"
    class="fixed inset-0 z-50 flex flex-col bg-white shadow-xl sm:inset-y-0 sm:left-auto sm:right-0 sm:w-[560px]"
    style="display: none;">

    {{-- Header --}}
    <div class="flex shrink-0 items-center justify-between border-b border-gray-200 px-6 py-5">
        <h2 class="text-lg font-semibold text-gray-900">{{ $title }}</h2>
        <button type="button" @click="drawerOpen = false"
            class="rounded-md p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600">
            <span class="sr-only">Cerrar</span>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    {{-- Formulario scrollable --}}
    <form
        action="{{ $action }}"
        method="POST"
        enctype="multipart/form-data"
        class="flex flex-1 flex-col overflow-y-auto">
        @csrf
        @method($method)
        @if ($isEditing)
            <input type="hidden" name="slide_id" value="{{ $editingSlide->id }}">
        @endif

        <div class="flex-1 space-y-6 px-6 py-6">

            {{-- Errores globales --}}
            @if ($errors->{$errorBag}->any())
                <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    Revisá los campos marcados antes de guardar.
                </div>
            @endif

            {{-- Imagen Desktop --}}
            @include('admin.home.hero._image-upload-zone', [
                'inputName' => 'desktop_image',
                'inputId'   => 'drawer_desktop_image',
                'label'     => 'Imagen Desktop',
                'hint'      => '1920 × 1080 px · 16:9 · JPG / WebP · máx 3 MB',
                'currentUrl' => $desktopUrl,
                'hasFallback' => false,
                'required'  => ! $isEditing,
                'errorBag'  => $errorBag,
                'errorKey'  => 'desktop_image',
            ])

            {{-- Imagen Mobile --}}
            @include('admin.home.hero._image-upload-zone', [
                'inputName'  => 'mobile_image',
                'inputId'    => 'drawer_mobile_image',
                'label'      => 'Imagen Mobile',
                'hint'       => '1080 × 1350 px · 4:5 · JPG / WebP · máx 2 MB',
                'currentUrl' => $mobileUrl,
                'hasFallback' => $isEditing && ! $hasMobile,
                'required'   => ! $isEditing,
                'errorBag'   => $errorBag,
                'errorKey'   => 'mobile_image',
            ])

            {{-- Nombre interno --}}
            <div>
                <label for="drawer_name" class="block text-sm font-semibold text-gray-700">
                    Nombre interno
                </label>
                <input type="text"
                       name="name"
                       id="drawer_name"
                       value="{{ $name }}"
                       placeholder="Ej: Invierno 2026, Sale Jeans…"
                       maxlength="100"
                       class="mt-2 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                <p class="mt-1 text-xs text-gray-400">Solo visible en el panel de administración. No aparece en la web.</p>
                @if ($errors->{$errorBag}->has('name'))
                    <p class="mt-1 text-xs text-red-600">{{ $errors->{$errorBag}->first('name') }}</p>
                @endif
            </div>

            {{-- Alt text --}}
            <div>
                <label for="drawer_alt_text" class="block text-sm font-semibold text-gray-700">
                    Alt text <span class="text-brand-pink">*</span>
                </label>
                <input type="text"
                       name="alt_text"
                       id="drawer_alt_text"
                       value="{{ $altText }}"
                       required
                       maxlength="255"
                       placeholder="Ej: Mujer con abrigo beige, colección invierno"
                       class="mt-2 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                <p class="mt-1 text-xs text-gray-400">Texto alternativo para accesibilidad y SEO. Describí brevemente qué muestra la imagen.</p>
                @if ($errors->{$errorBag}->has('alt_text'))
                    <p class="mt-1 text-xs text-red-600">{{ $errors->{$errorBag}->first('alt_text') }}</p>
                @endif
            </div>

            {{-- Destino del banner --}}
            <div x-data="{ linkType: '{{ $linkType }}' }">
                <p class="text-sm font-semibold text-gray-700">Destino del banner</p>
                <div class="mt-3 space-y-2">

                    <label class="flex cursor-pointer items-center gap-2.5">
                        <input type="radio" name="link_type" value="none"
                               x-model="linkType"
                               class="text-brand-pink focus:ring-brand-pink">
                        <span class="text-sm text-gray-700">Sin destino</span>
                    </label>

                    <label class="flex cursor-pointer items-center gap-2.5">
                        <input type="radio" name="link_type" value="external"
                               x-model="linkType"
                               class="text-brand-pink focus:ring-brand-pink">
                        <span class="text-sm text-gray-700">URL externa</span>
                    </label>

                </div>

                {{-- URL externa --}}
                <div x-show="linkType === 'external'" x-transition class="mt-3">
                    <label for="drawer_link_url" class="block text-xs font-medium text-gray-600">URL de destino</label>
                    <input type="url"
                           name="link_url"
                           id="drawer_link_url"
                           value="{{ $linkUrl }}"
                           placeholder="https://…"
                           maxlength="2048"
                           class="mt-1.5 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                    @if ($errors->{$errorBag}->has('link_url'))
                        <p class="mt-1 text-xs text-red-600">{{ $errors->{$errorBag}->first('link_url') }}</p>
                    @endif
                </div>

            </div>

            {{-- Estado --}}
            <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                <input type="hidden" name="is_active" value="0">
                <label class="flex cursor-pointer items-center gap-3">
                    <input type="checkbox"
                           name="is_active"
                           id="drawer_is_active"
                           value="1"
                           @if($isActive) checked @endif
                           class="rounded border-gray-300 text-brand-pink shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                    <span class="text-sm font-medium text-gray-700">Mostrar en la web</span>
                </label>
                <p class="mt-1.5 pl-7 text-xs text-gray-400">Si lo desactivás, el banner queda guardado pero no aparece en la portada.</p>
            </div>

        </div>

        {{-- Footer fijo --}}
        <div class="shrink-0 border-t border-gray-200 bg-white px-6 py-4">
            <div class="flex items-center gap-3">
                <button type="submit"
                    class="flex-1 rounded-md bg-brand-pink py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-heart">
                    {{ $isEditing ? 'Guardar cambios' : 'Agregar banner' }}
                </button>
                <button type="button"
                    @click="drawerOpen = false"
                    class="rounded-md border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-600 transition hover:bg-gray-50">
                    Cancelar
                </button>
            </div>
        </div>

    </form>
</div>
