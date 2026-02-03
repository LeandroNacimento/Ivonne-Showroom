<footer class="bg-white border-t border-gray-100">
    <div class="max-w-[90%] mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col items-center text-center">
            <!-- Brand / Contact -->
            <div>
                <h3 class="text-xl font-bold text-gray-900 mb-4">Ivonne Showroom</h3>
                <p class="text-gray-500 text-sm mb-2">
                    {{ $settings['address'] ?? 'Napoleón Uriburu 1366, Formosa Capital' }}
                </p>
                <p class="text-gray-500 text-sm mb-2">
                    WhatsApp: <a
                        href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $settings['whatsapp_number'] ?? '5493704550445') }}"
                        class="hover:text-brand-gold transition-colors">{{ $settings['whatsapp_number'] ?? '+54 9 370 455-0445' }}</a>
                </p>
                <p class="text-gray-500 text-sm">
                    Email: <a href="mailto:{{ $settings['email'] ?? 'contacto@ivonneshowroom.com' }}"
                        class="hover:text-brand-gold transition-colors">{{ $settings['email'] ?? 'contacto@ivonneshowroom.com' }}</a>
                </p>
            </div>
        </div>
        <div class="mt-8 border-t border-gray-200 pt-8 flex justify-center">
            <p class="text-base text-gray-400 text-center">
                &copy; {{ date('Y') }} Ivonne Showroom. Todos los derechos reservados.
            </p>
        </div>
    </div>
</footer>
