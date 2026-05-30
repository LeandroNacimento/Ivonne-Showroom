<div>
    @if ($errors->has('variations') || $errors->has('variations.*'))
        <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <p class="font-medium">Revisa las variaciones antes de guardar.</p>
            @foreach ($errors->all() as $message)
                @if (str_starts_with($message, 'El talle') ||
                        str_contains($message, 'variaci') ||
                        str_contains($message, 'precio') ||
                        str_contains($message, 'stock') ||
                        str_contains($message, 'color'))
                    <p>{{ $message }}</p>
                @endif
            @endforeach
        </div>
    @endif



    @php $flatIndex = 0; @endphp

    @foreach ($colors as $cIdx => $color)
        <div class="mb-6 rounded-lg border border-gray-100 bg-white p-4 shadow-sm" wire:key="color-{{ $color['uuid'] }}"
            x-data="{ colorName: @js($color['name'] ?? '') }">
            <div class="mb-4">
                <label class="mb-2 flex items-center gap-2 text-sm font-medium text-gray-700">
                    <span class="text-xl">Color</span>
                </label>

                <div class="flex items-center justify-between">
                    <input type="text" wire:model.blur="colors.{{ $cIdx }}.name" x-model="colorName"
                        placeholder="Ej: Rosa, Negro, Beige" @keydown.enter.prevent
                        class="h-10 flex-1 rounded-md border-gray-300 text-sm font-medium shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50"
                        required>

                    <div class="ml-3 flex w-10 shrink-0 justify-end">
                        @if (count($colors) > 1)
                            <button type="button" wire:click="removeColor({{ $cIdx }})"
                                class="flex h-9 w-9 items-center justify-center rounded-md border border-transparent text-red-500 transition-colors hover:border-red-200 hover:bg-red-50"
                                title="Eliminar color">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            @if ($supportsSize)
                <div class="overflow-x-auto px-2">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-center text-xs font-medium uppercase tracking-wider text-gray-500">
                                <th class="px-3 pb-3">Talle</th>
                                <th class="px-3 pb-3">Precio</th>
                                <th class="px-3 pb-3">Oferta</th>
                                <th class="px-3 pb-3">Stock</th>
                                <th class="w-10 pb-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($color['variations'] as $vIdx => $variation)
                                @php $currentFlatIndex = $flatIndex; @endphp
                                <tr wire:key="var-{{ $variation['uuid'] }}">
                                    <td class="px-3 py-3">
                                        <select
                                            wire:model.blur="colors.{{ $cIdx }}.variations.{{ $vIdx }}.size"
                                            name="variations[{{ $currentFlatIndex }}][size]"
                                            class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50"
                                            required>
                                            <option value="" disabled>Seleccionar</option>
                                            @foreach ($sizeOptions as $sizeOption)
                                                <option value="{{ $sizeOption }}">{{ $sizeOption }}</option>
                                            @endforeach
                                        </select>
                                        @error("colors.{$cIdx}.variations.{$vIdx}.size")
                                            <span class="text-xs text-red-500">{{ $message }}</span>
                                        @enderror
                                    </td>

                                    <td class="px-3 py-3">
                                        <input type="number" step="0.01"
                                            wire:model.blur="colors.{{ $cIdx }}.variations.{{ $vIdx }}.price"
                                            name="variations[{{ $currentFlatIndex }}][price]" placeholder="0.00"
                                            class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50"
                                            required>
                                        @error("colors.{$cIdx}.variations.{$vIdx}.price")
                                            <span class="text-xs text-red-500">{{ $message }}</span>
                                        @enderror
                                    </td>

                                    <td class="px-3 py-3">
                                        <input type="number" step="0.01"
                                            wire:model.blur="colors.{{ $cIdx }}.variations.{{ $vIdx }}.sale_price"
                                            name="variations[{{ $currentFlatIndex }}][sale_price]"
                                            placeholder="Opcional"
                                            class="w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                                        <p class="mt-1 text-[10px] text-gray-400">Vacio si no hay oferta</p>
                                        @error("colors.{$cIdx}.variations.{$vIdx}.sale_price")
                                            <span class="text-xs text-red-500">{{ $message }}</span>
                                        @enderror
                                    </td>

                                    <td class="px-3 py-3">
                                        @php
                                            $stockVal = $variation['stock'] ?? '';
                                            $stockClass = 'border-gray-300';
                                            if ($stockVal !== '' && is_numeric($stockVal)) {
                                                if ((int) $stockVal === 0) {
                                                    $stockClass = 'border-red-400 bg-red-50';
                                                } elseif ((int) $stockVal <= 5) {
                                                    $stockClass = 'border-yellow-400 bg-yellow-50';
                                                }
                                            }
                                        @endphp
                                        <input type="number"
                                            wire:model.blur="colors.{{ $cIdx }}.variations.{{ $vIdx }}.stock"
                                            name="variations[{{ $currentFlatIndex }}][stock]" placeholder="0"
                                            class="w-full rounded-md text-sm shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50 {{ $stockClass }}"
                                            required>
                                        @error("colors.{$cIdx}.variations.{$vIdx}.stock")
                                            <span class="text-xs text-red-500">{{ $message }}</span>
                                        @enderror
                                    </td>

                                    <td class="px-3 py-3 text-center">
                                        @if (count($color['variations']) > 1)
                                            <button type="button"
                                                wire:click="removeVariation({{ $cIdx }}, {{ $vIdx }})"
                                                class="flex h-9 w-9 items-center justify-center rounded-md text-red-400 transition-colors hover:bg-red-50 hover:text-red-600"
                                                title="Eliminar variacion">
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        @endif
                                    </td>
                                </tr>

                                <tr wire:key="sku-{{ $variation['uuid'] }}">
                                    <td colspan="5" class="pb-2">
                                        <details class="text-xs">
                                            <summary
                                                class="cursor-pointer select-none text-gray-400 hover:text-gray-600">
                                                Opciones avanzadas
                                            </summary>
                                            <div class="mt-1.5">
                                                <label class="text-xs text-gray-500">Codigo interno (opcional)</label>
                                                <input type="text"
                                                    wire:model.blur="colors.{{ $cIdx }}.variations.{{ $vIdx }}.sku"
                                                    name="variations[{{ $currentFlatIndex }}][sku]"
                                                    placeholder="SKU-001"
                                                    class="mt-0.5 w-full rounded-md border-gray-300 text-xs shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                                            </div>
                                        </details>
                                    </td>
                                </tr>

                                <input type="hidden" name="variations[{{ $currentFlatIndex }}][id]"
                                    value="{{ $variation['id'] ?? '' }}">
                                <input type="hidden" name="variations[{{ $currentFlatIndex }}][color_id]"
                                    value="{{ $color['id'] ?? '' }}">
                                <input type="hidden" name="variations[{{ $currentFlatIndex }}][color]"
                                    x-bind:value="colorName">
                                <input type="hidden" name="variations[{{ $currentFlatIndex }}][uuid]"
                                    value="{{ $color['uuid'] }}">

                                @php $flatIndex++; @endphp
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="mb-3 text-xs text-gray-400">
                    Este producto usa talle unico. Se persistira como <span class="font-medium">UNICO</span>.
                </p>

                @if (count($color['variations']) > 1)
                    <p class="mb-3 text-xs text-amber-600">
                        Deja una sola variacion por color para talle unico.
                    </p>
                @endif

                <div class="space-y-1">
                    @foreach ($color['variations'] as $vIdx => $variation)
                        @php $currentFlatIndex = $flatIndex; @endphp
                        @php
                            $stockVal = $variation['stock'] ?? '';
                            $stockClass = 'border-gray-300';
                            if ($stockVal !== '' && is_numeric($stockVal)) {
                                if ((int) $stockVal === 0) {
                                    $stockClass = 'border-red-400 bg-red-50';
                                } elseif ((int) $stockVal <= 5) {
                                    $stockClass = 'border-yellow-400 bg-yellow-50';
                                }
                            }
                            $isSingle = count($color['variations']) === 1;
                        @endphp

                        <div wire:key="var-{{ $variation['uuid'] }}"
                            class="flex items-center gap-3 py-3 {{ !$loop->last ? 'border-b border-gray-200' : '' }}">
                            <div class="min-w-[5.5rem]">
                                <label class="mb-1 block text-xs text-gray-400">Talle</label>
                                <input type="text" name="variations[{{ $currentFlatIndex }}][size]"
                                    value="{{ \App\Models\Product::ONE_SIZE_VALUE }}" readonly
                                    class="h-10 w-full rounded-md border border-gray-200 bg-gray-50 px-3 text-center text-sm font-medium text-gray-600">
                            </div>

                            <div class="flex-1">
                                <label class="mb-1 block text-xs text-gray-400">Precio</label>
                                <input type="number" step="0.01"
                                    wire:model.blur="colors.{{ $cIdx }}.variations.{{ $vIdx }}.price"
                                    name="variations[{{ $currentFlatIndex }}][price]" placeholder="0.00"
                                    class="h-10 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50"
                                    required>
                                @error("colors.{$cIdx}.variations.{$vIdx}.price")
                                    <span class="text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="flex-1">
                                <label class="mb-1 block text-xs text-gray-400">Oferta</label>
                                <input type="number" step="0.01"
                                    wire:model.blur="colors.{{ $cIdx }}.variations.{{ $vIdx }}.sale_price"
                                    name="variations[{{ $currentFlatIndex }}][sale_price]" placeholder="Opcional"
                                    class="h-10 w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                                @error("colors.{$cIdx}.variations.{$vIdx}.sale_price")
                                    <span class="text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="flex-1">
                                <label class="mb-1 block text-xs text-gray-400">Stock</label>
                                <input type="number"
                                    wire:model.blur="colors.{{ $cIdx }}.variations.{{ $vIdx }}.stock"
                                    name="variations[{{ $currentFlatIndex }}][stock]" placeholder="0"
                                    class="h-10 w-full rounded-md text-sm shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50 {{ $stockClass }}"
                                    required>
                                @error("colors.{$cIdx}.variations.{$vIdx}.stock")
                                    <span class="text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="flex-1">
                                <details class="text-xs">
                                    <summary class="mb-1 cursor-pointer select-none text-gray-400 hover:text-gray-600">
                                        SKU (opc.)
                                    </summary>
                                    <input type="text"
                                        wire:model.blur="colors.{{ $cIdx }}.variations.{{ $vIdx }}.sku"
                                        name="variations[{{ $currentFlatIndex }}][sku]" placeholder="SKU-001"
                                        class="mt-0.5 w-full rounded-md border-gray-300 text-xs shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                                </details>
                            </div>

                            <div class="flex w-9 shrink-0 justify-center">
                                @if (!$isSingle)
                                    <button type="button"
                                        wire:click="removeVariation({{ $cIdx }}, {{ $vIdx }})"
                                        class="flex h-9 w-9 items-center justify-center rounded-md text-red-400 transition-colors hover:bg-red-50 hover:text-red-600"
                                        title="Eliminar variacion">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </div>

                        <input type="hidden" name="variations[{{ $currentFlatIndex }}][id]"
                            value="{{ $variation['id'] ?? '' }}">
                        <input type="hidden" name="variations[{{ $currentFlatIndex }}][color_id]"
                            value="{{ $color['id'] ?? '' }}">
                        <input type="hidden" name="variations[{{ $currentFlatIndex }}][color]"
                            x-bind:value="colorName">
                        <input type="hidden" name="variations[{{ $currentFlatIndex }}][uuid]"
                            value="{{ $color['uuid'] }}">

                        @php $flatIndex++; @endphp
                    @endforeach
                </div>
            @endif

            @if ($supportsSize)
                <button type="button" wire:click="addVariation({{ $cIdx }})"
                    class="mt-3 text-sm font-medium text-brand-pink transition-colors hover:text-brand-heart">
                    + Agregar talla
                </button>
            @endif
        </div>
    @endforeach

    <div class="flex gap-3">
        <button type="button" wire:click="addColor"
            class="flex-1 rounded-lg border-2 border-dashed border-gray-300 py-3 text-sm font-medium text-gray-500 transition-colors hover:border-brand-pink hover:text-brand-pink">
            + Agregar nuevo color
        </button>

        <button type="button" wire:click="forceSyncColors"
            class="flex-1 rounded-lg border border-gray-200 bg-gray-50 py-3 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-100 hover:text-gray-900">
            <span class="flex items-center justify-center gap-2">
                <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Sincronizar imágenes
            </span>
        </button>
    </div>
</div>
