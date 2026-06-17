@php
    $primarySlide = $homeHeroSlides->first();
    $hasAdminHero = $homeHero !== null && $primarySlide !== null;
@endphp

@if ($hasAdminHero && $homeHeroMode === 'carousel')
    <div id="inicio" data-home-hero-mode="carousel"
        x-data="homeHeroCarousel({ total: {{ $homeHeroSlides->count() }}, interval: 5000 })"
        x-init="init()"
        @mouseenter="pause()"
        @mouseleave="resume()"
        class="spy-section relative w-full h-[70vh] sm:h-[85vh] min-h-[600px] overflow-hidden bg-brand-blush">

        @foreach ($homeHeroSlides as $slide)
            @php $link = $slide->resolved_link_url; @endphp
            <div x-show="active === {{ $loop->index }}"
                x-transition:enter="transition ease-out duration-700"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-500"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="absolute inset-0"
                style="{{ $loop->first ? '' : 'display: none;' }}">

                @if ($link)
                    <a href="{{ $link }}" class="absolute inset-0 block" tabindex="{{ $loop->first ? '0' : '-1' }}">
                @endif

                <picture>
                    <source
                        media="(max-width: 767px)"
                        srcset="{{ $slide->public_mobile_image_url }}">
                    <img
                        src="{{ $slide->public_desktop_image_url }}"
                        alt="{{ $slide->alt_text }}"
                        class="absolute inset-0 h-full w-full object-cover"
                        loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                        fetchpriority="{{ $loop->first ? 'high' : 'auto' }}"
                        decoding="async">
                </picture>

                @if ($link)
                    </a>
                @endif
            </div>
        @endforeach

        <button type="button" @click="prev()"
            class="absolute left-4 top-1/2 z-20 hidden -translate-y-1/2 rounded-full bg-white/85 p-3 text-gray-800 shadow-lg backdrop-blur-sm transition hover:bg-white md:flex">
            <span class="sr-only">Slide anterior</span>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                stroke="currentColor" class="h-5 w-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>
        </button>

        <button type="button" @click="next()"
            class="absolute right-4 top-1/2 z-20 hidden -translate-y-1/2 rounded-full bg-white/85 p-3 text-gray-800 shadow-lg backdrop-blur-sm transition hover:bg-white md:flex">
            <span class="sr-only">Slide siguiente</span>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                stroke="currentColor" class="h-5 w-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
        </button>

        <div class="absolute inset-x-0 bottom-6 z-20 flex justify-center gap-2">
            @foreach ($homeHeroSlides as $slide)
                <button type="button" @click="goTo({{ $loop->index }})"
                    class="h-2.5 w-2.5 rounded-full bg-white/60 transition"
                    :class="{ 'bg-white shadow-md scale-110': active === {{ $loop->index }} }">
                    <span class="sr-only">Ir al slide {{ $loop->iteration }}</span>
                </button>
            @endforeach
        </div>
    </div>

@elseif ($hasAdminHero && $homeHeroMode === 'static')
    @php $link = $primarySlide->resolved_link_url; @endphp
    <div id="inicio" data-home-hero-mode="static"
        class="spy-section relative w-full h-[70vh] sm:h-[85vh] min-h-[600px] bg-brand-blush">

        @if ($link)
            <a href="{{ $link }}" class="absolute inset-0 block">
        @endif

        <picture>
            <source
                media="(max-width: 767px)"
                srcset="{{ $primarySlide->public_mobile_image_url }}">
            <img
                src="{{ $primarySlide->public_desktop_image_url }}"
                alt="{{ $primarySlide->alt_text }}"
                class="absolute inset-0 h-full w-full object-cover"
                loading="eager"
                fetchpriority="high"
                decoding="async">
        </picture>

        @if ($link)
            </a>
        @endif
    </div>

@else
    <div id="inicio" data-home-hero-mode="fallback"
        class="spy-section relative w-full h-[70vh] sm:h-[85vh] min-h-[600px] bg-brand-blush">
        <img src="{{ asset('img/imgHero.png') }}"
             alt="Ivonne Showroom — Estilo y elegancia"
             class="absolute inset-0 h-full w-full object-cover object-right-top"
             loading="eager"
             fetchpriority="high"
             decoding="async">

        <div class="absolute inset-0 bg-black/60"></div>

        <div
            class="absolute inset-0 bg-gradient-to-r from-white/90 via-white/40 to-transparent sm:from-white/95 sm:via-white/25">
        </div>

        <div class="relative w-full h-full flex items-center max-w-[90%] mx-auto px-4 sm:px-6 lg:px-8">
            <main class="lg:w-1/2 xl:w-2/5">
                <div class="sm:text-center lg:text-left">
                    <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl reveal">
                        <span class="block xl:inline">Estilo y elegancia</span>
                        <span class="block text-brand-pink font-script xl:inline mt-2">para vos</span>
                    </h1>
                    <p class="reveal mt-4 text-base text-gray-700 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0 font-medium"
                        style="transition-delay: 100ms;">
                        Descubre nuestra coleccion exclusiva. Prendas seleccionadas con amor para resaltar tu belleza y
                        confianza en cada paso.
                    </p>
                    <div class="reveal mt-8 sm:flex sm:justify-center lg:justify-start gap-4"
                        style="transition-delay: 200ms;">
                        <div class="rounded-full shadow-lg">
                            <a href="{{ route('catalog') }}"
                                class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-full text-white bg-brand-pink hover:bg-brand-heart hover:scale-105 transform transition-all duration-300 md:py-4 md:text-lg md:px-10">
                                Ver Catalogo
                            </a>
                        </div>

                        <div class="mt-3 sm:mt-0">
                            <a href="{{ route('contact') }}"
                                class="w-full flex items-center justify-center px-8 py-3 border-2 border-brand-pink text-base font-medium rounded-full text-brand-pink bg-white/50 backdrop-blur-sm hover:bg-brand-pink hover:text-white md:py-4 md:text-lg md:px-10 transition-all duration-300">
                                Contactar
                            </a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
@endif
