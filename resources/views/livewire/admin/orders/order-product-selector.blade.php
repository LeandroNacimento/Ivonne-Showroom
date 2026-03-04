<div>
    <div class="relative w-full" x-data="{ open: true }" @click.away="open = false">
        <!-- Input Selector -->
        <input type="text" wire:model.live.debounce.300ms="search" @focus="open = true" @input="open = true"
            placeholder="Buscar producto por nombre..."
            class="block w-full rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-pink sm:text-sm sm:leading-6">

        <!-- Loading Indicator -->
        <div wire:loading.class.remove="hidden" wire:target="search"
            class="hidden absolute inset-y-0 right-0 flex items-center pr-3">
            <svg class="animate-spin h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                </circle>
                <path class="opacity-75" fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                </path>
            </svg>
        </div>

        <!-- Dropdown Results -->
        @if (strlen($search) >= 2)
            <div x-show="open"
                class="absolute z-10 mt-1 w-full rounded-md bg-white shadow-lg overflow-hidden border border-gray-200">
                <ul class="max-h-60 overflow-y-auto" role="listbox">
                    @forelse($products as $product)
                        <li wire:click="selectProduct({{ $product->id }})" @click="open = false"
                            class="group cursor-pointer select-none py-2 pl-3 pr-9 text-gray-900 hover:bg-brand-pink hover:text-white transition-colors border-b border-gray-100 last:border-0"
                            role="option">
                            <div class="flex items-center justify-between">
                                <div class="flex flex-col">
                                    <span class="font-semibold text-sm">{{ $product->name }}</span>
                                    <span class="text-xs text-gray-500 group-hover:text-brand-blush">
                                        {{ $product->variations->count() }}
                                        {{ $product->variations->count() == 1 ? 'talle disponible' : 'talles disponibles' }}
                                    </span>
                                </div>
                                <span
                                    class="bg-gray-100 text-gray-600 group-hover:bg-white group-hover:text-brand-pink text-xs px-2 py-1 rounded-full font-bold">
                                    Stock total: {{ $product->variations->sum('stock') }}
                                </span>
                            </div>
                        </li>
                    @empty
                        <li class="select-none py-3 px-3 text-sm text-gray-500 text-center">
                            No se encontraron productos disponibles con stock para: "<span
                                class="font-semibold">{{ $search }}</span>"
                        </li>
                    @endforelse
                </ul>
            </div>
        @endif
    </div>
</div>
