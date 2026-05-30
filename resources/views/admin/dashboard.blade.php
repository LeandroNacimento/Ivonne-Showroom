@extends('layouts.admin')

@section('page_title', 'Dashboard')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
            <p class="mt-1 text-sm text-gray-500">Resumen y métricas clave de {{ now()->locale('es')->isoFormat('MMMM YYYY') }}</p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center gap-3">
             <span class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" /></svg>
                {{ now()->format('d/m/Y') }}
            </span>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Sales Today -->
        <div class="relative overflow-hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 hover:shadow-md transition-shadow">
            <dt>
                <div class="absolute rounded-md bg-brand-blush p-3">
                    <svg class="h-6 w-6 text-brand-pink" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <p class="ml-16 truncate text-sm font-medium text-gray-500">Ventas Hoy</p>
            </dt>
            <dd class="ml-16 flex items-baseline pb-1 sm:pb-2">
                <p class="text-2xl font-semibold text-gray-900">${{ number_format($todaySales, 0, ',', '.') }}</p>
            </dd>
        </div>

        <!-- Sales Month -->
        <div class="relative overflow-hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 hover:shadow-md transition-shadow">
            <dt>
                <div class="absolute rounded-md bg-purple-50 p-3">
                    <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
                </div>
                <p class="ml-16 truncate text-sm font-medium text-gray-500">Ventas Mes</p>
            </dt>
            <dd class="ml-16 flex items-baseline pb-1 sm:pb-2">
                <p class="text-2xl font-semibold text-gray-900">${{ number_format($monthSales, 0, ',', '.') }}</p>
            </dd>
        </div>

        <!-- Pending Orders -->
        <div class="relative overflow-hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 hover:shadow-md transition-shadow">
            <dt>
                <div class="absolute rounded-md bg-yellow-50 p-3">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <p class="ml-16 truncate text-sm font-medium text-gray-500">Pendientes</p>
            </dt>
            <dd class="ml-16 flex items-baseline pb-1 sm:pb-2">
                <p class="text-2xl font-semibold text-gray-900">{{ $pendingOrders }}</p>
            </dd>
        </div>

        <!-- Low Stock -->
        <div class="relative overflow-hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 hover:shadow-md transition-shadow">
            <dt>
                <div class="absolute rounded-md bg-red-50 p-3">
                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                </div>
                <p class="ml-16 truncate text-sm font-medium text-gray-500">Stock Crítico</p>
            </dt>
            <dd class="ml-16 flex items-baseline pb-1 sm:pb-2">
                <p class="text-2xl font-semibold text-gray-900">{{ $lowStockProducts->count() }}</p>
            </dd>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent Orders -->
        <div class="lg:col-span-2 space-y-4">
            <div class="sm:flex sm:items-center sm:justify-between">
                <h2 class="text-lg font-bold text-gray-900">Ultimos Pedidos</h2>
                <a href="{{ route('admin.orders.index') }}" class="text-sm font-medium text-brand-pink hover:text-brand-heart">Ver todos &rarr;</a>
            </div>
            
            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm ring-1 ring-gray-900/5">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-xs font-semibold text-gray-500 uppercase sm:pl-6">Cliente</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Estado</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Total</th>
                            <th scope="col" class="px-3 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($recentOrders as $order)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                <a href="{{ route('admin.orders.show', $order) }}" class="hover:underline">{{ $order->client->name }}</a>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                <x-admin.status-badge :status="$order->status" />
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm font-semibold text-gray-900">${{ number_format($order->total, 0, ',', '.') }}</td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $order->date->format('d/m/Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-sm text-gray-500 text-center italic">No hay pedidos recientes.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Sidebar Widgets -->
        <div class="space-y-8">
            <!-- Quick Actions -->
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <h3 class="text-base font-semibold leading-6 text-gray-900 mb-4">Atajos Rápidos</h3>
                <div class="flex flex-col gap-3">
                    <a href="{{ route('admin.products.create') }}" class="flex items-center justify-between rounded-lg border border-gray-200 p-3 text-sm font-medium text-gray-700 hover:border-brand-pink hover:text-brand-pink hover:bg-brand-blush/10 transition-all group">
                        <span>Nuevo Producto</span>
                        <svg class="h-5 w-5 text-gray-400 group-hover:text-brand-pink" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    </a>
                    <a href="{{ route('admin.orders.create') }}" class="flex items-center justify-between rounded-lg border border-gray-200 p-3 text-sm font-medium text-gray-700 hover:border-brand-pink hover:text-brand-pink hover:bg-brand-blush/10 transition-all group">
                        <span>Nuevo Pedido</span>
                        <svg class="h-5 w-5 text-gray-400 group-hover:text-brand-pink" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                    </a>
                    <a href="{{ route('admin.clients.create') }}" class="flex items-center justify-between rounded-lg border border-gray-200 p-3 text-sm font-medium text-gray-700 hover:border-brand-pink hover:text-brand-pink hover:bg-brand-blush/10 transition-all group">
                        <span>Nuevo Cliente</span>
                        <svg class="h-5 w-5 text-gray-400 group-hover:text-brand-pink" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                    </a>
                </div>
            </div>

            <!-- Top Products -->
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5">
                <h3 class="text-base font-semibold leading-6 text-gray-900 mb-4">Top Productos (Mes)</h3>
                 <ul role="list" class="divide-y divide-gray-100">
                    @forelse($topProducts as $product)
                    <li class="flex items-center justify-between py-3">
                        <div class="flex items-center gap-3">
                             <div class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-500">
                                #{{ $loop->iteration }}
                            </div>
                            <p class="text-sm font-medium text-gray-900">{{ $product->name }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                             <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">{{ $product->total_sold }} un.</span>
                        </div>
                    </li>
                    @empty
                    <li class="py-3 text-sm text-gray-500 text-center">No hay datos suficientes.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <!-- Stock Alerts -->
    @if($lowStockProducts->count() > 0)
    <div class="rounded-xl border border-red-200 bg-white p-6 shadow-sm">
        <div class="flex items-center gap-2 mb-4">
             <div class="rounded-full bg-red-100 p-1">
                <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
             </div>
             <h3 class="text-base font-semibold text-gray-900">Alertas de Stock Bajo</h3>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($lowStockProducts as $product)
            <div class="flex items-center justify-between rounded-lg bg-red-50 p-3 sm:px-4">
                <span class="text-sm font-medium text-gray-900 truncate">{{ $product->name }}</span>
                <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800">
                    {{ $product->total_stock }} un.
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
