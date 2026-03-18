<div>
    {{-- ─── Precio Base Helper ─── --}}
    <div class="bg-gray-50 rounded-lg p-4 mb-6 flex flex-col sm:flex-row items-start sm:items-end gap-3">
        <div class="flex-1">
            <label class="block text-sm font-medium text-gray-600 mb-1">Precio base sugerido</label>
            <input type="number" wire:model="basePrice" step="0.01" placeholder="Ej: 27850"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50 text-sm">
        </div>
        <button type="button" wire:click="applyBasePrice"
            class="px-4 py-2 text-sm font-medium text-white bg-brand-pink rounded-md hover:bg-brand-heart transition-colors whitespace-nowrap">
            Aplicar a todas las tallas
        </button>
    </div>

    {{-- ─── Color Groups ─── --}}
    @foreach ($colors as $cIdx => $color)
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-4 mb-6"
            wire:key="color-{{ $color['uuid'] }}">
            {{-- Color Header --}}
            <div class="mb-4">
                {{-- Row 1: Label --}}
                <label class="flex items-center gap-2 mb-2 text-sm font-medium text-gray-700">
                    <span class="text-xl">🎨</span>
                    <span>Color</span>
                </label>

                {{-- Row 2: Input + Delete --}}
                <div class="flex items-center justify-between">
                    <input type="text" wire:model.blur="colors.{{ $cIdx }}.name"
                        placeholder="Ej: Rosa, Negro, Beige…" @keydown.enter.prevent
                        class="flex-1 h-10 rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50 text-sm font-medium"
                        required>

                    <div class="w-10 flex justify-end shrink-0 ml-3">
                        @if (count($colors) > 1)
                            <button type="button" wire:click="removeColor({{ $cIdx }})"
                                class="h-9 w-9 flex items-center justify-center rounded-md text-red-500 hover:bg-red-50 transition-colors border border-transparent hover:border-red-200"
                                title="Eliminar color">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            {{-- ─── Variations: dual-mode layout ─── --}}
            @if ($supportsSize)
                {{-- ── TABLE LAYOUT (with size) ── --}}
                <div class="overflow-x-auto px-2">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <th class="pb-3 px-3">Talla</th>
                                <th class="pb-3 px-3">Precio</th>
                                <th class="pb-3 px-3">Stock</th>
                                <th class="pb-3 w-10"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($color['variations'] as $vIdx => $variation)
                                <tr wire:key="var-{{ $variation['uuid'] }}">
                                    {{-- Size --}}
                                    <td class="py-3 px-3">
                                        <select
                                            wire:model.blur="colors.{{ $cIdx }}.variations.{{ $vIdx }}.size"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50 text-sm"
                                            required>
                                            <option value="" disabled>Seleccionar</option>
                                            <option value="XS">XS</option>
                                            <option value="S">S</option>
                                            <option value="M">M</option>
                                            <option value="L">L</option>
                                            <option value="XL">XL</option>
                                            <option value="XXL">XXL</option>
                                        </select>
                                        @error("colors.{$cIdx}.variations.{$vIdx}.size")
                                            <span class="text-xs text-red-500">{{ $message }}</span>
                                        @enderror
                                    </td>

                                    {{-- Price --}}
                                    <td class="py-3 px-3">
                                        <input type="number" step="0.01"
                                            wire:model.blur="colors.{{ $cIdx }}.variations.{{ $vIdx }}.price"
                                            placeholder="0.00"
                                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50 text-sm"
                                            required>
                                        @error("colors.{$cIdx}.variations.{$vIdx}.price")
                                            <span class="text-xs text-red-500">{{ $message }}</span>
                                        @enderror
                                    </td>

                                    {{-- Stock --}}
                                    <td class="py-3 px-3">
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
                                            placeholder="0"
                                            class="w-full rounded-md shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50 text-sm {{ $stockClass }}"
                                            required>
                                        @error("colors.{$cIdx}.variations.{$vIdx}.stock")
                                            <span class="text-xs text-red-500">{{ $message }}</span>
                                        @enderror
                                    </td>

                                    {{-- Delete --}}
                                    <td class="py-3 px-3 text-center">
                                        @if (count($color['variations']) > 1)
                                            <button type="button"
                                                wire:click="removeVariation({{ $cIdx }}, {{ $vIdx }})"
                                                class="h-9 w-9 flex items-center justify-center rounded-md text-red-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                                title="Eliminar variación">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        @endif
                                    </td>
                                </tr>

                                {{-- SKU --}}
                                <tr wire:key="sku-{{ $variation['uuid'] }}">
                                    <td colspan="4" class="pb-2">
                                        <details class="text-xs">
                                            <summary
                                                class="cursor-pointer text-gray-400 hover:text-gray-600 select-none">
                                                Opciones avanzadas</summary>
                                            <div class="mt-1.5">
                                                <label class="text-xs text-gray-500">Código interno (opcional)</label>
                                                <input type="text"
                                                    wire:model.blur="colors.{{ $cIdx }}.variations.{{ $vIdx }}.sku"
                                                    placeholder="SKU-001"
                                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50 text-xs mt-0.5">
                                            </div>
                                        </details>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                {{-- ── COMPACT CARD LAYOUT (no size) ── --}}
                <p class="text-xs text-gray-400 mb-3">Este producto no utiliza talles.</p>

                <div class="space-y-1">
                    @foreach ($color['variations'] as $vIdx => $variation)
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

                            {{-- Precio --}}
                            <div class="flex-1">
                                <label class="block text-xs text-gray-400 mb-1">Precio</label>
                                <input type="number" step="0.01"
                                    wire:model.blur="colors.{{ $cIdx }}.variations.{{ $vIdx }}.price"
                                    placeholder="0.00"
                                    class="w-full h-10 rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50 text-sm"
                                    required>
                                @error("colors.{$cIdx}.variations.{$vIdx}.price")
                                    <span class="text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Stock --}}
                            <div class="flex-1">
                                <label class="block text-xs text-gray-400 mb-1">Stock</label>
                                <input type="number"
                                    wire:model.blur="colors.{{ $cIdx }}.variations.{{ $vIdx }}.stock"
                                    placeholder="0"
                                    class="w-full h-10 rounded-md shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50 text-sm {{ $stockClass }}"
                                    required>
                                @error("colors.{$cIdx}.variations.{$vIdx}.stock")
                                    <span class="text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- SKU (inline collapsed) --}}
                            <div class="flex-1">
                                <details class="text-xs">
                                    <summary class="cursor-pointer text-gray-400 hover:text-gray-600 select-none mb-1">
                                        SKU (opc.)</summary>
                                    <input type="text"
                                        wire:model.blur="colors.{{ $cIdx }}.variations.{{ $vIdx }}.sku"
                                        placeholder="SKU-001"
                                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50 text-xs mt-0.5">
                                </details>
                            </div>

                            {{-- Delete --}}
                            <div class="shrink-0 w-9 flex justify-center">
                                @if (!$isSingle)
                                    <button type="button"
                                        wire:click="removeVariation({{ $cIdx }}, {{ $vIdx }})"
                                        class="h-9 w-9 flex items-center justify-center rounded-md text-red-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                        title="Eliminar variación">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Add Variation button: solo si la categoría soporta talle --}}
            @if ($supportsSize)
                <button type="button" wire:click="addVariation({{ $cIdx }})"
                    class="mt-3 text-sm text-brand-pink hover:text-brand-heart font-medium transition-colors">
                    + Agregar talla
                </button>
            @endif
        </div>
    @endforeach

    {{-- Add Color Button --}}
    <button type="button" wire:click="addColor"
        class="w-full py-3 border-2 border-dashed border-gray-300 rounded-lg text-sm font-medium text-gray-500 hover:border-brand-pink hover:text-brand-pink transition-colors">
        + Agregar nuevo color
    </button>

    {{-- ─── Hidden Inputs for Form Submission ─── --}}
    @foreach ($this->flatVariations as $idx => $flat)
        <input type="hidden" name="variations[{{ $idx }}][id]" value="{{ $flat['id'] }}">
        <input type="hidden" name="variations[{{ $idx }}][color_id]" value="{{ $flat['color_id'] }}">
        <input type="hidden" name="variations[{{ $idx }}][color]" value="{{ $flat['color'] }}">
        <input type="hidden" name="variations[{{ $idx }}][size]" value="{{ $flat['size'] }}">
        <input type="hidden" name="variations[{{ $idx }}][price]" value="{{ $flat['price'] }}">
        <input type="hidden" name="variations[{{ $idx }}][stock]" value="{{ $flat['stock'] }}">
        <input type="hidden" name="variations[{{ $idx }}][sku]" value="{{ $flat['sku'] }}">
    @endforeach
</div>
