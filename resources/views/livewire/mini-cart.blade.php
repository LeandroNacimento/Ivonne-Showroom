<div x-data="{ expanded: false }" @mouseenter="expanded = true" @mouseleave="expanded = false"
    class="fixed bottom-6 right-6 z-50 flex flex-col items-end transition-all duration-300">

    @if ($itemCount > 0)
        <!-- Detalles hover -->
        <div x-show="expanded" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-2 scale-95"
            class="bg-white rounded-lg shadow-xl border border-gray-100 p-4 mb-3 w-64 origin-bottom-right">

            <h4 class="text-sm font-semibold text-text-dark mb-2 border-b pb-2">Resumen</h4>
            <div class="flex justify-between items-center text-sm text-gray-600 mb-4">
                <span>{{ $itemCount }} {{ $itemCount === 1 ? 'prenda' : 'prendas' }}</span>
                <span class="font-bold text-text-dark">${{ number_format($total, 0, ',', '.') }}</span>
            </div>

            <a href="{{ route('cart') }}"
                class="w-full block text-center bg-brand-pink text-white font-medium py-2 rounded-md hover:bg-brand-pink/90 transition-colors text-sm shadow-sm">
                Ir a Mi Pedido
            </a>
        </div>

        <!-- Botón principal flotante -->
        <a href="{{ route('cart') }}"
            class="bg-brand-pink text-white shadow-luxury rounded-full h-14 w-14 flex items-center justify-center hover:scale-105 transition-transform duration-300 relative group">

            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
            </svg>

            <!-- Badge rotulado cantidad -->
            <span class="absolute top-0 right-0 -mt-1 -mr-1 flex h-5 w-5">
                <span
                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-brand-heart opacity-75"></span>
                <span
                    class="relative inline-flex rounded-full h-5 w-5 bg-white text-brand-pink text-[10px] items-center justify-center font-bold shadow-sm">
                    {{ $itemCount }}
                </span>
            </span>
        </a>
    @endif
</div>
