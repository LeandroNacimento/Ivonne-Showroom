<div class="space-y-6">
    <!-- Page Header -->
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Pedidos</h1>
            <p class="mt-1 text-sm text-gray-500">Gestiona y realiza seguimiento de todas las órdenes en tiempo real.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('admin.orders.create') }}"
                class="inline-flex items-center justify-center rounded-lg bg-brand-pink px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-heart transition-all focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-pink">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path
                        d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                </svg>
                Nuevo Pedido
            </a>
        </div>
    </div>

    <!-- Filters & Table Container -->
    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <!-- Filter Bar -->
        <div class="border-b border-gray-200 px-4 py-5 sm:px-6">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Text Search -->
                <div class="relative">
                    <input wire:model.live.debounce.300ms="search" type="text"
                        class="block w-full rounded-md border-0 py-1.5 pl-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-pink sm:text-sm sm:leading-6"
                        placeholder="Buscar ID o Cliente...">
                    <div wire:loading.class.remove="hidden" wire:target="search"
                        class="hidden absolute inset-y-0 right-0 flex items-center pr-3">
                        <span class="text-xs text-gray-500 font-medium">...</span>
                    </div>
                </div>

                <!-- Status Select -->
                <div>
                    <select wire:model.live="status"
                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-brand-pink sm:text-sm sm:leading-6">
                        <option value="">Todos los Estados</option>
                        <option value="{{ \App\Models\Order::STATUS_PENDING }}">Pendiente</option>
                        <option value="{{ \App\Models\Order::STATUS_RESERVED }}">Reservado</option>
                        <option value="{{ \App\Models\Order::STATUS_DELIVERED }}">Entregado</option>
                        <option value="{{ \App\Models\Order::STATUS_CANCELLED }}">Cancelado</option>
                    </select>
                </div>

                <!-- Date From -->
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-500 w-12">Desde:</span>
                    <input type="date" wire:model.live="date_from"
                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-pink sm:text-sm sm:leading-6">
                </div>

                <!-- Date To -->
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-500 w-12">Hasta:</span>
                    <input type="date" wire:model.live="date_to"
                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-pink sm:text-sm sm:leading-6">
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead>
                    <tr class="bg-gray-50/50">
                        <th scope="col"
                            class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold text-gray-900 sm:pl-6">ID</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-gray-900">Cliente
                        </th>
                        <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-gray-900">Fecha</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-gray-900">Estado</th>
                        <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-gray-900">Total</th>
                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                            <span class="sr-only">Acciones</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($orders as $order)
                        <tr class="hover:bg-gray-50 transition-colors group" wire:key="order-{{ $order->id }}">
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-mono text-gray-500 sm:pl-6">
                                #{{ $order->id }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-900">
                                {{ $order->client->name }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                {{ $order->date->format('d/m/Y') }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                @php
                                    $statusClasses = match ($order->status) {
                                        \App\Models\Order::STATUS_PENDING => 'text-gray-600',
                                        \App\Models\Order::STATUS_RESERVED => 'text-yellow-600',
                                        \App\Models\Order::STATUS_DELIVERED => 'text-green-600',
                                        \App\Models\Order::STATUS_CANCELLED => 'text-red-600',
                                        default => 'text-gray-600',
                                    };
                                    $statusLabel = match ($order->status) {
                                        \App\Models\Order::STATUS_PENDING => 'Pendiente',
                                        \App\Models\Order::STATUS_RESERVED => 'Reservado',
                                        \App\Models\Order::STATUS_DELIVERED => 'Entregado',
                                        \App\Models\Order::STATUS_CANCELLED => 'Cancelado',
                                        default => ucfirst($order->status),
                                    };
                                @endphp
                                <span class="inline-flex items-center font-semibold {{ $statusClasses }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm font-semibold text-gray-900">
                                ${{ number_format($order->total, 0, ',', '.') }}</td>
                            <td
                                class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                <div
                                    class="flex items-center justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <a href="{{ route('admin.orders.show', $order) }}"
                                        class="text-gray-500 hover:text-brand-pink p-1" title="Ver">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.orders.edit', $order) }}"
                                        class="text-gray-500 hover:text-indigo-600 p-1" title="Editar">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                        </svg>
                                    </a>
                                    <form action="{{ route('admin.orders.destroy', $order) }}" method="POST"
                                        class="inline-block"
                                        onsubmit="return confirm('¿Estás seguro de eliminar este pedido?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-gray-500 hover:text-red-600 p-1"
                                            title="Eliminar">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                No se encontraron pedidos con estos filtros.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-200 px-4 py-3 sm:px-6">
            {{ $orders->links() }}
        </div>
    </div>
</div>
