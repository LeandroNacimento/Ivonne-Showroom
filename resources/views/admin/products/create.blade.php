@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Nuevo Producto</h1>
        <a href="{{ route('admin.products.index') }}" class="text-gray-600 hover:text-gray-900">
            &larr; Volver
        </a>
    </div>

    <div x-data="productForm()">
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
                        <div class="space-y-4">
                            <template x-for="(variation, index) in variations" :key="index">
                                <div class="grid grid-cols-12 gap-4 items-center">
                                    <div class="col-span-4">
                                        <input type="text" :name="`variations[${index}][color]`" x-model="variation.color" placeholder="Color (Ej: Rosa)" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50" required>
                                    </div>
                                    <div class="col-span-4">
                                        <select :name="`variations[${index}][size]`" x-model="variation.size" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50" required>
                                            <option value="" disabled>Seleccionar</option>
                                            <option value="XS">XS</option>
                                            <option value="S">S</option>
                                            <option value="M">M</option>
                                            <option value="L">L</option>
                                            <option value="XL">XL</option>
                                            <option value="XXL">XXL</option>
                                        </select>
                                    </div>
                                    <div class="col-span-3">
                                        <input type="number" :name="`variations[${index}][stock]`" x-model="variation.stock" placeholder="Stock" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50" required>
                                    </div>
                                    <div class="col-span-1 text-center">
                                        <button type="button" @click="removeVariation(index)" class="text-red-500 hover:text-red-700" x-show="variations.length > 1">
                                            <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <button type="button" @click="addVariation()" class="mt-4 text-sm text-brand-pink hover:text-brand-heart font-medium">+ Agregar Variación</button>
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
    function productForm() {
        return {
            variations: [{ color: '', size: '', stock: '' }],

            addVariation() {
                this.variations.push({ color: '', size: '', stock: '' });
            },

            removeVariation(index) {
                this.variations.splice(index, 1);
            }
        }
    }
</script>
@endsection
