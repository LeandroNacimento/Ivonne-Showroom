<div>
    {{-- Wrapper principal: Aumentar espacio vertical (py-12 lg:py-20) --}}
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-20">

        {{-- Sección Superior --}}
        <div class="flex flex-col space-y-8 md:flex-row md:items-end md:justify-end mb-12 pb-8 border-b border-gray-100">
            <div class="flex flex-col space-y-4 sm:flex-row sm:space-y-0 sm:space-x-6">
                {{-- Selector de Categoría --}}
                <div class="relative min-w-[200px]">
                    <select wire:model.live="categoryId"
                        class="block w-full appearance-none bg-white border border-neutral-300 rounded-md px-4 py-2 text-sm text-neutral-800 shadow-sm focus:outline-none focus:ring-1 focus:ring-neutral-400 transition hover:border-neutral-500 cursor-pointer">
                        <option value="">Todas las prendas</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </div>
                </div>

                {{-- Selector de Ordenamiento --}}
                <div class="relative min-w-[180px]">
                    <select wire:model.live="sort"
                        class="block w-full appearance-none bg-white border border-neutral-300 rounded-md px-4 py-2 text-sm text-neutral-800 shadow-sm focus:outline-none focus:ring-1 focus:ring-neutral-400 transition hover:border-neutral-500 cursor-pointer">
                        <option value="latest">Novedades</option>
                        <option value="price_asc">Menor Precio</option>
                        <option value="price_desc">Mayor Precio</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-400">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- Indicador de carga sutil --}}
        <div wire:loading class="text-xs font-light tracking-widest text-gray-400 mb-6 uppercase">
            Actualizando catálogo...
        </div>

        {{-- Grid de Productos --}}
        <div class="grid grid-cols-2 gap-x-4 gap-y-12 sm:gap-x-6 md:grid-cols-3 lg:grid-cols-4 lg:gap-x-10 lg:gap-y-16 transition-opacity duration-300"
            wire:loading.class="opacity-50">
            @forelse($products as $product)
                {{-- Product Card: Transición opacity sutil on hover --}}
                <a href="{{ route('product.show', $product->slug) }}"
                    class="group flex flex-col h-full focus:outline-none transition duration-300 ease-in-out hover:-translate-y-1 hover:shadow-lg">

                    {{-- A) Imagen Principal --}}
                    <div class="relative w-full aspect-[4/5] overflow-hidden bg-stone-50">
                        <img src="{{ $product->mainColor?->image ?? ($product->colors->first()?->image ?? 'https://via.placeholder.com/600x750') }}"
                            loading="lazy" alt="{{ $product->name }}"
                            class="w-full h-full object-cover object-center transition-transform duration-500 ease-out group-hover:scale-[1.03]">
                    </div>

                    {{-- Cuerpo de Información --}}
                    <div class="flex flex-col flex-grow bg-white/90 backdrop-blur-sm p-4 rounded-b-md space-y-1">

                        {{-- B) Nombre del Producto --}}
                        <div class="flex-grow">
                            <h3 class="text-lg font-semibold text-neutral-900 line-clamp-2 leading-relaxed">
                                {{ $product->name }}
                            </h3>
                        </div>

                        {{-- C) Precio Mínimo --}}
                        <div class="mt-1">
                            <p class="text-base font-semibold text-neutral-800">
                                Desde ${{ number_format($product->variations_min_price, 0, ',', '.') }}
                            </p>
                        </div>

                        {{-- Atributos Disponibles --}}
                        <div class="mt-2 space-y-1">
                            <p class="text-sm font-medium text-neutral-700 mt-2">
                                {{ $product->availability_label }}
                            </p>

                            <p class="text-xs text-neutral-500 mt-1">
                                {{ $product->colors->count() }} colores disponibles
                            </p>
                        </div>
                    </div>
                </a>
            @empty
                {{-- Estado Empty Premium --}}
                <div class="col-span-full py-32 text-center">
                    <p class="text-lg font-light text-gray-400 tracking-wide">
                        La colección seleccionada no tiene prendas disponibles en este momento.
                    </p>
                </div>
            @endforelse
        </div>

        {{-- Paginación --}}
        <div class="mt-16 sm:mt-24">
            {{ $products->links() }}
        </div>
    </div>
</div>
