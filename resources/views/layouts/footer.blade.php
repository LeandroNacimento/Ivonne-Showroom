<footer class="bg-white border-t border-gray-100">
    <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Brand -->
            <div class="flex flex-col items-center md:items-start">
                <img src="{{ asset('img/Logo.png') }}" alt="Ivonne Showroom" class="h-20 w-auto mb-4">
                <p class="text-gray-400 text-sm mb-2">
                    {{ $settings['address'] ?? 'Napoleón Uriburu 1366, Formosa Capital' }}
                </p>
                <p class="text-gray-400 text-sm mb-2">
                    WhatsApp: <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $settings['whatsapp_number'] ?? '5493704550445') }}" class="hover:text-brand-pink transition-colors">{{ $settings['whatsapp_number'] ?? '+54 9 370 455-0445' }}</a>
                </p>
                <p class="text-gray-400 text-sm">
                    Email: <a href="mailto:{{ $settings['email'] ?? 'contacto@ivonneshowroom.com' }}" class="hover:text-brand-pink transition-colors">{{ $settings['email'] ?? 'contacto@ivonneshowroom.com' }}</a>
                </p>
            </div>

            <!-- Links -->
            <div class="flex flex-col items-center md:items-start">
                <h3 class="text-sm font-semibold text-gray-400 tracking-wider uppercase">Navegación</h3>
                <ul class="mt-4 space-y-4">
                    <li>
        </div>
    </div>
</footer>
