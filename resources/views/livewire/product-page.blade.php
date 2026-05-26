<div class="bg-white">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="lg:grid lg:grid-cols-2 lg:items-start lg:gap-x-8"
            x-data="productGallery(
                @js($imagesByColor),
                @js($sortedVariations),
                @js($initialColor),
                @js([
                    'price' => $product->display_price,
                    'originalPrice' => $product->display_original_price,
                    'hasActiveOffer' => $product->display_has_active_offer,
                ])
            )">

            <div class="relative flex flex-col-reverse">
                <div class="relative aspect-[4/5] w-full overflow-hidden rounded-lg bg-gray-200">
                    <div x-ref="galleryScroll" @scroll.passive="updateIndexFromScroll" class="hide-scroll absolute inset-0 flex h-full w-full snap-x snap-mandatory overflow-x-auto scroll-smooth"
                        style="scrollbar-width: none; -ms-overflow-style: none;">
                        <template x-for="(imgUrl, idx) in activeImages" :key="imgUrl + '-' + idx">
                            <div class="flex h-full w-full flex-shrink-0 snap-center items-center justify-center">
                                <img :src="imgUrl" :alt="'{{ $product->name }}'" x-data="{ shown: false }"
                                    x-init="setTimeout(() => shown = true, 10)" x-show="shown"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 scale-95"
                                    x-transition:enter-end="opacity-100 scale-100"
                                    class="h-full w-full origin-center object-cover object-center" loading="lazy">
                            </div>
                        </template>
                    </div>

                    <div class="pointer-events-none absolute inset-0 flex items-center justify-between p-2 hidden md:flex" x-show="activeImages.length > 1">
                        <button type="button" @click="prev()"
                            class="pointer-events-auto flex h-10 w-10 items-center justify-center rounded-full bg-white/80 text-gray-800 shadow-sm opacity-40 hover:opacity-100 disabled:opacity-0 disabled:cursor-not-allowed transition-all duration-200"
                            :disabled="currentImageIndex === 0" aria-label="Anterior">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                        </button>
                        <button type="button" @click="next()"
                            class="pointer-events-auto flex h-10 w-10 items-center justify-center rounded-full bg-white/80 text-gray-800 shadow-sm opacity-40 hover:opacity-100 disabled:opacity-0 disabled:cursor-not-allowed transition-all duration-200"
                            :disabled="currentImageIndex === activeImages.length - 1" aria-label="Siguiente">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                    </div>

                    <div x-show="activeImages.length > 1"
                        class="pointer-events-none absolute bottom-4 right-4 rounded-md bg-black/50 px-2 py-1 text-xs text-white backdrop-blur-sm">
                        Desliza para ver más
                    </div>
                </div>
            </div>

            <div class="mt-10 px-4 sm:mt-16 sm:px-0 lg:mt-0">
                <h1 class="text-3xl font-extrabold tracking-tight text-gray-900">{{ $product->name }}</h1>

                <div class="mt-3 space-y-2">
                    <h2 class="sr-only">Información del producto</h2>
                    <span x-show="displayPricing?.hasActiveOffer"
                        class="inline-flex rounded bg-brand-pink px-2 py-1 text-xs font-bold uppercase tracking-wide text-white"
                        style="display:none;">
                        Oferta
                    </span>
                    <div class="flex flex-wrap items-center gap-3" x-show="displayPricing?.price !== null"
                        style="display:none;">
                        <p class="text-lg text-gray-400 line-through"
                            x-show="displayPricing?.originalPrice !== null && displayPricing?.originalPrice > displayPricing?.price"
                            x-text="formatPrice(displayPricing?.originalPrice ?? 0)" style="display:none;"></p>
                        <p class="text-3xl font-bold text-gray-900"
                            x-text="formatPrice(displayPricing?.price ?? 0)"></p>
                    </div>
                </div>

                <div class="mt-4">
                    <h3 class="sr-only">Descripción</h3>
                    <div class="space-y-6 text-base text-gray-700">
                        <p>{{ $product->description }}</p>
                    </div>
                </div>

                <div class="mt-6" x-show="colorNames.length > 1">
                    <h3 class="mb-2 text-sm font-medium text-gray-900">Color:</h3>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="color in colorNames" :key="color">
                            <button type="button" @click="selectColor(color)"
                                class="rounded-full border px-4 py-1.5 text-sm font-medium transition-all duration-200 hover:scale-110"
                                :class="activeColor === color ?
                                    'ring-2 ring-brand-pink border-brand-pink bg-brand-blush text-brand-pink' :
                                    'border-gray-300 text-gray-700 hover:border-gray-400 hover:bg-gray-50'"
                                x-text="color">
                            </button>
                        </template>
                    </div>
                </div>

                <div class="mt-6">
                    <template x-if="activeVariations.length > 0">
                        <form x-data="{ qty: 1 }" @submit.prevent="$wire.addToCart(selectedVariation, qty)">
                            @if ($product->has_sizes)
                                <div class="mb-5">
                                    <h3 class="mb-2 text-sm font-medium text-gray-900">Seleccioná tu talle:</h3>
                                    <div class="flex flex-wrap gap-3">
                                        <template x-for="variation in activeVariations" :key="variation.id">
                                            <label
                                                class="flex min-w-[3.5rem] cursor-pointer items-center justify-center rounded-lg border px-5 py-2 text-sm transition-all duration-200 focus:outline-none"
                                                :class="{
                                                    'ring-2 ring-brand-pink border-brand-pink bg-brand-blush text-brand-pink': selectedVariation == variation.id,
                                                    'border-gray-300 text-gray-700 hover:border-gray-400 hover:bg-gray-50': selectedVariation != variation.id
                                                }">
                                                <input type="radio" name="variation_id" :value="variation.id"
                                                    class="sr-only" x-model="selectedVariation" required>
                                                <span class="text-sm font-medium" x-text="variation.size_label"></span>
                                            </label>
                                        </template>
                                    </div>
                                </div>
                            @else
                                <div x-init="$watch('activeVariations', () => { if (activeVariations.length > 0) selectedVariation = activeVariations[0].id });
                                if (activeVariations.length > 0) selectedVariation = activeVariations[0].id" style="display:none;"></div>
                            @endif

                            <div class="mb-6 mt-6 flex flex-col items-start"
                                x-effect="if (selectedStock !== null && qty > selectedStock) qty = Math.max(1, selectedStock)">
                                <label for="quantity" class="mb-2 text-sm font-medium text-gray-700">Cantidad:</label>
                                <div class="flex w-fit items-center gap-3 rounded-lg border border-gray-300 px-3 py-2">
                                    <button type="button" @click="qty > 1 ? qty-- : null"
                                        class="flex h-6 w-6 items-center justify-center text-gray-600 transition-colors hover:text-black focus:outline-none"
                                        :class="qty <= 1 && 'opacity-30 cursor-not-allowed'">
                                        <span class="mb-1 text-lg font-medium leading-none">-</span>
                                    </button>
                                    <input type="number" name="quantity" id="quantity" x-model="qty" min="1"
                                        :max="selectedStock || 99" readonly
                                        class="w-8 border-0 bg-transparent p-0 text-center text-base font-semibold text-gray-900 focus:ring-0">
                                    <button type="button"
                                        @click="(selectedStock === null || qty < selectedStock) ? qty++ : null"
                                        class="flex h-6 w-6 items-center justify-center text-gray-600 transition-colors hover:text-black focus:outline-none"
                                        :class="selectedStock !== null && qty >= selectedStock &&
                                            'opacity-30 cursor-not-allowed'">
                                        <span class="mb-1 text-lg font-medium leading-none">+</span>
                                    </button>
                                </div>
                                <span x-show="selectedStock !== null && qty >= selectedStock"
                                    class="ml-3 text-xs text-amber-600" style="display:none;">
                                    Máximo disponible
                                </span>
                            </div>

                            <button type="submit"
                                class="flex w-full items-center justify-center rounded-lg border border-transparent bg-brand-pink px-8 py-3.5 text-base font-semibold text-white transition duration-200 hover:scale-[1.02] hover:bg-brand-heart focus:outline-none focus:ring-2 focus:ring-brand-pink focus:ring-offset-2 focus:ring-offset-gray-50 active:scale-95 disabled:cursor-not-allowed disabled:opacity-75"
                                wire:loading.attr="disabled" wire:target="addToCart">
                                <span wire:loading.remove wire:target="addToCart">Agregar al Pedido</span>
                                <span wire:loading.flex wire:target="addToCart"
                                    class="items-center justify-center gap-2">
                                    <svg class="h-5 w-5 animate-spin text-white" xmlns="http://www.w3.org/2000/svg"
                                        fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10"
                                            stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                        </path>
                                    </svg>
                                    Agregando...
                                </span>
                            </button>
                        </form>
                    </template>

                    <template x-if="activeVariations.length === 0">
                        <p class="font-medium text-red-500">No hay stock disponible por el momento.</p>
                    </template>

                    <div class="mt-5 text-center text-sm text-gray-500">
                        <p>El pago y el envío se coordinan directamente por WhatsApp.</p>
                    </div>
                </div>
            </div>
        </div>

        @if ($relatedProducts->count() > 0)
            <div class="mt-16 border-t border-gray-200 pt-10">
                <h2 class="mb-6 font-script text-2xl font-bold tracking-tight text-brand-pink text-gray-900">
                    También te puede interesar
                </h2>
                <div class="grid grid-cols-1 gap-x-6 gap-y-10 sm:grid-cols-2 lg:grid-cols-4 xl:gap-x-8">
                    @foreach ($relatedProducts as $related)
                        <x-product-card :product="$related" />
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
