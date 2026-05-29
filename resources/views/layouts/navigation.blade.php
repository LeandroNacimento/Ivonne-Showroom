<nav x-data="{ mobileMenuOpen: false }" class="bg-white shadow-sm sticky top-0 z-50 border-b border-gray-100">
    <div class="max-w-[90%] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20">
            <div class="flex items-center">
                <a href="{{ route('home') }}" class="flex-shrink-0 flex items-center gap-2">
                    <img src="{{ asset('img/showroom-logo.png') }}" alt="Ivonne Showroom" class="h-16 w-auto">
                </a>
            </div>

            <div class="hidden md:flex md:items-center md:space-x-8">
                <a href="{{ route('home') }}" data-spy-target="#inicio"
                    class="nav-link text-gray-600 hover:text-brand-gold px-3 py-2 text-sm font-medium transition-colors">Inicio</a>
                <a href="{{ route('catalog') }}" data-spy-target="/catalogo"
                    class="nav-link text-gray-600 hover:text-brand-gold px-3 py-2 text-sm font-medium transition-colors">Catálogo</a>
                <a href="{{ route('home') }}#novedades" data-spy-target="#novedades"
                    class="nav-link text-gray-600 hover:text-brand-gold px-3 py-2 text-sm font-medium transition-colors">Novedades</a>

                <a href="{{ route('contact') }}" data-spy-target="/contacto"
                    class="nav-link text-gray-600 hover:text-brand-gold px-3 py-2 text-sm font-medium transition-colors">Contacto</a>
            </div>

            <div class="flex items-center space-x-4">
                <a href="{{ route('cart') }}"
                    class="relative p-2 text-gray-600 hover:text-brand-gold transition-colors">
                    <livewire:cart-badge />
                </a>

                <!-- Mobile menu button -->
                <div class="flex items-center md:hidden">
                    <button type="button" @click="mobileMenuOpen = !mobileMenuOpen"
                        class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-brand-gold"
                        aria-controls="mobile-menu" :aria-expanded="mobileMenuOpen">
                        <span class="sr-only">Open main menu</span>
                        <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" aria-hidden="true" x-show="!mobileMenuOpen">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" x-show="mobileMenuOpen" style="display: none;">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile menu, show/hide based on menu state. -->
    <!-- Mobile menu, show/hide based on menu state. -->
    <div x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="md:hidden fixed top-20 inset-x-0 bg-white shadow-lg border-b border-gray-100 z-40"
        id="mobile-menu" style="display: none;">
        <div class="pt-4 pb-6 px-6 space-y-4">
            <a href="{{ route('home') }}"
                class="block text-lg font-medium text-gray-800 hover:text-brand-pink border-b border-gray-100 pb-2">Inicio</a>
            <a href="{{ route('catalog') }}"
                class="block text-lg font-medium text-gray-800 hover:text-brand-pink border-b border-gray-100 pb-2">Catálogo</a>
            <a href="{{ route('contact') }}"
                class="block text-lg font-medium text-gray-800 hover:text-brand-pink border-b border-gray-100 pb-2">Contacto</a>
        </div>
    </div>
</nav>
