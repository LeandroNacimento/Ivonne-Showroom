<div>
    @if (count($colors) === 0)
        <div class="text-sm text-gray-400 italic py-4 text-center">
            Agregá al menos un color en las variaciones para subir imágenes.
        </div>
    @endif

    @foreach ($colors as $color)
        <div class="mb-6 bg-gray-50 rounded-lg p-4 border border-gray-100" wire:key="img-color-{{ $loop->index }}">
            <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                <span class="text-lg">🖼</span>
                Imágenes para: <span class="text-brand-pink">{{ $color }}</span>
            </h3>

            {{-- Existing images (edit mode) --}}
            @if (!empty($existingImages[$color]))
                <div class="grid grid-cols-4 sm:grid-cols-5 gap-3 mb-3">
                    @foreach ($existingImages[$color] as $img)
                        <div class="relative group" x-data="{ markedForDelete: false }">
                            <img src="{{ $img['url'] }}" alt=""
                                class="h-20 w-20 object-cover rounded-md border border-gray-200"
                                :class="markedForDelete && 'opacity-30'">
                            <label class="absolute top-1 right-1 cursor-pointer">
                                <input type="checkbox" name="delete_images[]" value="{{ $img['id'] }}"
                                    class="sr-only" x-model="markedForDelete">
                                <span class="bg-red-500 text-white text-xs px-1.5 py-0.5 rounded-full"
                                    :class="markedForDelete ? 'bg-red-700' :
                                        'bg-red-500 opacity-0 group-hover:opacity-100'"
                                    x-text="markedForDelete ? '✓' : '✕'"></span>
                            </label>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Upload new images for this color --}}
            <input type="file" name="images[{{ $color }}][]" multiple accept="image/*"
                class="w-full text-sm text-gray-500
                    file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0
                    file:text-sm file:font-semibold file:bg-brand-blush file:text-brand-pink
                    hover:file:bg-brand-pink hover:file:text-white transition-colors">
        </div>
    @endforeach
</div>
