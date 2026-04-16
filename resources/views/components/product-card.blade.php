@props(['product'])

@php
    $images = $product->colors->map(fn ($color) => $color->public_primary_image_url)->values()->toArray();

    if ($images === []) {
        $images = [asset('img/placeholder-product.jpg')];
    }

    $colorsData = $product->colors
        ->map(
            fn ($color) => [
                'id' => $color->id,
                'name' => $color->name,
                'image' => $color->public_primary_image_url,
            ],
        )
        ->toArray();

    $uniqueColors = $product->colors->unique('name');
    $visibleColors = $uniqueColors->take(4);
    $extraColorsCount = $uniqueColors->count() - 4;
@endphp

<div x-data="{
    images: {{ json_encode($images) }},
    currentIndex: 0,
    timer: null,
    previewIndex: null,
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
    }
}" @mouseenter="startCarousel()" @mouseleave="stopCarousel()"
    class="group relative flex h-full flex-col rounded-md bg-white transition-all duration-300 hover:scale-[1.02] hover:shadow-xl">

    <div class="pointer-events-none absolute left-2 top-2 z-30 flex flex-col gap-1">
        @if ($product->display_has_active_offer)
            <span
                class="rounded bg-brand-pink px-2 py-1 text-[10px] font-bold uppercase tracking-wide text-white shadow-sm">
                Oferta
            </span>
        @endif

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

    <div class="relative aspect-[4/5] w-full cursor-pointer overflow-hidden rounded-t-md bg-stone-50"
        @click="window.location.href = '{{ route('product.show', $product->slug) }}'">

        @foreach ($images as $index => $image)
            <img src="{{ $image }}" alt="{{ $product->name }} - Vista {{ $index + 1 }}" loading="lazy"
                x-show="previewIndex !== null ? previewIndex === {{ $index }} : currentIndex === {{ $index }}"
                x-transition:enter="transition opacity duration-500 ease-in-out" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="transition opacity duration-500 ease-in-out"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                class="absolute inset-0 h-full w-full object-cover object-center {{ $index === 0 ? '' : 'hidden' }}"
                :class="{ 'hidden': false }">
        @endforeach
    </div>

    <a href="{{ route('product.show', $product->slug) }}" class="flex flex-grow flex-col p-4 focus:outline-none">
        @if ($uniqueColors->count() > 0)
            <div class="mb-3 flex items-center space-x-1.5" @click.stop.prevent>
                @foreach ($visibleColors as $color)
                    <div @click.stop.prevent="window.location.assign('{{ route('product.show', $product->slug) }}?color={{ Str::slug($color->name) }}')"
                        class="relative block h-4 w-4 cursor-pointer overflow-hidden rounded-full border shadow-sm transition-transform hover:scale-110"
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
            class="line-clamp-2 text-sm font-medium leading-relaxed text-text-dark transition-colors duration-300 group-hover:text-brand-pink md:text-base">
            {{ $product->name }}
        </h3>

        <div class="mt-auto flex flex-col items-start gap-1 pt-3">
            @if ($product->display_price !== null)
                <div class="flex flex-wrap items-center gap-2">
                    @if ($product->display_original_price !== null && $product->display_original_price > $product->display_price)
                        <p class="text-sm text-gray-400 line-through">
                            ${{ number_format($product->display_original_price, 0, ',', '.') }}
                        </p>
                    @endif
                    <p class="text-base font-semibold text-text-dark">
                        ${{ number_format($product->display_price, 0, ',', '.') }}
                    </p>
                </div>
            @endif
            <p class="text-[10px] font-medium uppercase tracking-wider text-gray-500">
                {{ $product->availability_label }}
            </p>
        </div>
    </a>
</div>
