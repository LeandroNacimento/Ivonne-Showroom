@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Nuevo Producto</h1>
        <a href="{{ route('admin.products.index') }}" class="text-gray-600 hover:text-gray-900">
            &larr; Volver
        </a>
    </div>

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Main Info -->
            <div class="md:col-span-2 space-y-6">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Información General</h2>
                    
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                        <input type="text" name="name" id="name" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50" required>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                        <textarea name="description" id="description" rows="4" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50"></textarea>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Variaciones (Talle y Color)</h2>
                    <div id="variations-container">
                        <div class="grid grid-cols-3 gap-4 mb-2">
                            <input type="text" name="variations[0][color]" placeholder="Color (Ej: Rosa)" class="rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50" required>
                            <input type="text" name="variations[0][size]" placeholder="Talle (Ej: M)" class="rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50" required>
                            <input type="number" name="variations[0][stock]" placeholder="Stock" class="rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50" required>
                        </div>
                    </div>
                    <button type="button" onclick="addVariation()" class="mt-2 text-sm text-brand-pink hover:text-brand-heart font-medium">+ Agregar Variación</button>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Detalles</h2>
                    
                    <div class="mb-4">
                        <label for="category_id" class="block text-sm font-medium text-gray-700 mb-1">Categoría</label>
                        <select name="category_id" id="category_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50" required>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Precio ($)</label>
                        <input type="number" name="price" id="price" step="0.01" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50" required>
                    </div>

                    <div class="flex items-center mb-4">
                        <input type="checkbox" name="is_featured" id="is_featured" class="rounded border-gray-300 text-brand-pink shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                        <label for="is_featured" class="ml-2 block text-sm text-gray-900">Destacar en Home</label>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Imágenes</h2>
                    <input type="file" name="images[]" multiple class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand-blush file:text-brand-pink hover:file:bg-brand-pink hover:file:text-white transition-colors">
                </div>

                <button type="submit" class="w-full bg-brand-pink text-white px-6 py-3 rounded-md hover:bg-brand-heart transition-colors font-bold shadow-md">
                    Publicar Producto
                </button>
            </div>
        </div>
    </form>
</div>

<script>
    let variationCount = 1;
    function addVariation() {
        const container = document.getElementById('variations-container');
        const div = document.createElement('div');
        div.className = 'grid grid-cols-3 gap-4 mb-2';
        div.innerHTML = `
            <input type="text" name="variations[${variationCount}][color]" placeholder="Color" class="rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50" required>
            <input type="text" name="variations[${variationCount}][size]" placeholder="Talle" class="rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50" required>
            <input type="number" name="variations[${variationCount}][stock]" placeholder="Stock" class="rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50" required>
        `;
        container.appendChild(div);
        variationCount++;
    }
</script>
@endsection
