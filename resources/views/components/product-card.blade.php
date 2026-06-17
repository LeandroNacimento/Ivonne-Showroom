@props(['product', 'offerOnly' => false, 'eager' => false, 'priority' => false, 'compact' => false])

@php
    // Aseguramos reindexación limpia desde el inicio
    $colorsToProcess = $product->colors->values();

    if ($offerOnly) {
        $colorsToProcess = $colorsToProcess
            ->filter(fn ($color) => $color->hasActiveOffer())
            ->values();
    }

    $images = $colorsToProcess->map(fn ($color) => $color->public_primary_image_url)->values()->toArray();

    if ($images === []) {
        $images = [asset('img/placeholder-product.jpg')];
    }

    $colorsData = $colorsToProcess
        ->map(function ($color) use ($offerOnly) {
            $variation = null;
            
            if ($offerOnly) {
                $variation = $color->resolvePrimaryOfferVariation();
            }
            
            if (!$variation) {
                $variation = $color->resolvePrimaryVariation();
            }

            $price = $variation ? (float) $variation->effective_price : null;
            $originalPrice = $variation && $variation->sale_price !== null ? (float) $variation->price : null;
            $hasOffer = $variation && $variation->sale_price !== null && (float) $variation->sale_price > 0 && (float) $variation->sale_price < (float) $variation->price;

            return [
                'id' => $color->id,
                'name' => $color->name,
                'image' => $color->public_primary_image_url,
                'price' => $price,
                'original_price' => $originalPrice,
                'has_offer' => $hasOffer,
                'formatted_price' => $price !== null ? '$' . number_format($price, 0, ',', '.') : null,
                'formatted_original_price' => $originalPrice !== null ? '$' . number_format($originalPrice, 0, ',', '.') : null,
            ];
        })
        ->values() // Doble seguridad de reindexado
        ->toArray();

    $uniqueColors = $colorsToProcess->unique('name')->values();
    $visibleColors = $uniqueColors->take(4);
    $extraColorsCount = $uniqueColors->count() - 4;

    $defaultIndex = 0;
    if ($offerOnly) {
        $foundIndex = collect($colorsData)->search(fn($c) => $c['has_offer'] === true);
        if ($foundIndex !== false) {
            $defaultIndex = $foundIndex;
        }
    }

    // Fallback seguro si la colección se vació por completo
    if (!isset($colorsData[$defaultIndex]) && count($colorsData) > 0) {
        $defaultIndex = 0;
    }

    $defaultColor = $colorsData[$defaultIndex] ?? null;
    $defaultPrice = $defaultColor['price'] ?? null;
    $defaultOriginalPrice = $defaultColor['original_price'] ?? null;
    $defaultHasOffer = $defaultColor['has_offer'] ?? false;
@endphp

<div x-data="{
    images: {{ json_encode($images) }},
    colorsData: {{ json_encode($colorsData) }},
    currentIndex: {{ $defaultIndex }},
    timer: null,
    previewIndex: null,
    previewTimeout: null,
    isTouchDevice: window.matchMedia('(hover: none)').matches,
    destroy() {
        clearTimeout(this.previewTimeout);
        if (this.timer) {
            clearInterval(this.timer);
            this.timer = null;
        }
    },
    get activeColor() {
        let idx = this.previewIndex !== null ? this.previewIndex : this.currentIndex;
        return this.colorsData[idx] || null;
    },
    get currentVisibleIndex() {
        return this.previewIndex !== null ? this.previewIndex : this.currentIndex;
    },
    startCarousel() {
        if (this.timer) return;
        if (this.images.length > 1 && this.previewIndex === null && !this.isTouchDevice) {
            this.timer = setInterval(() => {
                this.currentIndex = (this.currentIndex + 1) % this.images.length;
            }, 2000);
        }
    },
    stopCarousel() {
        if (this.isTouchDevice) return;
        if (this.timer) {
            clearInterval(this.timer);
            this.timer = null;
        }
        this.currentIndex = 0;
    },
    setPreview(index) {
        clearTimeout(this.previewTimeout);
        if (this.timer) {
            clearInterval(this.timer);
            this.timer = null;
        }
        this.previewIndex = index;
    },
    clearPreviewDelay() {
        clearTimeout(this.previewTimeout);
        this.previewTimeout = setTimeout(() => {
            this.previewIndex = null;
            if (!this.isTouchDevice) {
                this.startCarousel();
            }
        }, 2500);
    },
    clearPreview() {
        if (this.isTouchDevice) return;
        this.previewIndex = null;
        this.startCarousel();
    }
}" @mouseenter="startCarousel()" @mouseleave="stopCarousel()"
    {{ $attributes->merge(['class' => 'group relative flex h-full flex-col rounded-md bg-white product-card-hover ' . ($compact ? 'shadow-sm' : '')]) }}>

    <div class="pointer-events-none absolute left-2 top-2 z-30 flex flex-col gap-1">
        <span x-show="activeColor ? activeColor.has_offer : {{ $defaultHasOffer ? 'true' : 'false' }}"
              style="{{ $defaultHasOffer ? '' : 'display: none;' }}"
              class="rounded bg-brand-pink px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-white shadow-sm">
            Oferta
        </span>

        @if ($product->is_low_stock)
            <span
                class="rounded border border-red-100 bg-red-50 px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-red-600 shadow-sm">
                Últimas unidades
            </span>
        @elseif ($product->is_new)
            <span class="rounded bg-brand-gold px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-white shadow-sm">
                Nuevo
            </span>
        @endif
    </div>

    <div class="relative aspect-[4/5] w-full cursor-pointer overflow-hidden rounded-t-md bg-brand-blush/30"
        @click="window.location.href = '{{ route('product.show', $product->slug) }}'">

        <template x-for="imgUrl in [images[currentVisibleIndex]]" :key="currentVisibleIndex">
            <img :src="imgUrl"
                 :alt="`{{ $product->name }} - Vista ` + (currentVisibleIndex + 1)"
                 x-transition:enter="transition-opacity duration-300 ease-in-out"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity duration-300 ease-in-out"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 h-full w-full object-cover object-center"
                 loading="{{ $eager ? 'eager' : 'lazy' }}"
                 @if($priority) fetchpriority="high" @endif
                 decoding="async">
        </template>
    </div>

    <a href="{{ route('product.show', $product->slug) }}" class="flex flex-grow flex-col {{ $compact ? 'p-2 sm:p-3' : 'p-3 sm:p-4' }} focus:outline-none">
        @if ($uniqueColors->count() > 0)
            <div class="{{ $compact ? 'mb-2' : 'mb-3' }} flex items-center space-x-1.5" @click.stop.prevent>
                @foreach ($visibleColors as $color)
                    <div @click.stop.prevent="isTouchDevice ? null : window.location.assign('{{ route('product.show', $product->slug) }}?color={{ Str::slug($color->name) }}')"
                        @touchstart.stop.prevent="setPreview({{ collect($colorsData)->search(fn($c) => $c['id'] == $color->id) }})"
                        @touchend="clearPreviewDelay()"
                        class="relative block {{ $compact ? 'h-4 w-4' : 'h-5 w-5' }} cursor-pointer overflow-hidden rounded-full border shadow-sm transition-transform hover:scale-110 before:absolute before:-inset-2 before:content-['']"
                        :class="previewIndex === {{ collect($colorsData)->search(fn($c) => $c['id'] == $color->id) }} ?
                            'border-brand-pink ring-1 ring-brand-pink' : 'border-gray-200'"
                        title="{{ $color->name }}"
                        @mouseenter="setPreview({{ collect($colorsData)->search(fn($c) => $c['id'] == $color->id) }})"
                        @mouseleave="clearPreview()">
                        <img src="{{ $color->public_primary_image_url }}" class="h-full w-full object-cover"
                            alt="{{ $color->name }}" loading="lazy">
                    </div>
                @endforeach
                @if ($extraColorsCount > 0)
                    <span class="ml-1 cursor-default text-[10px] font-medium text-gray-400" @click.stop.prevent>
                        +{{ $extraColorsCount }}
                    </span>
                @endif
            </div>
        @else
            <div class="mb-3 h-4"></div>
        @endif

        <h3
            class="line-clamp-2 {{ $compact ? 'text-xs md:text-sm' : 'text-sm md:text-base' }} font-medium leading-relaxed text-text-dark transition-colors duration-300 group-hover:text-brand-pink">
            {{ $product->name }}
        </h3>

        <p class="mt-1 text-xs text-gray-500 hidden" x-show="activeColor && activeColor.name" x-text="activeColor ? activeColor.name : '{{ $defaultColor['name'] ?? '' }}'">
            {{ $defaultColor['name'] ?? '' }}
        </p>

        <div class="mt-auto flex flex-col items-start gap-1 pt-2">
            <div class="flex flex-wrap items-center gap-1" x-show="activeColor && activeColor.price !== null" style="{{ $defaultPrice !== null ? '' : 'display: none;' }}">
                <p class="{{ $compact ? 'text-[10px]' : 'text-xs' }} text-gray-400 line-through"
                   x-show="activeColor && activeColor.original_price !== null && activeColor.original_price > activeColor.price"
                   x-text="activeColor ? activeColor.formatted_original_price : '{{ $defaultOriginalPrice !== null ? '$' . number_format($defaultOriginalPrice, 0, ',', '.') : '' }}'"
                   style="{{ $defaultOriginalPrice !== null && $defaultOriginalPrice > $defaultPrice ? '' : 'display: none;' }}">
                    @if ($defaultOriginalPrice !== null && $defaultOriginalPrice > $defaultPrice)
                        ${{ number_format($defaultOriginalPrice, 0, ',', '.') }}
                    @endif
                </p>
                <p class="{{ $compact ? 'text-sm' : 'text-base' }} font-semibold text-brand-pink"
                   x-text="activeColor ? activeColor.formatted_price : '{{ $defaultPrice !== null ? '$' . number_format($defaultPrice, 0, ',', '.') : '' }}'">
                    @if ($defaultPrice !== null)
                        ${{ number_format($defaultPrice, 0, ',', '.') }}
                    @endif
                </p>
            </div>
            
            <p class="text-[10px] font-medium tracking-wide text-gray-500">
                {{ $product->availability_label }}
            </p>
        </div>
    </a>
</div>
