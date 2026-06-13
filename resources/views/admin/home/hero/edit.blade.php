@extends('layouts.admin')

@php
    // Detectar si venimos de un error de validación de edición para re-abrir el drawer
    $editingSlideId = old('slide_id', request('slide'));
    $editingSlide = $editingSlideId
        ? $slides->firstWhere('id', (int) $editingSlideId)
        : null;

    // Barra de estado
    $publicModeLabel = match (true) {
        ! $hero->is_renderable      => 'Portada por defecto',
        $activeSlidesCount === 1    => 'Imagen única',
        default                     => 'Carrusel',
    };

    $publicStatusText = match (true) {
        ! $hero->is_renderable => 'Hoy se muestra la portada por defecto en la web.',
        $activeSlidesCount === 1 => 'Se muestra la portada con una imagen única.',
        default => "Se muestra la portada como carrusel con {$activeSlidesCount} imágenes visibles.",
    };
@endphp

@section('content')
    @php
        $shouldOpenCreateDrawer = request()->boolean('create') || $errors->createSlide->any();
    @endphp

    <div
        class="space-y-6"
        x-data="{ drawerOpen: {{ ($editingSlide || $shouldOpenCreateDrawer) ? 'true' : 'false' }} }"
        @keydown.escape.window="drawerOpen = false">

        {{-- Header --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-medium text-brand-pink">Home / Portada principal</p>
                <h1 class="mt-0.5 text-2xl font-bold text-gray-900">Portada principal</h1>
                @if ($slides->count() > 0)
                    <p class="mt-0.5 text-sm text-gray-500">
                        {{ $activeSlidesCount }} de {{ $slides->count() }} {{ $slides->count() === 1 ? 'banner activo' : 'banners activos' }}
                    </p>
                @endif
            </div>
            <a
                href="{{ route('admin.home.hero.edit', ['create' => 1]) }}"
                class="inline-flex items-center gap-2 rounded-md bg-brand-pink px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-heart">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Agregar banner
            </a>
        </div>

        {{-- Barra de estado público --}}
        <div class="flex flex-wrap items-center gap-2 rounded-xl border border-gray-200 bg-white px-5 py-3.5 shadow-sm">
            <span class="h-2.5 w-2.5 shrink-0 rounded-full {{ $hero->is_renderable ? 'bg-green-500' : 'bg-amber-400' }}"></span>
            <span class="text-sm font-medium text-gray-700">
                {{ $hero->is_renderable ? 'Portada administrable activa' : 'Portada por defecto activa' }}
            </span>
            <span class="text-gray-300">·</span>
            <span class="text-sm text-gray-500">{{ $publicModeLabel }}</span>
            @if ($hero->is_renderable)
                <span class="text-gray-300">·</span>
                <span class="text-sm text-gray-500">{{ $activeSlidesCount }} {{ $activeSlidesCount === 1 ? 'imagen visible' : 'imágenes visibles' }}</span>
            @endif
            <span class="ml-auto text-xs text-gray-400">{{ $publicStatusText }}</span>
        </div>

        {{-- Mensajes de éxito --}}
        @if (session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-800">
                {{ session('success') }}
            </div>
        @endif

        {{-- Lista de banners --}}
        @if ($slides->isEmpty())
            <div class="rounded-xl border-2 border-dashed border-gray-200 bg-white px-6 py-16 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mx-auto h-10 w-10 text-gray-300">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                </svg>
                <p class="mt-4 text-sm font-semibold text-gray-700">Todavía no hay banners</p>
                <p class="mt-1 text-sm text-gray-400">Agregá el primero para reemplazar la portada por defecto.</p>
                <a href="{{ route('admin.home.hero.edit', ['create' => 1]) }}"
                    class="mt-6 inline-flex items-center gap-2 rounded-md bg-brand-pink px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-heart">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Agregar banner
                </a>
            </div>
        @else
            <ul id="slides-sortable"
                data-reorder-url="{{ route('admin.home.hero.slides.reorder') }}"
                class="space-y-3">
                @foreach ($slides as $slide)
                    <li>
                        @include('admin.home.hero._slide-card', [
                            'slide'    => $slide,
                            'position' => $loop->index,
                            'total'    => $slides->count(),
                        ])
                    </li>
                @endforeach
            </ul>

            @if ($slides->count() > 1)
                <p class="text-center text-xs text-gray-400">Arrastrá los banners para cambiar el orden.</p>
            @endif
        @endif

        {{-- Drawer: en alta usa $editingSlide = null; en edición post-error usa el slide detectado --}}
        @include('admin.home.hero._drawer', [
            'editingSlide' => $editingSlide,
        ])

    </div>
@endsection
