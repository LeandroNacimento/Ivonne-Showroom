@extends('layouts.app')

@section('content')
    <!-- Editorial Header Section (Pure White) -->
    <div class="bg-white border-b border-gray-100">
        <div class="max-w-[90%] mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20">
            <!-- Centered Editorial Header -->
            <div class="text-center reveal">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-script text-brand-pink/90 leading-tight">
                    Catálogo
                </h1>
                <p class="text-gray-400 mt-3 font-medium tracking-[0.2em] uppercase text-xs md:text-sm">
                    Colección Exclusiva
                </p>
            </div>
        </div>
    </div>

    <!-- Catalog Showroom Area (Light Pink Blush) -->
    <div class="bg-brand-blush-soft">
        <div class="max-w-[90%] mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
            <div class="flex flex-col lg:flex-row gap-12">
                <!-- Sidebar (Desktop) -->
                <aside class="hidden lg:block w-64 flex-shrink-0 space-y-12 reveal" style="transition-delay: 200ms;">
                    @include('partials.catalog-sidebar')
                </aside>

                <!-- Main Content Area -->
                <div class="flex-grow">
                    <!-- Grid Header: Mobile Filter Button & Sort -->
                    <div class="flex items-center justify-between mb-8 pb-4 border-b border-gray-100 reveal"
                        style="transition-delay: 100ms;">
                        <button type="button"
                            class="lg:hidden flex items-center gap-2 text-sm font-semibold uppercase tracking-wider text-gray-900 hover:text-brand-pink transition-colors open-filters-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                            </svg>
                            Filtrar
                        </button>

                        <div class="flex items-center gap-2 ml-auto">
                            <span
                                class="text-[11px] uppercase tracking-widest text-gray-600 font-extrabold hidden sm:inline">Ordenar
                                por</span>
                            <div class="relative group">
                                <select name="sort" form="catalog-filters-form"
                                    class="catalog-filter appearance-none bg-transparent py-1 pl-0 pr-8 focus:outline-none text-sm font-semibold text-gray-900 cursor-pointer transition-colors border-b border-transparent hover:border-brand-pink">
                                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Más nuevos
                                    </option>
                                    <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Menor
                                        precio</option>
                                    <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Mayor
                                        precio</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center text-gray-400">
                                    <svg class="h-4 w-4 fill-current" viewBox="0 0 20 20">
                                        <path
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product Grid -->
                    <div id="catalog-products"
                        class="grid grid-cols-2 gap-y-12 gap-x-6 lg:grid-cols-3 xl:grid-cols-4 reveal-stagger-container">
                        @forelse($products as $product)
                            <div class="group reveal-child h-full">
                                <a href="{{ route('product.show', $product->slug) }}"
                                    class="flex flex-col h-full overflow-hidden rounded-2xl bg-white shadow-soft transition-all duration-500 hover:shadow-luxury">
                                    <div class="w-full aspect-[4/5] bg-brand-blush relative overflow-hidden">
                                        @if ($product->cover_url)
                                            <img src="{{ $product->cover_url }}" alt="{{ $product->name }}"
                                                class="w-full h-full object-center object-cover transform transition-transform duration-700 ease-out group-hover:scale-110">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-brand-pink/20">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                        @endif
                                        <div
                                            class="absolute inset-0 bg-black/5 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                                        </div>
                                    </div>

                                    <div class="pt-6 pb-4 px-2 space-y-1">
                                        <p class="text-xl font-bold text-gray-900 leading-tight">
                                            ${{ number_format($product->price, 0, ',', '.') }}
                                        </p>
                                        <h3 class="text-sm font-medium text-gray-600 tracking-wide uppercase">
                                            {{ $product->name }}
                                        </h3>
                                    </div>
                                </a>
                            </div>
                        @empty
                            <div class="col-span-full text-center py-24">
                                <div class="mb-4 inline-block p-6 rounded-full bg-brand-blush/30">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-brand-pink/40"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                            d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7M3 7a2 2 0 012-2h14a2 2 0 012 2M3 7l9 6 9-6" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-medium text-gray-900 mb-2">Sin resultados</h3>
                                <p class="text-gray-500 mb-8 max-w-xs mx-auto">No encontramos prendas que coincidan con tu
                                    búsqueda. Prueba ajustando los filtros.</p>
                                <a href="{{ route('catalog') }}"
                                    class="text-xs font-bold uppercase tracking-widest text-brand-pink hover:text-brand-pink/80 border-b-2 border-brand-pink/20 pb-1">
                                    Ver toda la colección
                                </a>
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    <div class="mt-16 flex justify-center border-t border-gray-100 pt-12">
                        {{ $products->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Filter Drawer -->
    <div id="filter-drawer" class="fixed inset-0 z-[100] invisible pointer-events-none" aria-hidden="true">
        <!-- Backdrop -->
        <div
            class="absolute inset-0 bg-black/20 backdrop-blur-sm opacity-0 transition-opacity duration-500 drawer-backdrop">
        </div>

        <!-- Drawer Panel -->
        <div
            class="absolute inset-y-0 right-0 w-[85%] max-w-sm bg-white shadow-2xl translate-x-full transition-transform duration-500 ease-out drawer-panel flex flex-col">
            <div class="p-6 flex items-center justify-between border-b border-gray-100">
                <h2 class="text-lg font-bold uppercase tracking-tighter">Filtros</h2>
                <button type="button"
                    class="close-filters-btn p-2 -mr-2 text-gray-400 hover:text-gray-900 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="flex-grow overflow-y-auto p-6 space-y-12">
                @include('partials.catalog-sidebar')
            </div>

            <div class="p-6 grid grid-cols-2 gap-4 border-t border-gray-100">
                <a href="{{ route('catalog') }}"
                    class="py-3 px-4 text-center text-xs font-bold uppercase tracking-widest text-gray-400 hover:text-gray-900 transition-colors">
                    Limpiar
                </a>
                <button type="button"
                    class="close-filters-btn py-3 px-4 bg-gray-900 text-white text-xs font-bold uppercase tracking-widest hover:bg-brand-pink transition-colors rounded-lg shadow-lg shadow-black/5">
                    Aplicar
                </button>
            </div>
        </div>
    </div>
@endsection
