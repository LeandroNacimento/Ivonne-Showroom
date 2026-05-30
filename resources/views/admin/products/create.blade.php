@extends('layouts.admin')

@section('page_title', 'Nuevo Producto')
@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-gray-900 transition-colors">Resumen</a>
    <span class="mx-2 text-gray-400">/</span>
    <a href="{{ route('admin.products.index') }}" class="hover:text-gray-900 transition-colors">Productos</a>
    <span class="mx-2 text-gray-400">/</span>
    <span class="text-gray-900">Nuevo Producto</span>
@endsection

@section('content')
    <div class="mx-auto max-w-4xl">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-800">Nuevo Producto</h1>
            <a href="{{ route('admin.products.index') }}" class="text-gray-600 hover:text-gray-900">
                &larr; Volver
            </a>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <p class="font-medium">No se pudo guardar el producto.</p>
                @foreach ($errors->all() as $message)
                    <p>{{ $message }}</p>
                @endforeach
            </div>
        @endif

        <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <div class="space-y-6 md:col-span-2">
                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <h2 class="mb-4 text-lg font-semibold text-gray-900">Información General</h2>

                        <div class="mb-4">
                            <label for="name" class="mb-1 block text-sm font-medium text-gray-700">Nombre</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50"
                                required>
                        </div>

                        <div class="mb-4">
                            <label for="description" class="mb-1 block text-sm font-medium text-gray-700">Descripción</label>
                            <textarea name="description" id="description" rows="4"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">{{ old('description') }}</textarea>
                        </div>
                    </div>

                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <h2 class="mb-4 text-lg font-semibold text-gray-900">Variaciones</h2>
                        @livewire('admin.product.variations-form', ['sizeType' => old('size_type', \App\Models\Product::DEFAULT_SIZE_TYPE)])
                    </div>
                </div>

                <div class="space-y-6 lg:sticky lg:top-6 lg:self-start">
                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <h2 class="mb-4 text-lg font-semibold text-gray-900">Detalles</h2>

                        <div class="mb-4">
                            <label for="category_id" class="mb-1 block text-sm font-medium text-gray-700">Categoría</label>
                            <select name="category_id" id="category_id"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50"
                                required>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="size_type" class="mb-1 block text-sm font-medium text-gray-700">Tipo de talles</label>
                            <select name="size_type" id="size_type"
                                x-on:change="Livewire.dispatch('size-type-changed', { sizeType: $event.target.value })"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50"
                                required>
                                @foreach ($sizeTypeOptions as $value => $label)
                                    <option value="{{ $value }}" @selected(old('size_type', \App\Models\Product::DEFAULT_SIZE_TYPE) === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('size_type')
                                <span class="mt-1 block text-xs text-red-500">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-4 flex items-center">
                            <input type="checkbox" name="is_featured" id="is_featured"
                                class="rounded border-gray-300 text-brand-pink shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50"
                                @checked(old('is_featured'))>
                            <label for="is_featured" class="ml-2 block text-sm text-gray-900">Destacar en Home</label>
                        </div>
                    </div>

                    <div class="rounded-lg bg-white p-6 shadow-sm">
                        <h2 class="mb-4 text-lg font-semibold text-gray-900">Imágenes</h2>
                        @livewire('admin.product.images-form')
                    </div>

                    <div x-data="{ showSticky: true }"
                         x-init="
                            let observer = new IntersectionObserver((entries) => {
                                showSticky = !entries[0].isIntersecting;
                            }, { rootMargin: '0px' });
                            $nextTick(() => observer.observe($refs.mainSubmit));
                         ">
                        
                        <!-- Botón Mobile Sticky -->
                        <div x-cloak x-show="showSticky" x-transition.opacity.duration.300ms
                             class="fixed bottom-0 left-0 right-0 z-50 border-t border-gray-200 bg-white/95 p-3 backdrop-blur pb-[max(0.75rem,env(safe-area-inset-bottom))] lg:hidden">
                            <button type="submit"
                                class="w-full rounded-md bg-brand-pink px-6 py-2.5 font-bold text-white shadow-md transition-colors hover:bg-brand-heart">
                                Publicar Producto
                            </button>
                        </div>
    
                        <!-- Botón Principal (Desktop + Destino Scroll Mobile) -->
                        <div class="mt-6 lg:sticky lg:top-6" x-ref="mainSubmit">
                            <button type="submit"
                                class="w-full rounded-md bg-brand-pink px-6 py-3 font-bold text-white shadow-md transition-colors hover:bg-brand-heart">
                                Publicar Producto
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
