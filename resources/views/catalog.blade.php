<x-layouts.app>
    {{-- Editorial Header Section --}}
    <div class="bg-white border-b border-gray-100">
        <div class="max-w-[90%] mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-20">
            <div class="text-center reveal">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-script text-brand-pink/90 leading-tight">
                    Catálogo
                </h1>
                <p class="text-gray-400 mt-3 font-medium tracking-[0.3em] uppercase text-xs md:text-sm">
                    Colección Exclusiva
                </p>
            </div>
        </div>
    </div>

    {{-- Livewire Catalog --}}
    <livewire:public.catalog-page />
</x-layouts.app>
