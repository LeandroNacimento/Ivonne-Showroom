@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Configuración General</h1>
    </div>

    <div class="bg-white rounded-lg shadow-sm p-6">
        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <!-- General Info -->
            <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Información del Showroom</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                    <input type="text" name="address" id="address" value="{{ $settings['address'] ?? '' }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                </div>
                <div>
                    <label for="hours" class="block text-sm font-medium text-gray-700 mb-1">Horarios de Atención</label>
                    <input type="text" name="hours" id="hours" value="{{ $settings['hours'] ?? '' }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email de Contacto</label>
                    <input type="email" name="email" id="email" value="{{ $settings['email'] ?? '' }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                </div>
                <div>
                    <label for="logo" class="block text-sm font-medium text-gray-700 mb-1">Logo del Sitio</label>
                    <input type="file" name="logo" id="logo" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                    @if(isset($settings['logo_path']))
                        <div class="mt-2">
                            <p class="text-xs text-gray-500 mb-1">Logo actual:</p>
                            <img src="{{ asset($settings['logo_path']) }}" alt="Logo Actual" class="h-12 w-auto">
                        </div>
                    @endif
                </div>
            </div>

            <!-- Social Media -->
            <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Redes Sociales & Contacto</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="whatsapp_number" class="block text-sm font-medium text-gray-700 mb-1">Número de WhatsApp</label>
                    <input type="text" name="whatsapp_number" id="whatsapp_number" value="{{ $settings['whatsapp_number'] ?? '' }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                    <p class="text-xs text-gray-500 mt-1">Formato: +54 9 370 455-0445</p>
                </div>
                <div>
                    <label for="instagram_url" class="block text-sm font-medium text-gray-700 mb-1">Instagram URL</label>
                    <input type="text" name="instagram_url" id="instagram_url" value="{{ $settings['instagram_url'] ?? '' }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                </div>
                <div>
                    <label for="facebook_url" class="block text-sm font-medium text-gray-700 mb-1">Facebook URL</label>
                    <input type="text" name="facebook_url" id="facebook_url" value="{{ $settings['facebook_url'] ?? '' }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                </div>
            </div>

            <!-- System Config -->
            <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Configuración del Sistema</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="min_stock" class="block text-sm font-medium text-gray-700 mb-1">Alerta de Stock Mínimo</label>
                    <input type="number" name="min_stock" id="min_stock" value="{{ $settings['min_stock'] ?? '5' }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                    <p class="text-xs text-gray-500 mt-1">Cantidad mínima para mostrar alerta en dashboard.</p>
                </div>
                <div class="md:col-span-2">
                    <label for="footer_text" class="block text-sm font-medium text-gray-700 mb-1">Texto del Pie de Página</label>
                    <textarea name="footer_text" id="footer_text" rows="2" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">{{ $settings['footer_text'] ?? '' }}</textarea>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-brand-pink text-white px-6 py-2 rounded-md hover:bg-brand-heart transition-colors">
                    Guardar Configuración
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
