@extends('layouts.app')

@section('content')
    <!-- Hero Section -->
    <div id="inicio" class="spy-section relative bg-brand-blush overflow-hidden">
        <div class="max-w-7xl mx-auto">
            <div class="relative z-10 pb-8 bg-brand-blush sm:pb-16 md:pb-20 lg:max-w-2xl lg:w-full lg:pb-28 xl:pb-32">
                <main class="mt-10 mx-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28">
                    <div class="sm:text-center lg:text-left">
                        <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl reveal">
                            <span class="block xl:inline">Estilo y elegancia</span>
                            <span class="block text-brand-gold font-script xl:inline">para ti</span>
                        </h1>
                        <p class="reveal mt-3 text-base text-gray-500 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0"
                            style="transition-delay: 100ms;">
                            Descubre nuestra colección exclusiva de moda femenina. Prendas seleccionadas para resaltar tu
                            belleza y confianza.
                        </p>
                        <div class="reveal mt-5 sm:mt-8 sm:flex sm:justify-center lg:justify-start"
                            style="transition-delay: 200ms;">
                            <div class="rounded-md shadow">
                                <a href="{{ route('catalog') }}"
                                    class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-white bg-brand-pink hover:bg-brand-heart md:py-4 md:text-lg transition-colors">
                                    Ver Catálogo
                                </a>
                            </div>
                            <div class="mt-3 sm:mt-0 sm:ml-3">
                                <a href="{{ route('contact') }}"
                                    class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-md text-brand-pink bg-white hover:bg-gray-50 md:py-4 md:text-lg transition-colors">
                                    Contactar
                                </a>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
        <div class="lg:absolute lg:inset-y-0 lg:right-0 lg:w-1/2 bg-brand-blush flex items-center justify-center reveal-fade"
            style="transition-delay: 300ms;">
            <img src="{{ asset('img/Logo.png') }}" alt="Ivonne Showroom"
                class="h-64 w-auto object-contain mix-blend-multiply">
        </div>
    </div>

    <!-- Categories Section -->
    <div id="categorias" class="spy-section bg-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 text-center mb-8 font-script reveal">Categorías
                Destacadas</h2>
            <div
                class="grid grid-cols-1 gap-y-10 sm:grid-cols-2 gap-x-6 lg:grid-cols-4 xl:gap-x-8 reveal-stagger-container">
                @foreach ($categories as $category)
                    <a href="{{ route('catalog', ['category' => $category->slug]) }}" class="group relative reveal-child">
                        <div
                            class="w-full h-80 bg-gray-200 rounded-lg overflow-hidden group-hover:opacity-75 sm:h-64 aspect-w-1 aspect-h-1 flex items-center justify-center">
                            @if ($category->image)
                                <img src="{{ asset($category->image) }}" alt="{{ $category->name }}"
                                    class="w-full h-full object-center object-cover">
                            @else
                                <span class="text-gray-400">{{ $category->name }}</span>
                            @endif
                        </div>
                        <h3
                            class="mt-4 text-base font-semibold text-gray-900 group-hover:text-brand-gold transition-colors text-center">
                            {{ $category->name }}
                        </h3>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Featured Products Section -->
    <div id="novedades" class="spy-section bg-brand-blush py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-extrabold tracking-tight text-gray-900 text-center mb-8 font-script reveal">Novedades
            </h2>
            <div
                class="grid grid-cols-1 gap-y-10 sm:grid-cols-2 gap-x-6 lg:grid-cols-4 xl:gap-x-8 reveal-stagger-container">
                @foreach ($featuredProducts as $product)
                    <div class="group relative bg-white rounded-lg shadow-sm overflow-hidden reveal-child">
                        <div
                            class="w-full min-h-80 bg-gray-200 aspect-w-1 aspect-h-1 rounded-t-lg overflow-hidden group-hover:opacity-75 lg:h-80 lg:aspect-none flex items-center justify-center">
                            <!-- Placeholder for product image -->
                            <span class="text-gray-400">Imagen Producto</span>
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
                            <p class="text-sm font-medium text-gray-900">${{ number_format($product->price, 0, ',', '.') }}
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
@endsection
