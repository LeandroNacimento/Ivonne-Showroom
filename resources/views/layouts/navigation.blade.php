<nav class="bg-white shadow-sm sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20">
            <div class="flex items-center">
                <a href="{{ route('home') }}" class="flex-shrink-0 flex items-center gap-2">
                    <img src="{{ asset('img/Logo.png') }}" alt="Ivonne Showroom" class="h-16 w-auto">
                </a>
            </div>
            
            <div class="hidden sm:flex sm:items-center sm:space-x-8">
                <a href="{{ route('home') }}" class="text-gray-600 hover:text-brand-gold px-3 py-2 rounded-md text-sm font-medium transition-colors">Inicio</a>
                <a href="{{ route('catalog') }}" class="text-gray-600 hover:text-brand-gold px-3 py-2 rounded-md text-sm font-medium transition-colors">Catálogo</a>
                <a href="{{ route('about') }}" class="text-gray-600 hover:text-brand-gold px-3 py-2 rounded-md text-sm font-medium transition-colors">Sobre Ivonne</a>
                <a href="{{ route('contact') }}" class="text-gray-600 hover:text-brand-gold px-3 py-2 rounded-md text-sm font-medium transition-colors">Contacto</a>
            </div>

            <div class="flex items-center space-x-4">
                <a href="{{ route('cart') }}" class="relative p-2 text-gray-600 hover:text-brand-gold transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                    @if(session('cart') && count(session('cart')) > 0)
                        <span class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/4 -translate-y-1/4 bg-brand-pink rounded-full">
                            {{ count(session('cart')) }}
                        </span>
                    @endif
                </a>
                
                <!-- Mobile menu button -->
                <div class="flex items-center sm:hidden">
                    <button type="button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-brand-gold" aria-controls="mobile-menu" aria-expanded="false">
                        <span class="sr-only">Open main menu</span>
                        <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile menu, show/hide based on menu state. -->
    <div class="sm:hidden hidden" id="mobile-menu">
        <div class="pt-2 pb-3 space-y-1">
            <a href="{{ route('home') }}" class="bg-brand-blush border-l-4 border-brand-gold text-brand-gold block pl-3 pr-4 py-2 text-base font-medium">Inicio</a>
            <a href="{{ route('catalog') }}" class="border-l-4 border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 block pl-3 pr-4 py-2 text-base font-medium">Catálogo</a>
            <a href="{{ route('about') }}" class="border-l-4 border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 block pl-3 pr-4 py-2 text-base font-medium">Sobre Ivonne</a>
            <a href="{{ route('contact') }}" class="border-l-4 border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 block pl-3 pr-4 py-2 text-base font-medium">Contacto</a>
        </div>
    </div>
</nav>
