<x-layouts.app>
    <div class="bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="lg:grid lg:grid-cols-2 lg:gap-x-8 lg:items-start" x-data="{
                allImages: @js($imagesByColor),
                allVariations: @js(
    $product->variations
        ->map(
            fn($v) => [
                'id' => $v->id,
                'color' => $v->color,
                'size' => $v->size,
                'price' => $v->price,
                'stock' => $v->stock,
            ],
        )
        ->values(),
),
                activeColor: Object.keys(@js($imagesByColor))[0] || @js($product->variations->first()?->color ?? ''),
                selectedVariation: null,
                currentSlide: 0,
            
                get colorNames() {
                    const fromImages = Object.keys(this.allImages);
                    const fromVariations = [...new Set(this.allVariations.map(v => v.color))];
                    return [...new Set([...fromImages, ...fromVariations])];
                },
                get activeImages() {
                    if (this.allImages[this.activeColor] && this.allImages[this.activeColor].length > 0) {
                        return this.allImages[this.activeColor];
                    }
                    // Fallback: first available images from any color
                    const firstColor = Object.keys(this.allImages)[0];
                    return firstColor ? this.allImages[firstColor] : ['{{ $product->cover_url }}'];
                },
                get activeVariations() {
                    return this.allVariations.filter(v => v.color === this.activeColor);
                },
                get selectedPrice() {
                    if (!this.selectedVariation) return null;
                    const v = this.allVariations.find(v => v.id == this.selectedVariation);
                    return v ? v.price : null;
                },
                get selectedStock() {
                    if (!this.selectedVariation) return null;
                    const v = this.allVariations.find(v => v.id == this.selectedVariation);
                    return v ? v.stock : null;
                },
                get minPrice() {
                    if (this.activeVariations.length === 0) return 0;
                    return Math.min(...this.activeVariations.map(v => v.price));
                },
            
                selectColor(color) {
                    this.activeColor = color;
                    this.selectedVariation = null;
                    this.currentSlide = 0;
                },
                formatPrice(price) {
                    return '$' + Number(price).toLocaleString('es-AR', { minimumFractionDigits: 0 });
                }
            }">

                <!-- Image gallery -->
                <div class="flex flex-col-reverse">
                    <div
                        class="w-full aspect-w-1 aspect-h-1 bg-gray-200 rounded-lg overflow-hidden sm:aspect-w-2 sm:aspect-h-3 relative group">

                        <!-- Gallery Container -->
                        <div class="flex overflow-x-auto snap-x snap-mandatory scroll-smooth w-full h-full absolute inset-0 hide-scroll"
                            style="scrollbar-width: none; -ms-overflow-style: none;">
                            <template x-for="(imgUrl, idx) in activeImages" :key="activeColor + '-' + idx">
                                <div class="w-full h-full flex-shrink-0 snap-center">
                                    <img :src="imgUrl" :alt="'{{ $product->name }}'"
                                        class="w-full h-full object-center object-cover">
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
                            <p class="text-3xl text-gray-900" x-show="!selectedPrice">
                                <span class="text-base text-gray-500 font-normal">Desde</span>
                                <span x-text="formatPrice(minPrice)"></span>
                            </p>
                            <p class="text-sm text-gray-500 mt-1" x-show="selectedStock !== null"
                                x-text="'Stock disponible: ' + selectedStock" style="display:none;"></p>
                        </div>
                    </div>

                    <div class="mt-6">
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
                                    class="px-4 py-1.5 rounded-full border text-sm font-medium transition-all duration-200"
                                    :class="activeColor === color ?
                                        'ring-2 ring-brand-pink border-brand-pink bg-pink-50 text-brand-pink' :
                                        'border-gray-300 text-gray-700 hover:border-gray-400 hover:bg-gray-50'"
                                    x-text="color">
                                </button>
                            </template>
                        </div>
                    </div>

                    <div class="mt-8">
                        <template x-if="activeVariations.length > 0">
                            <form action="{{ route('cart.add') }}" method="POST">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">

                                <!-- Variation Selector (filtered by active color) -->
                                <div class="mb-6">
                                    <h3 class="text-sm text-gray-900 font-medium mb-2"
                                        x-text="activeVariations.some(v => v.size !== 'Único') ? 'Seleccioná tu talle:' : 'Seleccioná una opción:'">
                                    </h3>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <template x-for="variation in activeVariations" :key="variation.id">
                                            <label
                                                class="relative border rounded-lg p-4 flex cursor-pointer focus:outline-none transition-all duration-200"
                                                :class="{
                                                    'ring-2 ring-brand-pink border-brand-pink bg-pink-50': selectedVariation ==
                                                        variation.id,
                                                    'hover:bg-gray-50': selectedVariation != variation.id
                                                }">
                                                <input type="radio" name="variation_id" :value="variation.id"
                                                    class="sr-only" x-model="selectedVariation" required>
                                                <div class="flex items-center justify-between w-full">
                                                    <div class="flex items-center">
                                                        <div class="text-sm">
                                                            <p class="font-medium text-gray-900"
                                                                x-text="variation.size !== 'Único' ? variation.size : 'Único'">
                                                            </p>
                                                            <p class="text-gray-500">
                                                                <span x-text="formatPrice(variation.price)"></span>
                                                                <span class="mx-1">·</span>
                                                                <span x-text="'Stock: ' + variation.stock"></span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="ml-4 flex-shrink-0 text-brand-pink"
                                                        x-show="selectedVariation == variation.id"
                                                        style="display: none;">
                                                        <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd"
                                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                                clip-rule="evenodd" />
                                                        </svg>
                                                    </div>
                                                </div>
                                            </label>
                                        </template>
                                    </div>
                                </div>

                                <div class="flex items-center mb-6" x-data="{ qty: 1 }"
                                    x-effect="if (selectedStock !== null && qty > selectedStock) qty = Math.max(1, selectedStock)">
                                    <label for="quantity"
                                        class="mr-4 text-sm font-medium text-gray-700">Cantidad:</label>
                                    <div class="flex items-center border border-gray-300 rounded-md">
                                        <button type="button" @click="qty > 1 ? qty-- : null"
                                            class="px-4 py-2 text-gray-600 hover:bg-gray-100 focus:outline-none transition-colors"
                                            :class="qty <= 1 && 'opacity-30 cursor-not-allowed'">
                                            -
                                        </button>
                                        <input type="number" name="quantity" id="quantity" x-model="qty"
                                            min="1" :max="selectedStock || 99" readonly
                                            class="w-16 border-0 text-center focus:ring-0 p-0 text-gray-900 font-medium">
                                        <button type="button"
                                            @click="(selectedStock === null || qty < selectedStock) ? qty++ : null"
                                            class="px-4 py-2 text-gray-600 hover:bg-gray-100 focus:outline-none transition-colors"
                                            :class="selectedStock !== null && qty >= selectedStock &&
                                                'opacity-30 cursor-not-allowed'">
                                            +
                                        </button>
                                    </div>
                                    <span x-show="selectedStock !== null && qty >= selectedStock"
                                        class="ml-3 text-xs text-amber-600" style="display:none;">
                                        Máximo disponible
                                    </span>
                                </div>

                                <button type="submit"
                                    class="w-full bg-brand-pink border border-transparent rounded-md py-3 px-8 flex items-center justify-center text-base font-medium text-white hover:bg-brand-heart focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-gray-50 focus:ring-brand-pink transition-colors">
                                    Agregar al Pedido
                                </button>
                            </form>
                        </template>

                        <template x-if="activeVariations.length === 0">
                            <p class="text-red-500 font-medium">No hay stock disponible por el momento.</p>
                        </template>

                        <div class="mt-4 text-sm text-gray-500 text-center">
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
                            <div class="group relative">
                                <div
                                    class="w-full min-h-80 bg-gray-200 aspect-w-1 aspect-h-1 rounded-md overflow-hidden group-hover:opacity-75 lg:h-80 lg:aspect-none flex items-center justify-center">
                                    @if ($related->images->first())
                                        <img src="{{ asset('storage/' . $related->images->first()->path) }}"
                                            alt="{{ $related->name }}"
                                            class="w-full h-full object-center object-cover">
                                    @else
                                        <span class="text-gray-400">Imagen</span>
                                    @endif
                                </div>
                                <div class="mt-4 flex justify-between">
                                    <div>
                                        <h3 class="text-sm text-gray-700">
                                            <a href="{{ route('product.show', $related->slug) }}">
                                                <span aria-hidden="true" class="absolute inset-0"></span>
                                                {{ $related->name }}
                                            </a>
                                        </h3>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">
                                        ${{ number_format($related->min_price, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>


</x-layouts.app>
