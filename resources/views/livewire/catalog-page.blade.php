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

                <label
                    class="inline-flex items-center gap-2 rounded-md border border-neutral-300 px-4 py-2 text-sm text-neutral-800 shadow-sm transition hover:border-neutral-500">
                    <input type="checkbox" wire:model.live="offerOnly"
                        class="rounded border-neutral-300 text-brand-pink focus:ring-brand-pink">
                    <span>En oferta</span>
                </label>
            </div>
        </div>

        {{-- Skeleton Loader para Livewire --}}
        <div wire:loading class="w-full">
            <div
                class="grid grid-cols-2 gap-x-4 gap-y-12 sm:gap-x-6 md:grid-cols-3 lg:grid-cols-4 lg:gap-x-10 lg:gap-y-16">
                @for ($i = 0; $i < 8; $i++)
                    <div class="flex flex-col h-full bg-white rounded-md">
                        <div class="w-full aspect-[4/5] bg-gray-200 rounded-t-md animate-pulse"></div>
                        <div class="p-4 space-y-4">
                            <div class="flex space-x-1.5 mb-2">
                                <div class="w-4 h-4 rounded-full bg-gray-200 animate-pulse"></div>
                                <div class="w-4 h-4 rounded-full bg-gray-200 animate-pulse"></div>
                                <div class="w-4 h-4 rounded-full bg-gray-200 animate-pulse"></div>
                            </div>
                            <div class="space-y-2">
                                <div class="h-4 bg-gray-200 rounded animate-pulse w-full"></div>
                                <div class="h-4 bg-gray-200 rounded animate-pulse w-2/3"></div>
                            </div>
                            <div class="space-y-2 mt-4 pt-2">
                                <div class="h-5 bg-gray-200 rounded animate-pulse w-1/3"></div>
                                <div class="h-3 bg-gray-200 rounded animate-pulse w-1/2"></div>
                            </div>
                        </div>
                    </div>
                @endfor
            </div>
        </div>

        {{-- Grid de Productos --}}
        <div wire:loading.remove
            class="grid grid-cols-2 gap-x-4 gap-y-12 sm:gap-x-6 md:grid-cols-3 lg:grid-cols-4 lg:gap-x-10 lg:gap-y-16">
            @forelse($products as $product)
                <x-product-card :product="$product" />
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
        <div class="mt-16 sm:mt-24 pagination-custom">
            {{ $products->links() }}
        </div>

        <style>
            .pagination-custom nav p {
                display: none !important;
            }
        </style>
    </div>

    {{-- Componente Sticky Mini Cart --}}
</div>
