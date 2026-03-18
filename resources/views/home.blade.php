<x-layouts.app>
    <!-- Hero Section -->
    <div id="inicio"
        class="spy-section relative w-full h-[85vh] min-h-[600px] bg-cover bg-center bg-no-repeat lg:bg-right-top"
        style="background-image: url('{{ asset('img/imgHero.png') }}');">
        <!-- Dark Overlay -->
        <div class="absolute inset-0 bg-black/60"></div>

        <!-- Overlay for readability -->
        <div
            class="absolute inset-0 bg-gradient-to-r from-white/90 via-white/40 to-transparent sm:from-white/95 sm:via-white/25">
        </div>

        <div class="relative w-full h-full flex items-center max-w-[90%] mx-auto px-4 sm:px-6 lg:px-8">
            <main class="lg:w-1/2 xl:w-2/5">
                <div class="sm:text-center lg:text-left">
                    <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl reveal">
                        <span class="block xl:inline">Estilo y elegancia</span>
                        <span class="block text-brand-pink font-script xl:inline mt-2">para vos</span>
                    </h1>
                    <p class="reveal mt-4 text-base text-gray-700 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0 font-medium"
                        style="transition-delay: 100ms;">
                        Descubre nuestra colección exclusiva. Prendas seleccionadas con amor para resaltar tu belleza y
                        confianza en cada paso.
                    </p>
                    <div class="reveal mt-8 sm:flex sm:justify-center lg:justify-start gap-4"
                        style="transition-delay: 200ms;">

                        <div class="rounded-full shadow-lg">
                            <a href="{{ route('catalog') }}"
                                class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-full text-white bg-brand-pink hover:bg-brand-heart hover:scale-105 transform transition-all duration-300 md:py-4 md:text-lg md:px-10">
                                Ver Catálogo
                            </a>
                        </div>

                        <div class="mt-3 sm:mt-0">
                            <a href="{{ route('contact') }}"
                                class="w-full flex items-center justify-center px-8 py-3 border-2 border-brand-pink text-base font-medium rounded-full text-brand-pink bg-white/50 backdrop-blur-sm hover:bg-brand-pink hover:text-white md:py-4 md:text-lg md:px-10 transition-all duration-300">
                                Contactar
                            </a>
                        </div>

                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Categories Section -->
    <div id="categorias" class="spy-section bg-white py-12" x-data="categoriesCarousel">
        <style>
            .hide-scrollbar::-webkit-scrollbar {
                display: none;
            }

            .hide-scrollbar {
                -ms-overflow-style: none;
                scrollbar-width: none;
            }
        </style>
        <div class="max-w-[90%] mx-auto px-4 sm:px-6 lg:px-8 relative group/carousel">
            <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 text-center mb-8 font-script reveal">
                Categorías
                Destacadas</h2>

            <!-- Left Button -->
            <button @click="scrollCategories('left')"
                class="absolute left-0 top-1/2 -translate-y-1/2 z-50 bg-white/90 hover:bg-white p-2 rounded-full shadow-xl backdrop-blur-sm text-gray-800 transition-all opacity-0 group-hover/carousel:opacity-100 -ml-2 lg:-ml-4 hidden md:flex items-center justify-center border border-gray-100 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
            </button>

            <!-- Carousel Track -->
            <div x-ref="container" class="flex overflow-x-auto gap-4 pb-4 hide-scrollbar">

                {{-- ORIGINALS --}}
                @foreach ($categories as $category)
                    <a href="{{ route('catalog', ['category' => $category->slug]) }}"
                        class="min-w-[30%] lg:min-w-[24%] group relative flex-shrink-0 block">
                        <div
                            class="w-full h-64 xl:h-80 bg-gray-200 rounded-lg overflow-hidden group-hover:opacity-75 relative">
                            @if ($category->image)
                                <img src="{{ asset('storage/' . $category->image) }}" alt="{{ $category->name }}"
                                    class="w-full h-full object-center object-cover absolute inset-0">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <span class="text-gray-400">{{ $category->name }}</span>
                                </div>
                            @endif
                        </div>
                        <h3
                            class="mt-4 text-base font-semibold text-gray-900 group-hover:text-brand-gold transition-colors text-center">
                            {{ $category->name }}
                        </h3>
                    </a>
                @endforeach
            </div>

            <!-- Right Button -->
            <button @click="scrollCategories('right')"
                class="absolute right-0 top-1/2 -translate-y-1/2 z-50 bg-white/90 hover:bg-white p-2 rounded-full shadow-xl backdrop-blur-sm text-gray-800 transition-all opacity-0 group-hover/carousel:opacity-100 -mr-2 lg:-mr-4 hidden md:flex items-center justify-center border border-gray-100 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </button>
        </div>
    </div>

    <!-- Featured Products Section -->
    <div id="novedades" class="spy-section bg-brand-blush py-12">
        <div class="max-w-[90%] mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 text-center mb-8 font-script reveal">
                Novedades
            </h2>
            <div
                class="grid grid-cols-1 gap-y-10 sm:grid-cols-2 gap-x-6 lg:grid-cols-4 xl:gap-x-8 reveal-stagger-container">
                @foreach ($featuredProducts as $product)
                    <div class="group relative bg-white rounded-lg shadow-sm overflow-hidden reveal-child">
                        <div
                            class="w-full min-h-80 bg-gray-200 aspect-w-1 aspect-h-1 rounded-t-lg overflow-hidden group-hover:opacity-75 lg:h-80 lg:aspect-none flex items-center justify-center">
                            <img src="{{ $product->cover_url }}" alt="{{ $product->name }}"
                                class="w-full h-full object-center object-cover">
                        </div>
                        <div class="mt-4 flex justify-between px-4 pb-4">
                            <div>
                                <h3 class="text-sm text-gray-700">
                                    <a href="{{ route('product.show', $product->slug) }}">
                                        <span aria-hidden="true" class="absolute inset-0"></span>
                                        {{ $product->name }}
                                    </a>
                                </h3>
                                <p class="mt-1 text-sm text-gray-500">{{ $product->category->name }}</p>
                            </div>
                            <p class="text-sm font-medium text-gray-900">
                                ${{ number_format($product->min_price, 0, ',', '.') }}
                            </p>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="mt-10 text-center reveal">
                <a href="{{ route('catalog') }}"
                    class="inline-block bg-white border border-transparent rounded-md py-3 px-8 font-medium text-brand-pink hover:bg-gray-50 shadow-sm">Ver
                    todo el catálogo</a>
            </div>
        </div>
    </div>
</x-layouts.app>
