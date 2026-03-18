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

                    {{-- Images by Color (Livewire) --}}
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Imágenes</h2>
                        @livewire('admin.product.images-form', ['product' => $product])
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Detalles</h2>

                        <div class="mb-4">
                            <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                            <select name="category_id" id="category_id"
                                x-on:change="Livewire.dispatch('category-changed', { categoryId: parseInt($event.target.value) })"
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

                    <button type="submit"
                        class="w-full bg-brand-pink text-white px-6 py-3 rounded-md hover:bg-brand-heart transition-colors font-bold shadow-md">
                        Guardar Cambios
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection
