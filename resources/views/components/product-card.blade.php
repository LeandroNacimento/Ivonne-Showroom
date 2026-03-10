@props(['product'])

@php
    $images = collect();
    $colorsData = [];

    // Optimize images and data for Alpine
    foreach ($product->colors as $index => $color) {
        $imgUrl = $color->image
            ? (str_starts_with($color->image, 'http')
                ? $color->image
                : asset('storage/' . $color->image))
            : 'https://via.placeholder.com/600x750';
        $images->push($imgUrl);
        $colorsData[] = ['id' => $color->id, 'name' => $color->name, 'image' => $imgUrl];
    }

    $images = $images->toArray();
    $uniqueColors = $product->colors->unique('name');
    $visibleColors = $uniqueColors->take(4);
    $extraColorsCount = $uniqueColors->count() - 4;

    // Smart Badges Logic
    $isNew = $product->created_at->diffInDays(now()) <= 15;
    $totalStock = collect($product->variations)->sum('stock');
    $isLowStock = $totalStock <= 3 && $totalStock > 0;

    // Quick Add Logic
    $availableVariations = $product->variations->where('stock', '>', 0)->values();
    $defaultSelectedVariation = $availableVariations->count() === 1 ? $availableVariations->first()->id : 'null';
@endphp

<div x-data="{
    images: {{ json_encode($images) }},
    currentIndex: 0,
    timer: null,
    previewIndex: null, // For Hover Preview
    showQuickAdd: false,
    selectedVariation: {{ $defaultSelectedVariation }},
    startCarousel() {
        if (this.images.length <= 1 || this.previewIndex !== null) return;
        this.timer = setInterval(() => {
            this.currentIndex = (this.currentIndex + 1) % this.images.length;
        }, 2000);
    },
    stopCarousel() {
        clearInterval(this.timer);
        this.currentIndex = 0;
    },
    setPreview(index) {
        clearInterval(this.timer);
        this.previewIndex = index;
    },
    clearPreview() {
        this.previewIndex = null;
        this.startCarousel();
    },
    addToCart(productId) {
        if (this.selectedVariation) {
            $wire.addToCart(productId, this.selectedVariation);
            this.showQuickAdd = false;
        }
    }
}" @mouseenter="startCarousel()" @mouseleave="stopCarousel(); showQuickAdd = false"
    class="group flex flex-col h-full bg-white rounded-md transition-all duration-300 hover:shadow-xl hover:scale-[1.02] relative">

    {{-- Smart Badges --}}
    <div class="absolute top-2 left-2 z-30 flex flex-col gap-1 pointer-events-none">
        @if ($isLowStock)
            <span
                class="bg-red-50 text-red-600 text-[10px] font-bold px-2 py-1 rounded shadow-sm border border-red-100 uppercase tracking-wide">
                Últimas unidades
            </span>
        @elseif ($isNew)
            <span
                class="bg-brand-gold text-white text-[10px] font-bold px-2 py-1 rounded shadow-sm uppercase tracking-wide">
                Nuevo
            </span>
        @endif
    </div>

    {{-- Grid Wrapper --}}
    <div class="relative w-full aspect-[4/5] overflow-hidden bg-stone-50 rounded-t-md cursor-pointer"
        @click="window.location.href = '{{ route('product.show', $product->slug) }}'">

        @foreach ($images as $index => $image)
            <img src="{{ $image }}" alt="{{ $product->name }} - Vista {{ $index + 1 }}" loading="lazy"
                x-show="previewIndex !== null ? previewIndex === {{ $index }} : currentIndex === {{ $index }}"
                x-transition:enter="transition opacity duration-500 ease-in-out" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition opacity duration-500 ease-in-out"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="absolute inset-0 w-full h-full object-cover object-center {{ $index === 0 ? '' : 'hidden' }}"
                :class="{ 'hidden': false }">
        @endforeach
    </div>

    {{-- Cuerpo de Información --}}
    <a href="{{ route('product.show', $product->slug) }}" class="flex flex-col flex-grow p-4 focus:outline-none">

        {{-- Swatches de Colores --}}
        @if ($uniqueColors->count() > 0)
            <div class="flex items-center space-x-1.5 mb-3" @click.stop.prevent>
                @foreach ($visibleColors as $color)
                    <div class="w-4 h-4 rounded-full border shadow-sm overflow-hidden relative cursor-pointer hover:scale-110 transition-transform"
                        :class="previewIndex === {{ collect($colorsData)->search(fn($c) => $c['id'] == $color->id) }} ?
                            'border-brand-pink ring-1 ring-brand-pink' : 'border-gray-200'"
                        title="{{ $color->name }}"
                        @mouseenter="setPreview({{ collect($colorsData)->search(fn($c) => $c['id'] == $color->id) }})"
                        @mouseleave="clearPreview()">
                        @php
                            $colorImgRaw = $color->image;
                            $colorImg = $colorImgRaw
                                ? (str_starts_with($colorImgRaw, 'http')
                                    ? $colorImgRaw
                                    : asset('storage/' . $colorImgRaw))
                                : null;
                        @endphp
                        @if ($colorImg)
                            <img src="{{ $colorImg }}" class="w-full h-full object-cover"
                                alt="{{ $color->name }}">
                        @else
                            <div class="w-full h-full bg-gray-200"></div>
                        @endif
                    </div>
                @endforeach
                @if ($extraColorsCount > 0)
                    <span class="text-[10px] font-medium text-gray-400 ml-1 cursor-default" @click.stop.prevent>
                        +{{ $extraColorsCount }}
                    </span>
                @endif
            </div>
        @else
            <div class="h-4 mb-3"></div>
        @endif

        {{-- Nombre --}}
        <h3
            class="text-sm md:text-base font-medium text-text-dark line-clamp-2 leading-relaxed group-hover:text-brand-pink transition-colors duration-300">
            {{ $product->name }}
        </h3>

        {{-- Precio y Disponibilidad --}}
        <div class="mt-auto pt-3 flex flex-col items-start gap-1">
            <p class="text-base font-semibold text-text-dark">
                ${{ number_format($product->variations_min_price, 0, ',', '.') }}
            </p>
            <p class="text-[10px] text-gray-500 uppercase tracking-wider font-medium">
                {{ $product->availability_label }}
            </p>
        </div>
    </a>
</div>
