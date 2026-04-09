@php
    $primarySlide = $homeHeroSlides->first();
    $hasAdminHero = $homeHero !== null && $primarySlide !== null;
    $hasPrimaryCta = $hasAdminHero && filled($homeHero->cta_label) && filled($homeHero->cta_url);
@endphp

@if ($hasAdminHero && $homeHeroMode === 'carousel')
    <div id="inicio" data-home-hero-mode="carousel"
        x-data="homeHeroCarousel({ total: {{ $homeHeroSlides->count() }}, interval: 5000 })"
        x-init="init()"
        @mouseenter="pause()"
        @mouseleave="resume()"
        class="spy-section relative w-full h-[85vh] min-h-[600px] overflow-hidden">
        @foreach ($homeHeroSlides as $slide)
            <div x-show="active === {{ $loop->index }}"
                x-transition:enter="transition ease-out duration-700"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-500"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="{{ $loop->first ? '' : 'hidden' }} absolute inset-0 bg-cover bg-center bg-no-repeat lg:bg-right-top"
                style="background-image: url('{{ $slide->public_image_url }}');">
                <div class="absolute inset-0 bg-black/60"></div>
            </div>
        @endforeach

        <div
            class="absolute inset-0 bg-gradient-to-r from-white/90 via-white/40 to-transparent sm:from-white/95 sm:via-white/25">
        </div>

        <div class="relative w-full h-full flex items-center max-w-[90%] mx-auto px-4 sm:px-6 lg:px-8">
            <main class="lg:w-1/2 xl:w-2/5">
                <div class="sm:text-center lg:text-left">
                    @if (filled($homeHero->eyebrow))
                        <p class="reveal text-sm font-semibold uppercase tracking-[0.24em] text-brand-pink">
                            {{ $homeHero->eyebrow }}
                        </p>
                    @endif

                    <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl reveal">
                        {{ $homeHero->title }}
                    </h1>
                    <p class="reveal mt-4 text-base text-gray-700 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0 font-medium"
                        style="transition-delay: 100ms;">
                        {{ $homeHero->description }}
                    </p>
                    <div class="reveal mt-8 sm:flex sm:justify-center lg:justify-start gap-4"
                        style="transition-delay: 200ms;">
                        @if ($hasPrimaryCta)
                            <div class="rounded-full shadow-lg">
                                <a href="{{ $homeHero->cta_url }}"
                                    class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-full text-white bg-brand-pink hover:bg-brand-heart hover:scale-105 transform transition-all duration-300 md:py-4 md:text-lg md:px-10">
                                    {{ $homeHero->cta_label }}
                                </a>
                            </div>
                        @endif

                        <div class="{{ $hasPrimaryCta ? 'mt-3 sm:mt-0' : '' }}">
                            <a href="{{ route('contact') }}"
                                class="w-full flex items-center justify-center px-8 py-3 border-2 border-brand-pink text-base font-medium rounded-full text-brand-pink bg-white/50 backdrop-blur-sm hover:bg-brand-pink hover:text-white md:py-4 md:text-lg md:px-10 transition-all duration-300">
                                Contactar
                            </a>
                        </div>
                    </div>
                </div>
            </main>
        </div>

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
    <div id="inicio" data-home-hero-mode="static"
        class="spy-section relative w-full h-[85vh] min-h-[600px] bg-cover bg-center bg-no-repeat lg:bg-right-top"
        style="background-image: url('{{ $primarySlide->public_image_url }}');">
        <div class="absolute inset-0 bg-black/60"></div>

        <div
            class="absolute inset-0 bg-gradient-to-r from-white/90 via-white/40 to-transparent sm:from-white/95 sm:via-white/25">
        </div>

        <div class="relative w-full h-full flex items-center max-w-[90%] mx-auto px-4 sm:px-6 lg:px-8">
            <main class="lg:w-1/2 xl:w-2/5">
                <div class="sm:text-center lg:text-left">
                    @if (filled($homeHero->eyebrow))
                        <p class="reveal text-sm font-semibold uppercase tracking-[0.24em] text-brand-pink">
                            {{ $homeHero->eyebrow }}
                        </p>
                    @endif

                    <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl reveal">
                        {{ $homeHero->title }}
                    </h1>
                    <p class="reveal mt-4 text-base text-gray-700 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0 font-medium"
                        style="transition-delay: 100ms;">
                        {{ $homeHero->description }}
                    </p>
                    <div class="reveal mt-8 sm:flex sm:justify-center lg:justify-start gap-4"
                        style="transition-delay: 200ms;">
                        @if ($hasPrimaryCta)
                            <div class="rounded-full shadow-lg">
                                <a href="{{ $homeHero->cta_url }}"
                                    class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-full text-white bg-brand-pink hover:bg-brand-heart hover:scale-105 transform transition-all duration-300 md:py-4 md:text-lg md:px-10">
                                    {{ $homeHero->cta_label }}
                                </a>
                            </div>
                        @endif

                        <div class="{{ $hasPrimaryCta ? 'mt-3 sm:mt-0' : '' }}">
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
@else
    <div id="inicio" data-home-hero-mode="fallback"
        class="spy-section relative w-full h-[85vh] min-h-[600px] bg-cover bg-center bg-no-repeat lg:bg-right-top"
        style="background-image: url('{{ asset('img/imgHero.png') }}');">
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
                        Descubre nuestra colección exclusiva. Prendas seleccionadas con amor para resaltar tu belleza y
                        confianza en cada paso.
                    </p>
                    <div class="reveal mt-8 sm:flex sm:justify-center lg:justify-start gap-4"
                        style="transition-delay: 200ms;">
                        <div class="rounded-full shadow-lg">
                            <a href="{{ route('catalog') }}"
                                class="w-full flex items-center justify-center px-8 py-3 border border-transparent text-base font-medium rounded-full text-white bg-brand-pink hover:bg-brand-heart hover:scale-105 transform transition-all duration-300 md:py-4 md:text-lg md:px-10">
                                Ver Catálogo
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
