<x-layouts.app>
    @section('title', 'Contacto - Ivonne Showroom')
    <div class="bg-white">
        <div class="max-w-[90%] mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-gray-900 font-script text-brand-pink">Contáctanos</h1>
                <p class="mt-4 text-lg text-gray-500">Estamos aquí para asesorarte y responder tus dudas.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                <!-- Contact Info -->
                <div class="bg-brand-blush rounded-lg p-8">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-6">Información de Contacto</h2>

                    <div class="space-y-6">
                        <div class="flex items-start">
                            <svg class="h-6 w-6 text-brand-pink mt-1 mr-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <div>
                                <h3 class="font-medium text-gray-900">WhatsApp</h3>
                                <p class="text-gray-600">{{ $settings['whatsapp_number'] ?? '+54 9 370 455-0445' }}</p>
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $settings['whatsapp_number'] ?? '5493704550445') }}"
                                    class="text-brand-pink hover:underline text-sm">Enviar mensaje</a>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <svg class="h-6 w-6 text-brand-pink mt-1 mr-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <div>
                                <h3 class="font-medium text-gray-900">Showroom</h3>
                                <p class="text-gray-600">
                                    {{ $settings['address'] ?? 'Napoleón Uriburu 1366, Formosa Capital' }}</p>
                                <p class="text-gray-500 text-sm">Con cita previa</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <svg class="h-6 w-6 text-brand-pink mt-1 mr-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <h3 class="font-medium text-gray-900">Horarios de Atención</h3>
                                <p class="text-gray-600">
                                    {{ $settings['hours'] ?? 'Lunes a Sábado: 9:00 - 12:30 / 17:00 - 21:00' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Map Placeholder -->
                <!-- Google Map -->
                <div class="rounded-lg overflow-hidden shadow-lg h-full min-h-[300px]">
                    <iframe class="w-full h-full" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"
                        src="https://maps.google.com/maps?q=Ivonne+Showroom+Napole%C3%B3n+Uriburu+1366+Formosa&t=&z=17&ie=UTF8&iwloc=B&output=embed">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
