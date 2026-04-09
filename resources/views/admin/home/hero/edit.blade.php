@extends('layouts.admin')

@section('content')
    <div class="space-y-8">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-medium text-brand-pink">Home / Hero</p>
                <h1 class="text-2xl font-bold text-gray-900">Hero principal</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Administrá la portada principal de la home desde un único módulo, sin mezclarla con Settings.
                </p>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white px-4 py-3 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">Estado público</p>
                <div class="mt-2 flex items-center gap-3">
                    <span
                        class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $hero->is_renderable ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                        {{ $hero->is_renderable ? 'Renderizable' : 'Usando fallback seguro' }}
                    </span>
                    <span class="text-sm text-gray-600">{{ $activeSlidesCount }} slide(s) activas</span>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-6 py-5">
                <h2 class="text-lg font-semibold text-gray-900">Contenido del hero</h2>
                <p class="mt-1 text-sm text-gray-500">Editá el contenido compartido del hero principal.</p>
            </div>

            <form action="{{ route('admin.home.hero.update') }}" method="POST" class="px-6 py-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <div class="lg:col-span-2">
                        <label for="eyebrow" class="block text-sm font-medium text-gray-700">Eyebrow</label>
                        <input type="text" name="eyebrow" id="eyebrow"
                            value="{{ old('eyebrow', $hero->eyebrow) }}"
                            class="mt-2 w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                        @if ($errors->heroContent->has('eyebrow'))
                            <p class="mt-1 text-sm text-red-600">{{ $errors->heroContent->first('eyebrow') }}</p>
                        @endif
                    </div>

                    <div class="lg:col-span-2">
                        <label for="title" class="block text-sm font-medium text-gray-700">Título</label>
                        <input type="text" name="title" id="title" required
                            value="{{ old('title', $hero->title) }}"
                            class="mt-2 w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                        @if ($errors->heroContent->has('title'))
                            <p class="mt-1 text-sm text-red-600">{{ $errors->heroContent->first('title') }}</p>
                        @endif
                    </div>

                    <div class="lg:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700">Descripción</label>
                        <textarea name="description" id="description" rows="4" required
                            class="mt-2 w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">{{ old('description', $hero->description) }}</textarea>
                        @if ($errors->heroContent->has('description'))
                            <p class="mt-1 text-sm text-red-600">{{ $errors->heroContent->first('description') }}</p>
                        @endif
                    </div>

                    <div>
                        <label for="cta_label" class="block text-sm font-medium text-gray-700">CTA label</label>
                        <input type="text" name="cta_label" id="cta_label"
                            value="{{ old('cta_label', $hero->cta_label) }}"
                            class="mt-2 w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                        @if ($errors->heroContent->has('cta_label'))
                            <p class="mt-1 text-sm text-red-600">{{ $errors->heroContent->first('cta_label') }}</p>
                        @endif
                    </div>

                    <div>
                        <label for="cta_url" class="block text-sm font-medium text-gray-700">CTA URL</label>
                        <input type="url" name="cta_url" id="cta_url"
                            value="{{ old('cta_url', $hero->cta_url) }}"
                            class="mt-2 w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                        @if ($errors->heroContent->has('cta_url'))
                            <p class="mt-1 text-sm text-red-600">{{ $errors->heroContent->first('cta_url') }}</p>
                        @endif
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit"
                        class="rounded-md bg-brand-pink px-6 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-brand-heart">
                        Guardar contenido
                    </button>
                </div>
            </form>
        </div>

        <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-200 px-6 py-5">
                <h2 class="text-lg font-semibold text-gray-900">Slides del hero</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Administrá imágenes, orden y estado activo sin lógica compleja en el frontend.
                </p>
            </div>

            <div class="space-y-8 px-6 py-6">
                <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-5">
                    <h3 class="text-base font-semibold text-gray-900">Nueva slide</h3>
                    <p class="mt-1 text-sm text-gray-500">Podés cargar una nueva imagen y decidir si queda activa.</p>

                    <form action="{{ route('admin.home.hero.slides.store') }}" method="POST" enctype="multipart/form-data"
                        class="mt-5 grid grid-cols-1 gap-5 lg:grid-cols-2">
                        @csrf

                        <div class="lg:col-span-2">
                            <label for="new_slide_image" class="block text-sm font-medium text-gray-700">Imagen</label>
                            <input type="file" name="image" id="new_slide_image" accept="image/*" required
                                class="mt-2 w-full text-sm text-gray-500 file:mr-4 file:rounded-full file:border-0 file:bg-brand-blush file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-pink transition-colors hover:file:bg-brand-pink hover:file:text-white">
                            @if ($errors->createSlide->has('image'))
                                <p class="mt-1 text-sm text-red-600">{{ $errors->createSlide->first('image') }}</p>
                            @endif
                        </div>

                        <div class="lg:col-span-2">
                            <label for="new_slide_alt_text" class="block text-sm font-medium text-gray-700">Alt text</label>
                            <input type="text" name="alt_text" id="new_slide_alt_text" required
                                value="{{ old('alt_text') }}"
                                class="mt-2 w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                            @if ($errors->createSlide->has('alt_text'))
                                <p class="mt-1 text-sm text-red-600">{{ $errors->createSlide->first('alt_text') }}</p>
                            @endif
                        </div>

                        <div>
                            <label for="new_slide_position" class="block text-sm font-medium text-gray-700">Posición</label>
                            <input type="number" name="position" id="new_slide_position" min="0"
                                value="{{ old('position') }}"
                                class="mt-2 w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                            <p class="mt-1 text-xs text-gray-500">Si lo dejás vacío, se agrega al final.</p>
                            @if ($errors->createSlide->has('position'))
                                <p class="mt-1 text-sm text-red-600">{{ $errors->createSlide->first('position') }}</p>
                            @endif
                        </div>

                        <div class="flex items-center">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" id="new_slide_is_active" value="1"
                                class="rounded border-gray-300 text-brand-pink shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50"
                                {{ old('is_active', '1') ? 'checked' : '' }}>
                            <label for="new_slide_is_active" class="ml-3 text-sm font-medium text-gray-700">Crear como activa</label>
                        </div>

                        <div class="lg:col-span-2 flex justify-end">
                            <button type="submit"
                                class="rounded-md bg-brand-pink px-6 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-brand-heart">
                                Agregar slide
                            </button>
                        </div>
                    </form>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-semibold text-gray-900">Slides existentes</h3>
                        <span class="text-sm text-gray-500">{{ $slides->count() }} slide(s) registradas</span>
                    </div>

                    @forelse ($slides as $slide)
                        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                            <div class="grid grid-cols-1 gap-6 lg:grid-cols-[220px_minmax(0,1fr)]">
                                <div>
                                    <div class="overflow-hidden rounded-lg border border-gray-200 bg-gray-50">
                                        <img src="{{ $slide->public_image_url }}" alt="{{ $slide->alt_text }}"
                                            class="h-40 w-full object-cover">
                                    </div>
                                    <div class="mt-3 flex flex-wrap items-center gap-2 text-xs font-medium">
                                        <span
                                            class="inline-flex rounded-full px-2.5 py-1 {{ $slide->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-700' }}">
                                            {{ $slide->is_active ? 'Activa' : 'Inactiva' }}
                                        </span>
                                        <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-gray-700">
                                            Posición {{ $slide->position }}
                                        </span>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <form action="{{ route('admin.home.hero.slides.update', $slide) }}" method="POST"
                                        enctype="multipart/form-data" class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="slide_id" value="{{ $slide->id }}">

                                        @if ((string) old('slide_id') === (string) $slide->id && $errors->updateSlide->any())
                                            <div class="lg:col-span-2 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                                                {{ $errors->updateSlide->first() }}
                                            </div>
                                        @endif

                                        <div class="lg:col-span-2">
                                            <label for="alt_text_{{ $slide->id }}"
                                                class="block text-sm font-medium text-gray-700">Alt text</label>
                                            <input type="text" name="alt_text" id="alt_text_{{ $slide->id }}" required
                                                value="{{ (string) old('slide_id') === (string) $slide->id ? old('alt_text', $slide->alt_text) : $slide->alt_text }}"
                                                class="mt-2 w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                                            @if ((string) old('slide_id') === (string) $slide->id && $errors->updateSlide->has('alt_text'))
                                                <p class="mt-1 text-sm text-red-600">{{ $errors->updateSlide->first('alt_text') }}</p>
                                            @endif
                                        </div>

                                        <div>
                                            <label for="position_{{ $slide->id }}"
                                                class="block text-sm font-medium text-gray-700">Posición</label>
                                            <input type="number" name="position" id="position_{{ $slide->id }}" min="0"
                                                required value="{{ (string) old('slide_id') === (string) $slide->id ? old('position', $slide->position) : $slide->position }}"
                                                class="mt-2 w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                                            @if ((string) old('slide_id') === (string) $slide->id && $errors->updateSlide->has('position'))
                                                <p class="mt-1 text-sm text-red-600">{{ $errors->updateSlide->first('position') }}</p>
                                            @endif
                                        </div>

                                        <div class="flex items-center pt-7">
                                            <input type="hidden" name="is_active" value="0">
                                            <input type="checkbox" name="is_active" id="is_active_{{ $slide->id }}"
                                                value="1"
                                                class="rounded border-gray-300 text-brand-pink shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50"
                                                {{ ((string) old('slide_id') === (string) $slide->id ? old('is_active', $slide->is_active ? '1' : '0') : ($slide->is_active ? '1' : '0')) == '1' ? 'checked' : '' }}>
                                            <label for="is_active_{{ $slide->id }}"
                                                class="ml-3 text-sm font-medium text-gray-700">Slide activa</label>
                                            @if ((string) old('slide_id') === (string) $slide->id && $errors->updateSlide->has('is_active'))
                                                <p class="ml-3 text-sm text-red-600">{{ $errors->updateSlide->first('is_active') }}</p>
                                            @endif
                                        </div>

                                        <div class="lg:col-span-2">
                                            <label for="image_{{ $slide->id }}"
                                                class="block text-sm font-medium text-gray-700">Reemplazar imagen</label>
                                            <input type="file" name="image" id="image_{{ $slide->id }}" accept="image/*"
                                                class="mt-2 w-full text-sm text-gray-500 file:mr-4 file:rounded-full file:border-0 file:bg-brand-blush file:px-4 file:py-2 file:text-sm file:font-semibold file:text-brand-pink transition-colors hover:file:bg-brand-pink hover:file:text-white">
                                            <p class="mt-1 text-xs text-gray-500">Dejalo vacío para mantener la imagen actual.</p>
                                            @if ((string) old('slide_id') === (string) $slide->id && $errors->updateSlide->has('image'))
                                                <p class="mt-1 text-sm text-red-600">{{ $errors->updateSlide->first('image') }}</p>
                                            @endif
                                        </div>

                                        <div class="lg:col-span-2 flex justify-end">
                                            <button type="submit"
                                                class="rounded-md bg-gray-900 px-5 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-gray-800">
                                                Guardar cambios
                                            </button>
                                        </div>
                                    </form>

                                    <div class="border-t border-gray-200 pt-4">
                                        <form action="{{ route('admin.home.hero.slides.destroy', $slide) }}" method="POST"
                                            onsubmit="return confirm('¿Eliminar esta slide del hero?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="text-sm font-semibold text-red-600 transition-colors hover:text-red-700">
                                                Eliminar slide
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 px-5 py-8 text-center text-sm text-gray-500">
                            Todavía no hay slides cargadas para el hero principal.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
