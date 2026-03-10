<x-layouts.app>
    <div class="bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="lg:grid lg:grid-cols-2 lg:gap-x-8 lg:items-start" x-data="productGallery(@js($imagesByColor), @js($sortedVariations), @js($initialColor))">

                <!-- Image gallery -->
                <div class="flex flex-col-reverse relative">
                    <div class="w-full aspect-[4/5] bg-gray-200 rounded-lg overflow-hidden relative group">

                        <!-- Gallery Container -->
                        <div class="flex overflow-x-auto snap-x snap-mandatory scroll-smooth w-full h-full absolute inset-0 hide-scroll"
                            style="scrollbar-width: none; -ms-overflow-style: none;">
                            <template x-for="(imgUrl, idx) in activeImages" :key="imgUrl">
                                <div class="w-full h-full flex-shrink-0 snap-center flex items-center justify-center">
                                    <img :src="imgUrl" :alt="'{{ $product->name }}'" x-data="{ shown: false }"
                                        x-init="setTimeout(() => shown = true, 10)" x-show="shown"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        class="w-full h-full object-center object-cover transform origin-center">
                                </div>
                            </template>
                        </div>

                        <!-- Badge for multiple images -->
                        <div x-show="activeImages.length > 1"
                            class="absolute bottom-4 right-4 bg-black/50 text-white text-xs px-2 py-1 rounded-md backdrop-blur-sm pointer-events-none">
                            Desliza para ver más
                        </div>
                    </div>
                </div>

                <!-- Product info -->
                <div class="mt-10 px-4 sm:px-0 sm:mt-16 lg:mt-0">
                    <h1 class="text-3xl font-extrabold tracking-tight text-gray-900">{{ $product->name }}</h1>

                    <div class="mt-3">
                        <h2 class="sr-only">Información del producto</h2>
                        <div>
                            <p class="text-3xl font-bold text-gray-900" x-show="selectedPrice"
                                x-text="selectedPrice ? formatPrice(selectedPrice) : ''" style="display:none;"></p>
                            <p class="text-3xl font-bold text-gray-900" x-show="!selectedPrice"
                                x-text="formatPrice(minPrice)"></p>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h3 class="sr-only">Descripción</h3>
                        <div class="text-base text-gray-700 space-y-6">
                            <p>{{ $product->description }}</p>
                        </div>
                    </div>

                    <!-- Color Selector -->
                    <div class="mt-6" x-show="colorNames.length > 1">
                        <h3 class="text-sm text-gray-900 font-medium mb-2">Color:</h3>
                        <div class="flex gap-2 flex-wrap">
                            <template x-for="color in colorNames" :key="color">
                                <button type="button" @click="selectColor(color)"
                                    class="px-4 py-1.5 rounded-full border text-sm font-medium transition-all duration-200 transform hover:scale-110"
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
                            <form action="{{ route('cart.add') }}" method="POST">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">

                                <!-- Variation Selector (filtered by active color) -->
                                <div class="mb-5">
                                    <h3 class="text-sm text-gray-900 font-medium mb-2"
                                        x-text="activeVariations.some(v => v.size !== 'Único') ? 'Seleccioná tu talle:' : 'Seleccioná una opción:'">
                                    </h3>
                                    <div class="flex gap-3 flex-wrap">
                                        <template x-for="variation in activeVariations" :key="variation.id">
                                            <label
                                                class="px-5 py-2 min-w-[3.5rem] border rounded-lg text-sm transition-all duration-200 cursor-pointer focus:outline-none flex items-center justify-center"
                                                :class="{
                                                    'ring-2 ring-brand-pink border-brand-pink bg-brand-blush text-brand-pink': selectedVariation ==
                                                        variation.id,
                                                    'border-gray-300 text-gray-700 hover:border-gray-400 hover:bg-gray-50': selectedVariation !=
                                                        variation.id
                                                }">
                                                <input type="radio" name="variation_id" :value="variation.id"
                                                    class="sr-only" x-model="selectedVariation" required>
                                                <span class="font-medium text-sm"
                                                    x-text="variation.size !== 'Único' ? variation.size : 'Único'">
                                                </span>
                                            </label>
                                        </template>
                                    </div>
                                </div>

                                <div class="flex flex-col items-start mt-6 mb-6" x-data="{ qty: 1 }"
                                    x-effect="if (selectedStock !== null && qty > selectedStock) qty = Math.max(1, selectedStock)">
                                    <label for="quantity"
                                        class="text-sm font-medium text-gray-700 mb-2">Cantidad:</label>
                                    <div
                                        class="flex items-center border border-gray-300 gap-3 rounded-lg px-3 py-2 w-fit">
                                        <button type="button" @click="qty > 1 ? qty-- : null"
                                            class="text-gray-600 hover:text-black focus:outline-none transition-colors flex items-center justify-center w-6 h-6"
                                            :class="qty <= 1 && 'opacity-30 cursor-not-allowed'">
                                            <span class="text-lg font-medium leading-none mb-1">-</span>
                                        </button>
                                        <input type="number" name="quantity" id="quantity" x-model="qty"
                                            min="1" :max="selectedStock || 99" readonly
                                            class="w-8 border-0 bg-transparent text-center focus:ring-0 p-0 text-gray-900 font-semibold text-base">
                                        <button type="button"
                                            @click="(selectedStock === null || qty < selectedStock) ? qty++ : null"
                                            class="text-gray-600 hover:text-black focus:outline-none transition-colors flex items-center justify-center w-6 h-6"
                                            :class="selectedStock !== null && qty >= selectedStock &&
                                                'opacity-30 cursor-not-allowed'">
                                            <span class="text-lg font-medium leading-none mb-1">+</span>
                                        </button>
                                    </div>
                                    <span x-show="selectedStock !== null && qty >= selectedStock"
                                        class="ml-3 text-xs text-amber-600" style="display:none;">
                                        Máximo disponible
                                    </span>
                                </div>

                                <button type="submit"
                                    class="w-full bg-brand-pink border border-transparent rounded-lg py-3.5 px-8 flex items-center justify-center text-base font-semibold text-white hover:bg-brand-heart hover:scale-[1.02] active:scale-95 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-50 focus:ring-brand-pink transition transform duration-200">
                                    Agregar al Pedido
                                </button>
                            </form>
                        </template>

                        <template x-if="activeVariations.length === 0">
                            <p class="text-red-500 font-medium">No hay stock disponible por el momento.</p>
                        </template>

                        <div class="mt-5 text-sm text-gray-500 text-center">
                            <p>El pago y el envío se coordinan directamente por WhatsApp.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            @if ($relatedProducts->count() > 0)
                <div class="mt-16 border-t border-gray-200 pt-10">
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900 font-script text-brand-pink mb-6">
                        También
                        te
                        puede interesar</h2>
                    <div class="grid grid-cols-1 gap-y-10 sm:grid-cols-2 gap-x-6 lg:grid-cols-4 xl:gap-x-8">
                        @foreach ($relatedProducts as $related)
                            <x-product-card :product="$related" />
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>


</x-layouts.app>
