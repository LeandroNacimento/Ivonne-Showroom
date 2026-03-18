{{-- Catalog Filters — shared by desktop sidebar and mobile drawer --}}
{{-- Rendered inside Livewire CatalogPage, so wire:model binds to the component state --}}

<div class="space-y-12">
    {{-- Categories --}}
    <div class="space-y-4">
        <h3 class="sidebar-section-title">Categorías</h3>
        <ul class="space-y-2">
            <li>
                <button type="button" wire:click="$set('category', null)"
                    class="block w-full text-left py-2 px-3 rounded-lg text-sm font-medium transition-all cursor-pointer {{ !$category ? 'bg-brand-pink/10 text-brand-pink' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    Todas las prendas
                </button>
            </li>
            @foreach ($categories as $cat)
                <li>
                    <button type="button" wire:click="$set('category', '{{ $cat->slug }}')"
                        class="flex items-center justify-between w-full py-2 px-3 rounded-lg text-sm transition-all group cursor-pointer {{ $category == $cat->slug ? 'bg-brand-pink/10 text-brand-pink font-bold' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                        <span>{{ $cat->name }}</span>
                        <span
                            class="text-[10px] text-gray-400 group-hover:text-brand-pink/60 transition-colors">({{ $cat->products_count }})</span>
                    </button>
                </li>
            @endforeach
        </ul>
    </div>

    {{-- Sizes --}}
    @if ($availableSizes->count() > 0)
        <div class="space-y-5 pt-4 border-t border-brand-pink/10">
            <h3 class="sidebar-section-title">Talles</h3>
            <div class="flex flex-wrap gap-2">
                @foreach ($availableSizes as $size)
                    <label class="cursor-pointer group">
                        <input type="checkbox" wire:model.live="sizes" value="{{ $size }}" class="hidden">
                        <span
                            class="inline-flex items-center justify-center min-w-[3rem] px-3 py-2 rounded-lg border text-xs font-bold transition-all
                                {{ in_array($size, $sizes)
                                    ? 'bg-gray-900 border-gray-900 text-white shadow-lg shadow-black/10'
                                    : 'bg-white border-gray-100 text-gray-600 hover:border-brand-pink/30 hover:text-gray-900' }}">
                            {{ $size }}
                        </span>
                    </label>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Colors --}}
    @if ($availableColors->count() > 0)
        <div class="space-y-5 pt-4 border-t border-brand-pink/10">
            <h3 class="sidebar-section-title">Colores</h3>
            <div class="flex flex-wrap gap-4">
                @foreach ($availableColors as $color)
                    @php
                        $isActive = in_array($color, $colors);
                        $cssColor = match (strtolower($color)) {
                            'blanco' => '#ffffff',
                            'negro' => '#000000',
                            'rojo' => '#ef4444',
                            'azul' => '#3b82f6',
                            'verde' => '#22c55e',
                            'amarillo' => '#eab308',
                            'rosa' => '#ec4899',
                            'gris' => '#9ca3af',
                            'celeste' => '#a8d1ff',
                            'beige' => '#f5f5dc',
                            'marrón', 'marron' => '#8b4513',
                            'naranja' => '#f97316',
                            'violeta', 'púrpura', 'purpura' => '#a855f7',
                            default => $color,
                        };
                    @endphp
                    <label class="cursor-pointer group relative" title="{{ $color }}">
                        <input type="checkbox" wire:model.live="colors" value="{{ $color }}" class="hidden">
                        <span
                            class="block w-7 h-7 rounded-full border border-gray-200 transition-all color-dot shadow-sm
                                {{ $isActive ? 'ring-2 ring-brand-pink ring-offset-2 scale-110 shadow-md' : 'hover:scale-110 hover:shadow-md' }}"
                            style="background-color: {{ $cssColor }};">
                        </span>
                    </label>
                @endforeach
            </div>
        </div>
    @endif
</div>
