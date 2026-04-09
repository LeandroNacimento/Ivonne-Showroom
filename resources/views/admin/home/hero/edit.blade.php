@extends('layouts.admin')

@php
    $publicModeLabel = match (true) {
        ! $hero->is_renderable => 'Portada por defecto',
        $activeSlidesCount === 1 => 'Imagen unica',
        default => 'Carrusel simple',
    };

    $publicStatusTitle = match (true) {
        ! $hero->is_renderable => 'Hoy se muestra la portada por defecto en la web.',
        $activeSlidesCount === 1 => 'Hoy se muestra la portada principal administrable con una sola imagen.',
        default => "Hoy se muestra la portada principal administrable como carrusel con {$activeSlidesCount} imagenes visibles.",
    };

    $publicStatusBadge = $hero->is_renderable ? 'Portada administrable activa' : 'Portada por defecto activa';
    $visibleImagesLabel = $activeSlidesCount === 1 ? '1 imagen visible' : "{$activeSlidesCount} imagenes visibles";
    $hiddenSlidesCount = $slides->count() - $activeSlidesCount;
@endphp

@section('content')
    <div class="space-y-8">
        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-6 py-5">
                <p class="text-sm font-medium text-brand-pink">Home / Portada principal</p>
                <h1 class="mt-1 text-2xl font-bold text-gray-900">Portada principal de la tienda</h1>
                <p class="mt-2 max-w-3xl text-sm text-gray-600">
                    Desde esta pantalla administras el texto principal y las imagenes de la portada que ve la gente al entrar a la web.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-4 px-6 py-6 lg:grid-cols-[minmax(0,1.5fr)_minmax(0,1fr)]">
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Estado publico</p>
                    <div class="mt-3 flex flex-wrap items-center gap-3">
                        <span
                            class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $hero->is_renderable ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ $publicStatusBadge }}
                        </span>
                        <span class="inline-flex rounded-full bg-white px-3 py-1 text-xs font-semibold text-gray-700">
                            {{ $publicModeLabel }}
                        </span>
                    </div>
                    <p class="mt-4 text-sm font-medium text-gray-900">{{ $publicStatusTitle }}</p>
                    <p class="mt-2 text-sm text-gray-600">
                        Si la portada administrable no esta lista para publicarse, la tienda sigue mostrando la portada por defecto sin romper la home.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 lg:grid-cols-1">
                    <div class="rounded-xl border border-gray-200 bg-white p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Lo que se muestra</p>
                        <p class="mt-2 text-sm font-semibold text-gray-900">{{ $publicModeLabel }}</p>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Imagenes visibles</p>
                        <p class="mt-2 text-sm font-semibold text-gray-900">{{ $visibleImagesLabel }}</p>
                    </div>

                    <div class="rounded-xl border border-gray-200 bg-white p-4">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Imagenes ocultas</p>
                        <p class="mt-2 text-sm font-semibold text-gray-900">
                            {{ $hiddenSlidesCount === 1 ? '1 imagen oculta' : "{$hiddenSlidesCount} imagenes ocultas" }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-6 py-5">
                <h2 class="text-lg font-semibold text-gray-900">Texto principal de la portada</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Este contenido se comparte en toda la portada. Si completas el boton, se mostrara igual para todas las imagenes visibles.
                </p>
            </div>

            <form action="{{ route('admin.home.hero.update') }}" method="POST" class="px-6 py-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <div class="lg:col-span-2">
                        <label for="eyebrow" class="block text-sm font-medium text-gray-700">Etiqueta corta</label>
                        <input type="text" name="eyebrow" id="eyebrow"
                            value="{{ old('eyebrow', $hero->eyebrow) }}"
                            class="mt-2 w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                        <p class="mt-1 text-xs text-gray-500">Opcional. Sirve para una frase breve arriba del titulo, por ejemplo "Nueva temporada".</p>
                        @if ($errors->heroContent->has('eyebrow'))
                            <p class="mt-1 text-sm text-red-600">{{ $errors->heroContent->first('eyebrow') }}</p>
                        @endif
                    </div>

                    <div class="lg:col-span-2">
                        <label for="title" class="block text-sm font-medium text-gray-700">Titulo principal</label>
                        <input type="text" name="title" id="title" required
                            value="{{ old('title', $hero->title) }}"
                            class="mt-2 w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                        @if ($errors->heroContent->has('title'))
                            <p class="mt-1 text-sm text-red-600">{{ $errors->heroContent->first('title') }}</p>
                        @endif
                    </div>

                    <div class="lg:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700">Descripcion</label>
                        <textarea name="description" id="description" rows="4" required
                            class="mt-2 w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">{{ old('description', $hero->description) }}</textarea>
                        @if ($errors->heroContent->has('description'))
                            <p class="mt-1 text-sm text-red-600">{{ $errors->heroContent->first('description') }}</p>
                        @endif
                    </div>

                    <div>
                        <label for="cta_label" class="block text-sm font-medium text-gray-700">Texto del boton</label>
                        <input type="text" name="cta_label" id="cta_label"
                            value="{{ old('cta_label', $hero->cta_label) }}"
                            class="mt-2 w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                        @if ($errors->heroContent->has('cta_label'))
                            <p class="mt-1 text-sm text-red-600">{{ $errors->heroContent->first('cta_label') }}</p>
                        @endif
                    </div>

                    <div>
                        <label for="cta_url" class="block text-sm font-medium text-gray-700">Enlace del boton</label>
                        <input type="url" name="cta_url" id="cta_url"
                            value="{{ old('cta_url', $hero->cta_url) }}"
                            class="mt-2 w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                        @if ($errors->heroContent->has('cta_url'))
                            <p class="mt-1 text-sm text-red-600">{{ $errors->heroContent->first('cta_url') }}</p>
                        @endif
                    </div>
                </div>

                <p class="mt-4 text-xs text-gray-500">Si quieres mostrar un boton en la portada, completa el texto y el enlace.</p>

                <div class="mt-6 flex justify-end">
                    <button type="submit"
                        class="rounded-md bg-brand-pink px-6 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-brand-heart">
                        Guardar texto principal
                    </button>
                </div>
            </form>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-6 py-5">
                <h2 class="text-lg font-semibold text-gray-900">Imagenes de la portada</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Aqui defines que imagenes se muestran en la web, en que orden aparecen y cuales quedan ocultas.
                </p>
            </div>

            <div class="space-y-8 px-6 py-6">
                <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-5">
                    <h3 class="text-base font-semibold text-gray-900">Agregar imagen a la portada</h3>
                    <p class="mt-1 text-sm text-gray-500">Sube una imagen nueva y decide si quieres mostrarla ahora en la web.</p>

                    <form action="{{ route('admin.home.hero.slides.store') }}" method="POST" enctype="multipart/form-data"
                        class="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-2">
                        @csrf

                        <div class="lg:col-span-2">
                            <label for="new_slide_image" class="block text-sm font-medium text-gray-700">Archivo de imagen</label>
                            <input type="file" name="image" id="new_slide_image" accept="image/*" required
                                class="mt-2 w-full text-sm text-gray-500 file:mr-4 file:rounded-full file:border-0 file:bg-brand-blush file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-pink transition-colors hover:file:bg-brand-pink hover:file:text-white">
                            @if ($errors->createSlide->has('image'))
                                <p class="mt-1 text-sm text-red-600">{{ $errors->createSlide->first('image') }}</p>
                            @endif
                        </div>

                        <div class="lg:col-span-2">
                            <label for="new_slide_alt_text" class="block text-sm font-medium text-gray-700">Texto alternativo</label>
                            <input type="text" name="alt_text" id="new_slide_alt_text" required
                                value="{{ old('alt_text') }}"
                                class="mt-2 w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                            <p class="mt-1 text-xs text-gray-500">Describe brevemente la imagen para accesibilidad y lectores de pantalla.</p>
                            @if ($errors->createSlide->has('alt_text'))
                                <p class="mt-1 text-sm text-red-600">{{ $errors->createSlide->first('alt_text') }}</p>
                            @endif
                        </div>

                        <div>
                            <label for="new_slide_position" class="block text-sm font-medium text-gray-700">Orden de aparicion</label>
                            <input type="number" name="position" id="new_slide_position" min="0"
                                value="{{ old('position') }}"
                                class="mt-2 w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                            <p class="mt-1 text-xs text-gray-500">Usa 0 para la primera imagen. Si lo dejas vacio, se agrega al final.</p>
                            @if ($errors->createSlide->has('position'))
                                <p class="mt-1 text-sm text-red-600">{{ $errors->createSlide->first('position') }}</p>
                            @endif
                        </div>

                        <div class="rounded-lg border border-gray-200 bg-white px-4 py-3">
                            <input type="hidden" name="is_active" value="0">
                            <div class="flex items-center">
                                <input type="checkbox" name="is_active" id="new_slide_is_active" value="1"
                                    class="rounded border-gray-300 text-brand-pink shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50"
                                    {{ old('is_active', '1') ? 'checked' : '' }}>
                                <label for="new_slide_is_active" class="ml-3 text-sm font-medium text-gray-700">Mostrar esta imagen en la web</label>
                            </div>
                            <p class="mt-2 text-xs text-gray-500">Si la dejas desmarcada, la imagen queda guardada pero no se muestra todavia.</p>
                        </div>

                        <div class="lg:col-span-2 flex justify-end">
                            <button type="submit"
                                class="rounded-md bg-brand-pink px-6 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-brand-heart">
                                Agregar imagen
                            </button>
                        </div>
                    </form>
                </div>

                <div class="space-y-4">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900">Imagenes cargadas</h3>
                            <p class="mt-1 text-sm text-gray-500">Edita cada imagen, ajusta su orden y decide si se muestra o queda oculta.</p>
                        </div>
                        <span class="text-sm text-gray-500">
                            {{ $slides->count() === 1 ? '1 imagen registrada' : "{$slides->count()} imagenes registradas" }}
                        </span>
                    </div>

                    @forelse ($slides as $slide)
                        @php
                            $isEditingSlide = (string) old('slide_id') === (string) $slide->id;
                            $isChecked = (($isEditingSlide
                                ? old('is_active', $slide->is_active ? '1' : '0')
                                : ($slide->is_active ? '1' : '0')) == '1');
                        @endphp

                        <div
                            class="rounded-xl border p-5 shadow-sm {{ $slide->is_active ? 'border-green-200 bg-green-50/30' : 'border-gray-200 bg-white' }}">
                            <div class="grid grid-cols-1 gap-6 lg:grid-cols-[220px_minmax(0,1fr)]">
                                <div>
                                    <div class="overflow-hidden rounded-lg border border-gray-200 bg-gray-50">
                                        <img src="{{ $slide->public_image_url }}" alt="{{ $slide->alt_text }}"
                                            class="h-40 w-full object-cover">
                                    </div>
                                    <div class="mt-3 flex flex-wrap items-center gap-2 text-xs font-medium">
                                        <span
                                            class="inline-flex rounded-full px-2.5 py-1 {{ $slide->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                                            {{ $slide->is_active ? 'Se muestra en la web' : 'Oculta en la web' }}
                                        </span>
                                        <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-gray-700">
                                            Orden {{ $slide->position + 1 }}
                                        </span>
                                    </div>
                                    <p class="mt-3 text-sm font-semibold text-gray-900">Imagen {{ $loop->iteration }}</p>
                                    <p class="mt-1 text-sm text-gray-500">
                                        {{ $slide->is_active ? 'Esta imagen puede aparecer en la portada publica.' : 'Esta imagen esta guardada, pero hoy no se muestra en la web.' }}
                                    </p>
                                </div>

                                <div class="space-y-4">
                                    <form action="{{ route('admin.home.hero.slides.update', $slide) }}" method="POST"
                                        enctype="multipart/form-data" class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="slide_id" value="{{ $slide->id }}">

                                        @if ($isEditingSlide && $errors->updateSlide->any())
                                            <div class="lg:col-span-2 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                                                Revisa los datos de esta imagen antes de guardar.
                                            </div>
                                        @endif

                                        <div class="lg:col-span-2">
                                            <label for="alt_text_{{ $slide->id }}"
                                                class="block text-sm font-medium text-gray-700">Texto alternativo</label>
                                            <input type="text" name="alt_text" id="alt_text_{{ $slide->id }}" required
                                                value="{{ $isEditingSlide ? old('alt_text', $slide->alt_text) : $slide->alt_text }}"
                                                class="mt-2 w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                                            <p class="mt-1 text-xs text-gray-500">Una breve descripcion de la imagen para accesibilidad.</p>
                                            @if ($isEditingSlide && $errors->updateSlide->has('alt_text'))
                                                <p class="mt-1 text-sm text-red-600">{{ $errors->updateSlide->first('alt_text') }}</p>
                                            @endif
                                        </div>

                                        <div>
                                            <label for="position_{{ $slide->id }}"
                                                class="block text-sm font-medium text-gray-700">Orden de aparicion</label>
                                            <input type="number" name="position" id="position_{{ $slide->id }}" min="0"
                                                required value="{{ $isEditingSlide ? old('position', $slide->position) : $slide->position }}"
                                                class="mt-2 w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                                            <p class="mt-1 text-xs text-gray-500">Usa 0 para la primera imagen visible.</p>
                                            @if ($isEditingSlide && $errors->updateSlide->has('position'))
                                                <p class="mt-1 text-sm text-red-600">{{ $errors->updateSlide->first('position') }}</p>
                                            @endif
                                        </div>

                                        <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                                            <input type="hidden" name="is_active" value="0">
                                            <div class="flex items-center">
                                                <input type="checkbox" name="is_active" id="is_active_{{ $slide->id }}"
                                                    value="1"
                                                    class="rounded border-gray-300 text-brand-pink shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50"
                                                    {{ $isChecked ? 'checked' : '' }}>
                                                <label for="is_active_{{ $slide->id }}"
                                                    class="ml-3 text-sm font-medium text-gray-700">Mostrar esta imagen en la web</label>
                                            </div>
                                            @if ($isEditingSlide && $errors->updateSlide->has('is_active'))
                                                <p class="mt-2 text-sm text-red-600">{{ $errors->updateSlide->first('is_active') }}</p>
                                            @endif
                                        </div>

                                        <div class="lg:col-span-2">
                                            <label for="image_{{ $slide->id }}"
                                                class="block text-sm font-medium text-gray-700">Reemplazar imagen</label>
                                            <input type="file" name="image" id="image_{{ $slide->id }}" accept="image/*"
                                                class="mt-2 w-full text-sm text-gray-500 file:mr-4 file:rounded-full file:border-0 file:bg-brand-blush file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-pink transition-colors hover:file:bg-brand-pink hover:file:text-white">
                                            <p class="mt-1 text-xs text-gray-500">Dejalo vacio si quieres conservar la imagen actual.</p>
                                            @if ($isEditingSlide && $errors->updateSlide->has('image'))
                                                <p class="mt-1 text-sm text-red-600">{{ $errors->updateSlide->first('image') }}</p>
                                            @endif
                                        </div>

                                        <div class="lg:col-span-2 flex justify-end">
                                            <button type="submit"
                                                class="rounded-md bg-gray-900 px-5 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-gray-800">
                                                Guardar cambios de esta imagen
                                            </button>
                                        </div>
                                    </form>

                                    <div class="border-t border-gray-200 pt-4">
                                        <form action="{{ route('admin.home.hero.slides.destroy', $slide) }}" method="POST"
                                            onsubmit="return confirm('¿Eliminar esta imagen de la portada?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-sm font-semibold text-red-600 transition-colors hover:text-red-700">
                                                Eliminar imagen
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 px-5 py-8 text-center text-sm text-gray-500">
                            Todavia no cargaste imagenes para esta portada. Mientras tanto, la web seguira mostrando la portada por defecto.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
