<div>
    @if (count($colors) === 0)
        <div class="text-sm text-gray-400 italic py-4 text-center">
            Agregá al menos un color en las variaciones para subir imágenes.
        </div>
    @endif

    @foreach ($colors as $colorData)
        @php
            $color = $colorData['name'];
            $uuid = $colorData['uuid'] ?? \Illuminate\Support\Str::uuid()->toString();
            $normColor = mb_strtolower(trim($color), 'UTF-8');
            $isPersisted = isset($persistedColorIds[$normColor]);
            $canUpload = is_null($productId) || $isPersisted;
        @endphp
        <div class="mb-6 bg-gray-50 rounded-lg p-4 border border-gray-100" wire:key="img-color-{{ $uuid }}">

            <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                <span class="text-lg">🖼</span>
                Imágenes para: <span class="text-brand-pink">{{ $color }}</span>
            </h3>

            {{-- Existing images (edit mode) --}}
            @if (!empty($existingImages[$color]))
                <div class="grid grid-cols-4 sm:grid-cols-5 gap-3 mb-3">
                    @foreach ($existingImages[$color] as $index => $img)
                        <div class="relative group" x-data="{ markedForDelete: false }">
                            <img src="{{ $img['url'] }}" alt=""
                                class="h-20 w-20 object-cover rounded-md border border-gray-200"
                                :class="markedForDelete && 'opacity-30'">
                            
                            <label class="absolute top-1 right-1 cursor-pointer">
                                <input type="checkbox" name="delete_images[]" value="{{ $img['id'] }}"
                                    class="sr-only" x-model="markedForDelete">
                                <span class="bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full"
                                    :class="markedForDelete ? 'bg-red-700' :
                                        'bg-red-500 md:opacity-0 md:group-hover:opacity-100'"
                                    x-text="markedForDelete ? '✓' : '✕'"></span>
                            </label>

                             <div class="absolute bottom-1 left-1 right-1 flex justify-between pointer-events-auto opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity">
                                <button type="button" wire:click="moveImage({{ $img['id'] }}, 'left')"
                                    class="bg-white/90 rounded text-gray-700 px-1 text-xs hover:bg-brand-blush hover:text-brand-pink disabled:opacity-30 disabled:cursor-not-allowed"
                                    @disabled($index === 0) title="Mover a la izquierda">
                                    &larr;
                                </button>
                                <button type="button" wire:click="moveImage({{ $img['id'] }}, 'right')"
                                    class="bg-white/90 rounded text-gray-700 px-1 text-xs hover:bg-brand-blush hover:text-brand-pink disabled:opacity-30 disabled:cursor-not-allowed"
                                    @disabled($index === count($existingImages[$color]) - 1) title="Mover a la derecha">
                                    &rarr;
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Upload new images for this color --}}
            @if ($canUpload)
                <input type="file" name="images[{{ $uuid }}][]" multiple accept="image/*"
                    class="w-full text-sm text-gray-500
                        file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0
                        file:text-sm file:font-semibold file:bg-brand-blush file:text-brand-pink
                        hover:file:bg-brand-pink hover:file:text-white transition-colors">
            @else
                <input type="file" disabled
                    class="w-full text-sm text-gray-400 opacity-60 cursor-not-allowed
                        file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0
                        file:text-sm file:font-semibold file:bg-gray-200 file:text-gray-500">
                <p class="mt-2 text-xs text-amber-600 font-medium flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Guarda los cambios del producto para habilitar la subida de imágenes para este color nuevo.
                </p>
            @endif
        </div>
    @endforeach
</div>
