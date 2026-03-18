@extends('layouts.admin')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Pedido #{{ $order->id }}</h1>
        <div class="flex space-x-3">
            <a href="{{ route('admin.orders.index') }}" class="text-gray-600 hover:text-gray-900 px-4 py-2">
                &larr; Volver
            </a>
            <a href="{{ route('admin.orders.edit', $order) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition-colors">
                Editar
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Order Info -->
        <div class="md:col-span-2 space-y-6">
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-800">Ítems</h2>
                </div>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Variación</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Cant.</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Precio</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach($order->items as $item)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $item->product->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $item->color }} / {{ $item->size }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 text-right">{{ $item->quantity }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900 text-right">${{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900 text-right">${{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-gray-500">Subtotal</td>
                            <td class="px-6 py-3 text-right text-sm font-medium text-gray-900">${{ number_format($order->total - $order->shipping_cost, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-gray-500">Envío</td>
                            <td class="px-6 py-3 text-right text-sm font-medium text-gray-900">${{ number_format($order->shipping_cost, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="px-6 py-3 text-right text-base font-bold text-gray-900">Total</td>
                            <td class="px-6 py-3 text-right text-base font-bold text-brand-pink">${{ number_format($order->total, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Client -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Cliente</h3>
                <div class="flex items-center mb-2">
                    <div class="h-10 w-10 rounded-full bg-brand-blush flex items-center justify-center text-brand-pink font-bold text-lg">
                        {{ substr($order->client->name, 0, 1) }}
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">{{ $order->client->name }}</p>
                        <p class="text-xs text-gray-500">{{ $order->client->email }}</p>
                    </div>
                </div>
                @if($order->client->phone)
                <div class="flex items-center text-sm text-gray-600 mt-2">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                    {{ $order->client->phone }}
                </div>
                @endif
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <a href="{{ route('admin.clients.show', $order->client) }}" class="text-sm text-brand-pink hover:underline">Ver perfil del cliente</a>
                </div>
            </div>

            <!-- Details -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">Detalles</h3>
                
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-gray-500">Fecha</p>
                        <p class="text-sm font-medium text-gray-900">{{ $order->date->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Estado</p>
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full mt-1
                            {{ $order->status === 'paid' ? 'bg-green-100 text-green-800' : 
                               ($order->status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Método de Pago</p>
                        <p class="text-sm font-medium text-gray-900">{{ ucfirst($order->payment_method ?? '-') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Entrega</p>
                        <p class="text-sm font-medium text-gray-900">{{ $order->delivery_type === 'showroom' ? 'Retiro en Showroom' : 'Envío' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
