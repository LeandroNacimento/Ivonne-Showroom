@props(['slide', 'position', 'total'])

@php
    $displayName = $slide->name ?? 'Sin nombre';
    $linkLabel = match($slide->link_type) {
        'external' => $slide->link_url ? '→ ' . parse_url($slide->link_url, PHP_URL_HOST) : '→ URL externa',
        default    => null,
    };
@endphp

<div
    data-slide-id="{{ $slide->id }}"
    class="group relative flex items-stretch gap-0 rounded-xl border border-gray-200 bg-white shadow-sm transition hover:shadow-md {{ ! $slide->is_active ? 'opacity-70' : '' }}"
    x-data="{ active: {{ $slide->is_active ? 'true' : 'false' }} }">

    {{-- Handle drag --}}
    <div class="drag-handle flex w-10 shrink-0 cursor-grab items-center justify-center rounded-l-xl border-r border-gray-100 bg-gray-50 text-gray-300 transition hover:bg-gray-100 hover:text-gray-400 active:cursor-grabbing">
        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20" class="h-5 w-5">
            <path d="M7 2a1 1 0 1 0 0 2 1 1 0 0 0 0-2Zm0 7a1 1 0 1 0 0 2 1 1 0 0 0 0-2Zm0 7a1 1 0 1 0 0 2 1 1 0 0 0 0-2Zm6-14a1 1 0 1 0 0 2 1 1 0 0 0 0-2Zm0 7a1 1 0 1 0 0 2 1 1 0 0 0 0-2Zm0 7a1 1 0 1 0 0 2 1 1 0 0 0 0-2Z"/>
        </svg>
    </div>

    {{-- Contenido principal --}}
    <div class="min-w-0 flex-1 p-4">

        {{-- Previews de imágenes --}}
        <div class="flex gap-3">

            {{-- Desktop --}}
            <div class="relative min-w-0 flex-[3]">
                <div class="overflow-hidden rounded-lg bg-gray-100">
                    <img src="{{ $slide->public_desktop_image_url }}"
                         alt="{{ $slide->alt_text }}"
                         class="aspect-video w-full object-cover {{ ! $slide->is_active ? 'grayscale-[30%]' : '' }}">
                </div>
                <span class="absolute left-2 top-2 rounded bg-black/50 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-white">
                    Desktop
                </span>
            </div>

            {{-- Mobile --}}
            <div class="relative w-[28%] shrink-0">
                <div class="overflow-hidden rounded-lg bg-gray-100">
                    <img src="{{ $slide->public_mobile_image_url }}"
                         alt="{{ $slide->alt_text }}"
                         class="aspect-[4/5] w-full object-cover {{ ! $slide->is_active ? 'grayscale-[30%]' : '' }}">
                </div>
                <span class="absolute left-1.5 top-1.5 rounded bg-black/50 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-white">
                    Mobile
                </span>
                @unless ($slide->has_mobile_image)
                    <div class="absolute inset-x-0 bottom-1.5 flex justify-center">
                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-800">
                            Usando desktop
                        </span>
                    </div>
                @endunless
            </div>
        </div>

        {{-- Metadata --}}
        <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1">
            {{-- Badge activo/inactivo (refleja el estado Alpine) --}}
            <span
                :class="active
                    ? 'bg-green-100 text-green-800'
                    : 'bg-gray-100 text-gray-600'"
                class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold transition">
                <span x-text="active ? 'Activo' : 'Oculto'"></span>
            </span>

            <span class="truncate text-sm font-semibold text-gray-900">{{ $displayName }}</span>

            @if ($linkLabel)
                <span class="truncate text-xs text-gray-400">{{ $linkLabel }}</span>
            @endif
        </div>
    </div>

    {{-- Acciones --}}
    <div class="flex shrink-0 flex-col items-center justify-between gap-2 px-3 py-4">

        {{-- Toggle activo --}}
        <button
            type="button"
            title="Activar / Desactivar"
            @click="
                fetch('{{ route('admin.home.hero.slides.toggle', $slide) }}', {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                })
                .then(r => r.json())
                .then(data => {
                    if (data.ok) { active = data.is_active; }
                    else { alert('No se pudo cambiar el estado.'); }
                })
                .catch(() => alert('Error de conexión al cambiar el estado.'));
            "
            :class="active ? 'bg-green-500 hover:bg-green-600' : 'bg-gray-200 hover:bg-gray-300'"
            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-brand-pink focus:ring-offset-2">
            <span
                :class="active ? 'translate-x-5' : 'translate-x-0'"
                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out">
            </span>
        </button>

        {{-- Editar --}}
        <button
            type="button"
            title="Editar banner"
            @click="
                $dispatch('open-drawer-edit', {
                    id: {{ $slide->id }},
                    url: '{{ route('admin.home.hero.slides.update', $slide) }}'
                });
                drawerOpen = true;
            "
            class="rounded-md p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
            </svg>
        </button>

        {{-- Eliminar --}}
        <form
            action="{{ route('admin.home.hero.slides.destroy', $slide) }}"
            method="POST"
            x-data
            @submit.prevent="$dispatch('open-confirm', {
                form: $el,
                title: 'Eliminar banner',
                message: '¿Eliminar el banner «{{ $displayName }}»? Esta acción no se puede deshacer.'
            })">
            @csrf
            @method('DELETE')
            <button type="submit" title="Eliminar banner"
                class="rounded-md p-1.5 text-gray-400 transition hover:bg-red-50 hover:text-red-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                </svg>
            </button>
        </form>

    </div>
</div>
