@extends('layouts.admin')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Editar Producto</h1>
            <a href="{{ route('admin.products.index') }}" class="text-gray-600 hover:text-gray-900">
                &larr; Volver
            </a>
        </div>

        <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Main Info -->
                <div class="md:col-span-2 space-y-6">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Información General</h2>

                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50"
                                required>
                        </div>

                        <div class="mb-4">
                            <label for="description"
                                class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                            <textarea name="description" id="description" rows="4"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">{{ old('description', $product->description) }}</textarea>
                        </div>
                    </div>

                    {{-- Variaciones (Livewire) --}}
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Variaciones</h2>
                        @livewire('admin.product.variations-form', ['product' => $product])
                    </div>

                    {{-- Existing Images --}}
                    @if ($product->images->count())
                        <div class="bg-white rounded-lg shadow-sm p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Imágenes Actuales</h2>
                            <div class="grid grid-cols-4 gap-4">
                                @foreach ($product->images as $image)
                                    <div class="relative group" x-data="{ markedForDelete: false }">
                                        <img src="{{ asset('storage/' . $image->path) }}" alt=""
                                            class="h-24 w-24 object-cover rounded-md"
                                            :class="markedForDelete && 'opacity-30'">
                                        <label class="absolute top-1 right-1 cursor-pointer">
                                            <input type="checkbox" name="delete_images[]" value="{{ $image->id }}"
                                                class="sr-only" x-model="markedForDelete">
                                            <span class="bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full"
                                                :class="markedForDelete ? 'bg-red-700' :
                                                    'bg-red-500 opacity-0 group-hover:opacity-100'"
                                                x-text="markedForDelete ? '✓ Eliminar' : '✕'"></span>
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Detalles</h2>

                        <div class="mb-4">
                            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                            <select name="category_id" id="category_id"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50"
                                required>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        {{ $product->category_id == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="flex items-center mb-4">
                            <input type="checkbox" name="is_featured" id="is_featured"
                                class="rounded border-gray-300 text-brand-pink shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50"
                                {{ $product->is_featured ? 'checked' : '' }}>
                            <label for="is_featured" class="ml-2 block text-sm text-gray-900">Destacar en Home</label>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Agregar Imágenes</h2>
                        <input type="file" name="images[]" multiple
                            class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand-blush file:text-brand-pink hover:file:bg-brand-pink hover:file:text-white transition-colors">
                    </div>

                    <button type="submit"
                        class="w-full bg-brand-pink text-white px-6 py-3 rounded-md hover:bg-brand-heart transition-colors font-bold shadow-md">
                        Guardar Cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
