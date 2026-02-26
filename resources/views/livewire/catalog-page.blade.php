<div>
    {{-- Wrapper principal --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-16">

        {{-- Sección Superior: Título y Filtros/Ordenamiento --}}
        <div
            class="flex flex-col space-y-6 md:flex-row md:items-center md:justify-between mb-8 pb-6 border-b border-gray-100">
            <div>
                <h1 class="text-3xl font-light tracking-tight text-gray-900">Catálogo</h1>
                <p class="mt-2 text-sm text-gray-500">Última colección exclusiva de Ivonne Showroom</p>
            </div>

            <div class="flex flex-col space-y-4 sm:flex-row sm:space-y-0 sm:space-x-4">
                {{-- Selector de Categoría --}}
                <div class="relative min-w-[200px]">
                    <select wire:model.live="categoryId"
                        class="block w-full appearance-none rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 focus:border-gray-900 focus:outline-none focus:ring-0">
                        <option value="">Todas las prendas</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </div>
                </div>

                {{-- Selector de Ordenamiento --}}
                <div class="relative min-w-[180px]">
                    <select wire:model.live="sort"
                        class="block w-full appearance-none rounded-lg border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-700 focus:border-gray-900 focus:outline-none focus:ring-0">
                        <option value="latest">Novedades</option>
                        <option value="price_asc">Menor Precio</option>
                        <option value="price_desc">Mayor Precio</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Grid de Productos (Mobile: 2 cols, Tablet: 3 cols, Desktop: 4 cols) --}}
        <div class="grid grid-cols-2 gap-x-4 gap-y-10 sm:gap-x-6 md:grid-cols-3 lg:grid-cols-4 lg:gap-x-8">
            @forelse($products as $product)
                {{-- Product Card --}}
                <a href="{{ route('product.show', $product->slug) }}"
                    class="group flex flex-col h-full focus:outline-none">

                    {{-- A) Imagen Principal --}}
                    <div class="relative w-full aspect-[4/5] overflow-hidden bg-stone-50 rounded-xl mb-4">
                        @if ($product->colors->first() && $product->colors->first()->mainImage)
                            <img src="{{ asset('storage/' . $product->colors->first()->mainImage->path) }}"
                                loading="lazy" alt="{{ $product->name }}"
                                class="w-full h-full object-cover object-center transition-transform duration-700 group-hover:scale-105">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-300">
                                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </div>
                        @endif
                    </div>

                    {{-- Cuerpo de Información --}}
                    <div class="flex flex-col flex-grow px-1">

                        {{-- B) Nombre del Producto --}}
                        <div class="flex-grow">
                            <h3 class="text-sm font-medium text-gray-900 line-clamp-2 leading-tight">
                                {{ $product->name }}
                            </h3>
                        </div>

                        {{-- C) Precio Mínimo --}}
                        <div class="mt-2">
                            <p class="text-sm font-bold tracking-tight text-gray-900">
                                Desde ${{ number_format($product->variations_min_price, 0, ',', '.') }}
                            </p>
                        </div>

                        {{-- Atributos Disponibles --}}
                        <div class="mt-3 flex flex-wrap items-center justify-between gap-2">

                            {{-- D) Colores --}}
                            <div class="flex -space-x-1.5">
                                @php $colorCount = 0; @endphp
                                @foreach ($product->colors as $color)
                                    @if ($colorCount < 4)
                                        <span
                                            class="inline-block w-4 h-4 rounded-full ring-2 ring-white shadow-sm border border-gray-100"
                                            style="background-color: {{ $color->hex_code ?? '#e5e7eb' }};"
                                            title="{{ $color->name }}">
                                        </span>
                                    @endif
                                    @php $colorCount++; @endphp
                                @endforeach
                            </div>

                            {{-- E) Talles --}}
                            <div class="flex gap-1">
                                @foreach (array_slice($product->available_sizes, 0, 4) as $size)
                                    <span
                                        class="inline-flex items-center justify-center px-1.5 py-0.5 border border-gray-200 rounded text-[10px] font-semibold text-gray-600 bg-white">
                                        {{ $size }}
                                    </span>
                                @endforeach

                                @if (count($product->available_sizes) > 4)
                                    <span
                                        class="inline-flex items-center justify-center px-1.5 py-0.5 border border-transparent rounded text-[10px] font-bold text-gray-400">
                                        +{{ count($product->available_sizes) - 4 }}
                                    </span>
                                @endif
                            </div>

                        </div>
                    </div>
                </a>
            @empty
                <div class="col-span-full py-24 text-center">
                    <p class="text-sm text-gray-500">No hay productos disponibles por el momento.</p>
                </div>
            @endforelse
        </div>

        {{-- Paginación --}}
        <div class="mt-12">
            {{ $products->links() }}
        </div>
    </div>
</div>
