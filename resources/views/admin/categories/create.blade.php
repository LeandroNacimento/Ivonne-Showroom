@extends('layouts.admin')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Nueva Categoría</h1>
            <a href="{{ route('admin.categories.index') }}" class="text-gray-600 hover:text-gray-900">
                &larr; Volver
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                    <input type="text" name="name" id="name"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50"
                        required>
                    @error('name')
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-6 grid grid-cols-2 gap-4">
                    <div class="flex items-center">
                        <input type="checkbox" name="supports_size" id="supports_size"
                            class="rounded border-gray-300 text-brand-pink shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50"
                            value="1">
                        <label for="supports_size" class="ml-2 block text-sm text-gray-900">Soporta Talle</label>
                    </div>
                    <div class="flex items-center">
                        <input type="checkbox" name="supports_color" id="supports_color"
                            class="rounded border-gray-300 text-brand-pink shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50"
                            value="1">
                        <label for="supports_color" class="ml-2 block text-sm text-gray-900">Soporta Color</label>
                    </div>
                </div>

                <div class="mb-6">
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-1">Imagen (Opcional)</label>
                    <input type="file" name="image" id="image"
                        class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-brand-blush file:text-brand-pink hover:file:bg-brand-pink hover:file:text-white transition-colors">
                    @error('image')
                        <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="bg-brand-pink text-white px-6 py-2 rounded-md hover:bg-brand-heart transition-colors">
                        Guardar Categoría
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
