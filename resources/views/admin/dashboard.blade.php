@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
        <div class="text-sm text-gray-500">
            {{ now()->format('d/m/Y') }}
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Today's Sales -->
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-brand-pink">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Ventas Hoy</p>
                    <p class="text-2xl font-bold text-gray-900">${{ number_format($todaySales, 0, ',', '.') }}</p>
                </div>
                <div class="p-3 bg-brand-blush rounded-full text-brand-pink">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Monthly Sales -->
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Ventas Mes</p>
                    <p class="text-2xl font-bold text-gray-900">${{ number_format($monthSales, 0, ',', '.') }}</p>
                </div>
                <div class="p-3 bg-purple-100 rounded-full text-purple-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Pending Orders -->
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Pedidos Pendientes</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $pendingOrders }}</p>
                </div>
                <div class="p-3 bg-yellow-100 rounded-full text-yellow-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Low Stock -->
        <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Stock Bajo</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $lowStockProducts->count() }}</p>
                </div>
                <div class="p-3 bg-red-100 rounded-full text-red-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Orders -->
        <div class="lg:col-span-2 bg-white rounded-lg shadow-sm p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Pedidos Recientes</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($recentOrders as $order)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900">{{ $order->client->name }}</td>
                            <td class="px-4 py-3 text-sm">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    {{ $order->status === 'paid' ? 'bg-green-100 text-green-800' : 
                                       ($order->status === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">${{ number_format($order->total, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $order->date->format('d/m/Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-3 text-sm text-gray-500 text-center">No hay pedidos recientes.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4 text-right">
                <a href="{{ route('admin.orders.index') }}" class="text-sm text-brand-pink hover:text-brand-heart font-medium">Ver todos los pedidos &rarr;</a>
            </div>
        </div>

        <!-- Quick Shortcuts & Top Products -->
        <div class="space-y-6">
            <!-- Shortcuts -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Atajos Rápidos</h3>
                <div class="grid grid-cols-1 gap-3">
                    <a href="{{ route('admin.products.create') }}" class="flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-brand-pink hover:bg-brand-heart">
                        + Nuevo Producto
                    </a>
                    <a href="{{ route('admin.orders.create') }}" class="flex items-center justify-center px-4 py-2 border border-brand-pink rounded-md shadow-sm text-sm font-medium text-brand-pink bg-white hover:bg-brand-blush">
                        + Nuevo Pedido
                    </a>
                    <a href="{{ route('admin.clients.create') }}" class="flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        + Nuevo Cliente
                    </a>
                </div>
            </div>

            <!-- Top Products -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Top Productos (Mes)</h3>
                <ul class="divide-y divide-gray-200">
                    @forelse($topProducts as $product)
                    <li class="py-3 flex justify-between items-center">
                        <span class="text-sm text-gray-700">{{ $product->name }}</span>
                        <span class="text-sm font-bold text-gray-900">{{ $product->total_sold }} un.</span>
                    </li>
                    @empty
                    <li class="py-3 text-sm text-gray-500 text-center">No hay datos suficientes.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <!-- Low Stock Alerts -->
    @if($lowStockProducts->count() > 0)
    <div class="bg-white rounded-lg shadow-sm p-6 border border-red-200">
        <div class="flex items-center mb-4">
            <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            <h3 class="text-lg font-semibold text-red-700">Alertas de Stock Bajo (Menos de {{ $minStock }} un.)</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($lowStockProducts as $product)
            <div class="flex items-center justify-between p-3 bg-red-50 rounded-md">
                <span class="text-sm font-medium text-gray-800">{{ $product->name }}</span>
                <span class="px-2 py-1 text-xs font-bold text-red-800 bg-red-200 rounded-full">{{ $product->total_stock }} un.</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
