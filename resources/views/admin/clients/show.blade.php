@extends('layouts.admin')

@section('page_title', 'Detalle de Cliente')
@section('breadcrumbs')
    <a href="{{ route('admin.dashboard') }}" class="hover:text-gray-900 transition-colors">Dashboard</a>
    <span class="mx-2 text-gray-400">/</span>
    <a href="{{ route('admin.clients.index') }}" class="hover:text-gray-900 transition-colors">Clientes</a>
    <span class="mx-2 text-gray-400">/</span>
    <span class="text-gray-900">{{ $client->name }}</span>
@endsection

@section('content')
<div class="max-w-4xl mx-auto" x-data="orderQuickView()">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Detalle del Cliente</h1>
        <a href="{{ route('admin.clients.index') }}" class="text-gray-600 hover:text-gray-900">
            &larr; Volver
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Client Info -->
        <div class="md:col-span-1 space-y-6">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ $client->name }}</h2>
                
                <div class="space-y-3 text-sm">
                    @if($client->phone)
                    <div class="flex items-center text-gray-600">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                        {{ $client->phone }}
                    </div>
                    @endif
                    @if($client->instagram)
                    <div class="flex items-center text-gray-600">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37zm1.5-4.87h.01"></path></svg>
                        {{ $client->instagram }}
                    </div>
                    @endif
                    @if($client->email)
                    <div class="flex items-center text-gray-600">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        {{ $client->email }}
                    </div>
                    @endif
                </div>

                @if($client->notes)
                <div class="mt-6 pt-6 border-t border-gray-100">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Notas</h3>
                    <p class="text-sm text-gray-600">{{ $client->notes }}</p>
                </div>
                @endif

                <div class="mt-6 pt-6 border-t border-gray-100">
                    <a href="{{ route('admin.clients.edit', $client) }}" class="block w-full text-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Editar Cliente
                    </a>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Estadísticas</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500">Pedidos</p>
                        <p class="text-xl font-bold text-gray-900">{{ $stats['total_orders'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Total Gastado</p>
                        <p class="text-xl font-bold text-brand-pink">${{ number_format($stats['total_spent'], 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="mt-4">
                    <p class="text-xs text-gray-500">Última Compra</p>
                    <p class="text-sm font-medium text-gray-900">{{ $stats['last_order'] }}</p>
                </div>
            </div>
        </div>

        <!-- Order History -->
        <div class="md:col-span-2">
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-800">Historial de Pedidos</h2>
                    <!-- <a href="#" class="text-sm text-brand-pink hover:text-brand-heart font-medium">+ Nuevo Pedido</a> -->
                </div>
                
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($client->orders as $order)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">#{{ $order->id }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $order->date->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <x-admin.status-badge :status="$order->status" />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">${{ number_format($order->total, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button @click='openOrder(@json($order))' class="text-indigo-600 hover:text-indigo-900 focus:outline-none">Ver</button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-sm text-gray-500 text-center">Este cliente aún no tiene pedidos.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick View Modal & Script -->
    <script>
    function orderQuickView() {
        return {
            selectedOrder: null,

            openOrder(order) {
                this.selectedOrder = order;
                document.body.classList.add('overflow-hidden');
            },

            close() {
                this.selectedOrder = null;
                document.body.classList.remove('overflow-hidden');
            }
        }
    }
    </script>

    <!-- Modal único con imágenes -->
    <div 
        x-cloak
        x-show="selectedOrder"
        x-transition
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4"
    >
        <div 
            @click.away="close()"
            class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-hidden flex flex-col"
        >

            <!-- Header -->
            <div class="flex justify-between items-center border-b p-4">
                <h2 class="text-lg font-semibold">
                    Pedido #<span x-text="selectedOrder.id"></span>
                </h2>

                <button @click="close()" class="text-gray-500 hover:text-black text-xl">
                    ✕
                </button>
            </div>

            <!-- Body -->
            <div class="p-4 overflow-y-auto space-y-3">

                <template x-if="selectedOrder.items && selectedOrder.items.length">
                    <div class="space-y-3">
                        <template x-for="item in selectedOrder.items" :key="item.id">
                            <div class="flex gap-3 items-center border rounded-lg p-3">

                                <!-- Imagen con zoom -->
                                <div class="relative w-16 h-16 flex-shrink-0 overflow-hidden rounded-lg border bg-gray-100">
                                    <img 
                                        :src="item.product?.cover_url ?? '/img/placeholder-product.jpg'"
                                        x-on:error="$event.target.src = '/img/placeholder-product.jpg'"
                                        loading="lazy"
                                        alt=""
                                        class="w-full h-full object-cover transition-transform duration-300 hover:scale-150"
                                    >
                                </div>

                                <!-- Info -->
                                <div class="flex-1">
                                    <p class="font-medium" x-text="item.product?.name ?? 'Producto eliminado'"></p>

                                    <p class="text-sm text-gray-500"
                                       x-text="item.variation ? ((item.variation.product_color ? item.variation.product_color.name + ' - ' : '') + item.variation.size) : 'Sin variación'">
                                    </p>
                                </div>

                                <!-- Cantidad y subtotal -->
                                <div class="text-right">
                                    <p class="text-sm">x<span x-text="item.quantity"></span></p>
                                    <p class="font-semibold">$<span x-text="Number(item.subtotal).toLocaleString('es-AR')"></span></p>
                                </div>

                            </div>
                        </template>
                    </div>
                </template>

                <template x-if="!selectedOrder.items || !selectedOrder.items.length">
                    <div class="py-8 text-center text-gray-500">
                        Este pedido no tiene productos
                    </div>
                </template>

            </div>

            <!-- Footer -->
            <div class="border-t p-4 flex justify-between items-center bg-gray-50">

                <span class="text-lg font-bold text-brand-pink">
                    Total: $<span x-text="Number(selectedOrder.total).toLocaleString('es-AR')"></span>
                </span>

                <div class="flex gap-3">
                    <a 
                        :href="`{{ route('admin.orders.show', ':id') }}`.replace(':id', selectedOrder.id)"
                        class="px-4 py-2 border border-gray-300 bg-white text-gray-700 rounded-lg hover:bg-gray-50 shadow-sm font-medium">
                        Ver completo
                    </a>

                    <button 
                        @click="close()"
                        class="px-4 py-2 bg-gray-800 text-white rounded-lg hover:bg-gray-900 shadow-sm font-medium">
                        Cerrar
                    </button>
                </div>

            </div>

        </div>
    </div>
</div>
@endsection
