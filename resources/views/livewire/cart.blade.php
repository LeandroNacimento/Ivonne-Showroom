<div class="bg-white">
    <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <h1 class="mb-8 font-script text-3xl font-bold text-brand-pink text-gray-900">Tu Pedido</h1>

        @if (session('success'))
            <div class="relative mb-4 rounded border border-green-400 bg-green-100 px-4 py-3 text-green-700"
                role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if (empty($cart))
            <div class="rounded-lg bg-brand-blush p-8 text-center">
                <x-icon name="bag" class="mx-auto mb-4 h-16 w-16 text-brand-pink" />
                <p class="mb-4 text-lg text-gray-700">
                    Tu pedido está vacío.
                </p>
                <a href="{{ route('catalog') }}"
                    class="inline-flex items-center rounded-md border border-transparent bg-brand-pink px-6 py-3 text-base font-medium text-white transition-colors hover:bg-brand-heart">
                    Ver Catálogo
                </a>
            </div>
        @else
            <div class="overflow-hidden border border-gray-200 bg-white shadow sm:rounded-lg">
                <ul class="divide-y divide-gray-200">
                    @foreach ($cart as $key => $item)
                        <li class="flex items-center p-4 transition-opacity duration-200 sm:p-6"
                            wire:key="item-{{ $key }}" wire:loading.class="opacity-50 pointer-events-none"
                            wire:target="increment('{{ $key }}'), decrement('{{ $key }}'), removeFromCart('{{ $key }}')">
                            <div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-md bg-gray-100">
                                @if ($item['image'])
                                    @php
                                        $imgSrc = str_starts_with($item['image'], 'http')
                                            ? $item['image']
                                            : \Illuminate\Support\Facades\Storage::url($item['image']);
                                    @endphp
                                    <img src="{{ $imgSrc }}" alt="{{ $item['name'] }}"
                                        class="h-full w-full object-cover" loading="lazy">
                                @else
                                    <span class="flex h-full w-full items-center justify-center text-xs text-gray-400">
                                        Sin imagen
                                    </span>
                                @endif
                            </div>
                            <div class="ml-4 flex flex-1 flex-col sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h3 class="text-lg font-medium text-gray-900">
                                        <a
                                            href="{{ route('product.show', \Illuminate\Support\Str::slug($item['name'])) }}">{{ $item['name'] }}</a>
                                    </h3>
                                    <p class="text-sm text-gray-500">{{ $item['color'] }} · {{ $item['size'] }}</p>
                                    <div class="mt-1 flex flex-wrap items-center gap-2">
                                        @if (($item['has_active_offer'] ?? false) && ($item['original_price'] ?? 0) > ($item['unit_price'] ?? 0))
                                            <p class="text-sm text-gray-400 line-through">
                                                ${{ number_format($item['original_price'], 0, ',', '.') }}
                                            </p>
                                        @endif
                                        <p class="text-sm font-medium text-gray-900">
                                            ${{ number_format($item['unit_price'], 0, ',', '.') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-4 flex items-center gap-4 sm:mt-0">
                                    <div class="flex items-center rounded-md border border-gray-300">
                                        <button type="button" wire:click="decrement('{{ $key }}')"
                                            wire:loading.attr="disabled"
                                            class="border-r border-gray-300 px-4 py-2.5 text-gray-600 transition-colors hover:bg-gray-100 focus:outline-none
                                                {{ $item['quantity'] <= 1 ? 'opacity-30 cursor-not-allowed' : '' }}">
                                            -
                                        </button>
                                        <input type="number" value="{{ $item['quantity'] }}" readonly
                                            class="h-8 w-12 border-0 bg-transparent p-0 text-center font-medium text-gray-900 focus:ring-0">
                                        <button type="button" wire:click="increment('{{ $key }}')"
                                            wire:loading.attr="disabled"
                                            class="border-l border-gray-300 px-4 py-2.5 text-gray-600 transition-colors hover:bg-gray-100 focus:outline-none
                                                {{ $item['quantity'] >= ($item['stock'] ?? 99) ? 'opacity-30 cursor-not-allowed' : '' }}">
                                            +
                                        </button>
                                    </div>
                                    @if ($item['quantity'] >= ($item['stock'] ?? 99))
                                        <span class="ml-2 text-xs text-amber-600">Máx.</span>
                                    @endif
                                    <button type="button" wire:click="removeFromCart('{{ $key }}')"
                                        wire:loading.attr="disabled"
                                        class="ml-2 text-sm font-medium text-red-500 hover:text-red-700">
                                        <span wire:loading.remove
                                            wire:target="removeFromCart('{{ $key }}')">Eliminar</span>
                                        <span wire:loading wire:target="removeFromCart('{{ $key }}')">...</span>
                                    </button>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <div class="flex items-center justify-between bg-gray-50 px-4 py-6 sm:px-6">
                    <div class="text-base font-medium text-gray-900">Total</div>
                    <div class="text-xl font-bold text-brand-pink">${{ number_format($total, 0, ',', '.') }}</div>
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <a href="https://wa.me/5493704550445?text={{ $whatsappMessage }}" target="_blank"
                    class="inline-flex transform items-center rounded-md border border-transparent bg-green-500 px-8 py-4 text-lg font-medium text-white shadow-md transition-colors duration-200 hover:scale-105 hover:bg-green-600">
                    <svg class="mr-2 h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.463 1.065 2.876 1.213 3.074.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z" />
                    </svg>
                    Enviar Pedido por WhatsApp
                </a>
            </div>
            <p class="mt-2 text-right text-xs text-gray-400">Confirmá disponibilidad y coordiná el pago por WhatsApp.</p>
        @endif
    </div>
</div>
