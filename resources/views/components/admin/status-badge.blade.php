@props(['status'])

@php
    $statusClasses = match ($status) {
        \App\Models\Order::STATUS_PENDING => 'bg-gray-100 text-gray-800',
        \App\Models\Order::STATUS_RESERVED => 'bg-yellow-100 text-yellow-800',
        \App\Models\Order::STATUS_DELIVERED => 'bg-green-100 text-green-800',
        \App\Models\Order::STATUS_CANCELLED => 'bg-red-100 text-red-800',
        default => 'bg-gray-100 text-gray-800',
    };
    $statusLabel = match ($status) {
        \App\Models\Order::STATUS_PENDING => 'Pendiente',
        \App\Models\Order::STATUS_RESERVED => 'Reservado',
        \App\Models\Order::STATUS_DELIVERED => 'Entregado',
        \App\Models\Order::STATUS_CANCELLED => 'Cancelado',
        default => ucfirst($status),
    };
@endphp

<span {{ $attributes->merge(['class' => "px-2 inline-flex text-xs leading-5 font-semibold rounded-full $statusClasses"]) }}>
    {{ $statusLabel }}
</span>
