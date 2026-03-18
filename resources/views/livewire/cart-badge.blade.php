<div class="relative inline-block">
    <x-icon name="bag" />
    @if ($totalItems > 0)
        <span
            class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/4 -translate-y-1/4 bg-brand-pink rounded-full">
            {{ $totalItems }}
        </span>
    @endif
</div>
