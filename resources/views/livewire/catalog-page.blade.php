<div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-20">
        <div class="mb-12 border-b border-white/50 pb-8">
            <div class="overflow-x-auto pb-2">
                <div class="inline-flex min-w-full items-center justify-end gap-3 sm:gap-4">
                    <div class="relative min-w-[220px]">
                        <label for="catalog-category" class="sr-only">Categoria</label>
                        <select id="catalog-category" wire:model.live="categoryId"
                            class="block h-11 w-full appearance-none rounded-xl border border-neutral-200/90 bg-white/95 px-4 pr-11 text-sm font-medium text-neutral-800 shadow-[0_10px_24px_-18px_rgba(15,23,42,0.35)] transition duration-200 hover:border-neutral-300 hover:shadow-[0_14px_30px_-20px_rgba(15,23,42,0.42)] focus:border-brand-pink focus:outline-none focus:ring-2 focus:ring-brand-pink/20 cursor-pointer">
                            <option value="">Todas las prendas</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    <div class="relative min-w-[200px]">
                        <label for="catalog-sort" class="sr-only">Ordenar</label>
                        <select id="catalog-sort" wire:model.live="sort"
                            class="block h-11 w-full appearance-none rounded-xl border border-neutral-200/90 bg-white/95 px-4 pr-11 text-sm font-medium text-neutral-800 shadow-[0_10px_24px_-18px_rgba(15,23,42,0.35)] transition duration-200 hover:border-neutral-300 hover:shadow-[0_14px_30px_-20px_rgba(15,23,42,0.42)] focus:border-brand-pink focus:outline-none focus:ring-2 focus:ring-brand-pink/20 cursor-pointer">
                            <option value="latest">Novedades</option>
                            <option value="price_asc">Menor precio</option>
                            <option value="price_desc">Mayor precio</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    <button type="button" wire:click="$toggle('offerOnly')"
                        wire:loading.attr="disabled"
                        wire:target="offerOnly"
                        aria-pressed="{{ $offerOnly ? 'true' : 'false' }}"
                        class="inline-flex h-11 items-center gap-2 rounded-xl border px-4 text-sm font-semibold shadow-[0_10px_24px_-18px_rgba(15,23,42,0.35)] transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-brand-pink/20 focus:ring-offset-2 focus:ring-offset-brand-blush disabled:cursor-not-allowed disabled:opacity-70 {{ $offerOnly ? 'border-brand-pink bg-brand-pink text-white shadow-brand-pink/20 hover:border-brand-heart hover:bg-brand-heart' : 'border-neutral-200/90 bg-white/95 text-neutral-800 hover:border-neutral-300 hover:bg-white hover:shadow-[0_14px_30px_-20px_rgba(15,23,42,0.42)]' }}">
                        <svg class="h-4 w-4 {{ $offerOnly ? 'text-white' : 'text-brand-pink' }}" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7"
                                d="M9 5.25H7.5A2.25 2.25 0 005.25 7.5V9m3.75-3.75l8.25 8.25m0 0A2.25 2.25 0 0117.25 19.5H15a2.25 2.25 0 01-1.59-.66L5.91 11.34A2.25 2.25 0 015.25 9.75V7.5m12 6l1.47-1.47a2.121 2.121 0 000-3l-3.75-3.75a2.121 2.121 0 00-3 0L10.5 6.75" />
                        </svg>
                        <span>Ofertas</span>
                    </button>
                </div>
            </div>
        </div>

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

        <div wire:loading.remove
            class="grid grid-cols-2 gap-x-4 gap-y-12 sm:gap-x-6 md:grid-cols-3 lg:grid-cols-4 lg:gap-x-10 lg:gap-y-16">
            @forelse($products as $product)
                <x-product-card :product="$product" />
            @empty
                <div class="col-span-full py-32 text-center">
                    <p class="text-lg font-light text-gray-400 tracking-wide">
                        La coleccion seleccionada no tiene prendas disponibles en este momento.
                    </p>
                </div>
            @endforelse
        </div>

        <div class="mt-16 sm:mt-24 pagination-custom">
            {{ $products->links() }}
        </div>

        <style>
            .pagination-custom nav p {
                display: none !important;
            }
        </style>
    </div>
</div>
