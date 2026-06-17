@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Configuración General</h1>
        <p class="mt-1 text-sm text-gray-500">Personaliza la información de tu showroom y preferencias del sistema.</p>
    </div>

    <!-- Form -->
    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="space-y-8 divide-y divide-gray-200">
            <!-- General Info Section -->
            <div class="grid grid-cols-1 gap-y-6 gap-x-8 sm:grid-cols-6 pt-2">
                <div class="sm:col-span-2">
                    <h2 class="text-base font-semibold leading-7 text-gray-900">Información del Showroom</h2>
                    <p class="mt-1 text-sm leading-6 text-gray-500">Esta información será visible para tus clientes en el sitio web.</p>
                </div>

                <div class="sm:col-span-4 space-y-6">
                     <div class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-6">
                        <div class="sm:col-span-6">
                            <label for="address" class="block text-sm font-medium leading-6 text-gray-900">Dirección</label>
                            <div class="mt-2">
                                <input type="text" name="address" id="address" value="{{ $settings['address'] ?? '' }}" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-pink sm:text-sm sm:leading-6">
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="hours" class="block text-sm font-medium leading-6 text-gray-900">Horarios de Atención</label>
                            <div class="mt-2">
                                <input type="text" name="hours" id="hours" value="{{ $settings['hours'] ?? '' }}" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-pink sm:text-sm sm:leading-6">
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="email" class="block text-sm font-medium leading-6 text-gray-900">Email de Contacto</label>
                            <div class="mt-2">
                                <input type="email" name="email" id="email" value="{{ $settings['email'] ?? '' }}" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-pink sm:text-sm sm:leading-6">
                            </div>
                        </div>

                         <div class="sm:col-span-6">
                            <label for="logo" class="block text-sm font-medium leading-6 text-gray-900">Logo del Sitio</label>
                            <div class="mt-2 flex items-center gap-x-3">
                                @if(isset($settings['logo_path']))
                                    <img src="{{ asset($settings['logo_path']) }}" alt="Logo Actual" class="h-12 w-auto bg-gray-50 p-1 rounded-md border border-gray-200">
                                @else
                                    <div class="h-12 w-12 rounded-md bg-gray-100 flex items-center justify-center text-gray-400">
                                       <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" /></svg>
                                    </div>
                                @endif
                                <input type="file" name="logo" id="logo" class="block w-full text-sm text-gray-900 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-brand-blush file:text-brand-pink hover:file:bg-brand-pink hover:file:text-white transition-all">
                            </div>
                         </div>
                     </div>
                </div>
            </div>

            <!-- Social Media Section -->
            <div class="grid grid-cols-1 gap-y-6 gap-x-8 sm:grid-cols-6 pt-8">
                <div class="sm:col-span-2">
                    <h2 class="text-base font-semibold leading-7 text-gray-900">Redes Sociales & Contacto</h2>
                    <p class="mt-1 text-sm leading-6 text-gray-500">Conecta tus redes para que los clientes te encuentren.</p>
                </div>

                <div class="sm:col-span-4 space-y-6">
                     <div class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-6">
                        <div class="sm:col-span-3">
                            <label for="whatsapp_number" class="block text-sm font-medium leading-6 text-gray-900">Número de WhatsApp</label>
                            <div class="relative mt-2 rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                   <svg class="h-5 w-5 text-gray-400" viewBox="0 0 24 24" fill="currentColor"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.816 9.816 0 0012.04 2zM12.05 20.21c-1.48 0-2.93-.4-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.264 8.264 0 01-1.26-4.38c0-4.54 3.7-8.24 8.24-8.24 2.2 0 4.27.86 5.82 2.42a8.183 8.183 0 012.41 5.83c.02 4.54-3.68 8.23-8.22 8.23zm4.52-6.16c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.12-.17.25-.66.81-.81.97-.14.17-.29.19-.54.06-.25-.12-1.05-.39-1.99-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.02-.38.11-.51.11-.11.25-.29.37-.43.12-.14.16-.25.25-.41.08-.17.04-.31-.02-.43-.06-.12-.56-1.34-.76-1.84-.2-.48-.41-.42-.56-.43h-.48c-.17 0-.44.06-.67.31-.23.25-.87.85-.87 2.07 0 1.22.89 2.39 1.01 2.56.12.17 1.75 2.67 4.23 3.74.59.26 1.05.41 1.41.52.59.19 1.13.16 1.56.1.48-.07 1.47-.6 1.67-1.18.21-.58.21-1.07.14-1.18-.06-.11-.22-.17-.47-.3z"/></svg>
                                </div>
                                <input type="text" name="whatsapp_number" id="whatsapp_number" value="{{ $settings['whatsapp_number'] ?? '' }}" class="block w-full rounded-md border-0 py-1.5 pl-10 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-pink sm:text-sm sm:leading-6" placeholder="+54 9 ...">
                            </div>
                             <p class="mt-1 text-xs text-gray-500">Esencial para que los clientes te envíen pedidos.</p>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="instagram_url" class="block text-sm font-medium leading-6 text-gray-900">Instagram URL</label>
                            <div class="mt-2">
                                <input type="text" name="instagram_url" id="instagram_url" value="{{ $settings['instagram_url'] ?? '' }}" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-pink sm:text-sm sm:leading-6">
                            </div>
                        </div>

                        <div class="sm:col-span-3">
                            <label for="facebook_url" class="block text-sm font-medium leading-6 text-gray-900">Facebook URL</label>
                             <div class="mt-2">
                                <input type="text" name="facebook_url" id="facebook_url" value="{{ $settings['facebook_url'] ?? '' }}" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-pink sm:text-sm sm:leading-6">
                            </div>
                        </div>
                     </div>
                </div>
            </div>

            <!-- System Info Section -->
            <div class="grid grid-cols-1 gap-y-6 gap-x-8 sm:grid-cols-6 pt-8">
                <div class="sm:col-span-2">
                    <h2 class="text-base font-semibold leading-7 text-gray-900">Configuración del Sistema</h2>
                    <p class="mt-1 text-sm leading-6 text-gray-500">Ajustes internos del funcionamiento del panel.</p>
                </div>

                <div class="sm:col-span-4 space-y-6">
                     <div class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-6">
                         <div class="sm:col-span-3">
                            <label for="min_stock" class="block text-sm font-medium leading-6 text-gray-900">Alerta de Stock Mínimo</label>
                            <div class="mt-2">
                                <input type="number" name="min_stock" id="min_stock" value="{{ $settings['min_stock'] ?? '5' }}" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-pink sm:text-sm sm:leading-6">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Mostrar alerta cuando el stock baje de este número.</p>
                        </div>
                        
                         <div class="sm:col-span-6">
                            <label for="footer_text" class="block text-sm font-medium leading-6 text-gray-900">Texto del Pie de Página</label>
                            <div class="mt-2">
                                <textarea name="footer_text" id="footer_text" rows="3" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-pink sm:text-sm sm:leading-6">{{ $settings['footer_text'] ?? '' }}</textarea>
                            </div>
                        </div>
                     </div>
                </div>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-end gap-x-6 pt-6 border-t border-gray-200">
            <a href="{{ route('admin.dashboard') }}" class="text-sm font-semibold leading-6 text-gray-900 hover:text-gray-700">Cancelar</a>
            <button type="submit" class="rounded-lg bg-brand-pink px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-heart focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-pink transition-colors">
                Guardar Cambios
            </button>
        </div>
    </form>
</div>
@endsection
