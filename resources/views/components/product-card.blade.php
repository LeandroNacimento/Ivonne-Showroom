@props(['product'])

@php
    $images = $product->colors
        ->map(function ($color) {
            return $color->image
                ? (str_starts_with($color->image, 'http')
                    ? $color->image
                    : asset('storage/' . $color->image))
                : 'https://via.placeholder.com/600x750';
        })
        ->values()
        ->toArray();

    $uniqueColors = $product->colors->unique('name');
    $visibleColors = $uniqueColors->take(4);
    $extraColorsCount = $uniqueColors->count() - 4;
@endphp

<a href="{{ route('product.show', $product->slug) }}" x-data="{
    images: {{ json_encode($images) }},
    currentIndex: 0,
    timer: null,
    startCarousel() {
        if (this.images.length <= 1) return;
        this.timer = setInterval(() => {
            this.currentIndex = (this.currentIndex + 1) % this.images.length;
        }, 2000);
    },
    stopCarousel() {
        clearInterval(this.timer);
        this.currentIndex = 0;
    }
}" @mouseenter="startCarousel()"
    @mouseleave="stopCarousel()"
    class="group flex flex-col h-full bg-white rounded-md transition-all duration-300 hover:shadow-xl hover:scale-[1.02] focus:outline-none">

    {{-- Contenedor de Imagen con Carousel --}}
    <div class="relative w-full aspect-[4/5] overflow-hidden bg-stone-50 rounded-t-md">

        @foreach ($images as $index => $image)
            <img src="{{ $image }}" alt="{{ $product->name }} - Vista {{ $index + 1 }}" loading="lazy"
                x-show="currentIndex === {{ $index }}"
                x-transition:enter="transition opacity duration-500 ease-in-out" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition opacity duration-500 ease-in-out"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="absolute inset-0 w-full h-full object-cover object-center {{ $index === 0 ? '' : 'hidden' }}"
                :class="{ 'hidden': false }">
        @endforeach

        {{-- Hover overlay para el botón --}}
        <div
            class="absolute inset-0 z-20 bg-black/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-end justify-center pb-6">
            <span
                class="bg-white/95 backdrop-blur-sm text-neutral-900 text-sm font-medium px-6 py-2 rounded shadow-sm transform translate-y-4 opacity-0 group-hover:translate-y-0 group-hover:opacity-100 transition-all duration-300">
                Ver producto
            </span>
        </div>
    </div>

    {{-- Cuerpo de Información --}}
    <div class="flex flex-col flex-grow p-4">

        {{-- Swatches de Colores --}}
        @if ($uniqueColors->count() > 0)
            <div class="flex items-center space-x-1.5 mb-3">
                @foreach ($visibleColors as $color)
                    <div class="w-4 h-4 rounded-full border border-gray-200 shadow-sm overflow-hidden"
                        title="{{ $color->name }}">
                        @php
                            $colorImgRaw = $color->image;
                            $colorImg = $colorImgRaw
                                ? (str_starts_with($colorImgRaw, 'http')
                                    ? $colorImgRaw
                                    : asset('storage/' . $colorImgRaw))
                                : null;
                        @endphp
                        @if ($colorImg)
                            <img src="{{ $colorImg }}" class="w-full h-full object-cover" alt="{{ $color->name }}">
                        @else
                            <div class="w-full h-full bg-gray-200"></div>
                        @endif
                    </div>
                @endforeach
                @if ($extraColorsCount > 0)
                    <span class="text-[10px] font-medium text-gray-400 ml-1">
                        +{{ $extraColorsCount }}
                    </span>
                @endif
            </div>
        @else
            <div class="h-4 mb-3"></div>
        @endif

        {{-- Nombre --}}
        <h3
            class="text-sm md:text-base font-medium text-neutral-900 line-clamp-2 leading-relaxed group-hover:text-gray-600 transition-colors duration-300">
            {{ $product->name }}
        </h3>

        {{-- Precio y Disponibilidad --}}
        <div class="mt-auto pt-3 flex flex-col items-start gap-1">
            <p class="text-base font-semibold text-neutral-900">
                ${{ number_format($product->variations_min_price, 0, ',', '.') }}
            </p>
            <p class="text-[10px] text-gray-500 uppercase tracking-wider font-medium">
                {{ $product->availability_label }}
            </p>
        </div>
    </div>
</a>
