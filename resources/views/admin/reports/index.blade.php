@extends('layouts.admin')

@section('content')
<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Reportes</h1>
    <form action="{{ route('admin.reports.export') }}" method="GET" class="inline-block">
        <input type="hidden" name="date_from" value="{{ $dateFrom }}">
        <input type="hidden" name="date_to" value="{{ $dateTo }}">
        <input type="hidden" name="report_type" value="{{ $reportType }}">
        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
            Exportar CSV
        </button>
    </form>
</div>

<!-- Filters -->
<div class="bg-white rounded-lg shadow-sm p-4 mb-6">
    <form action="{{ route('admin.reports.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label for="report_type" class="block text-sm font-medium text-gray-700 mb-1">Tipo de Reporte</label>
            <select name="report_type" id="report_type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
                <option value="sales" {{ $reportType == 'sales' ? 'selected' : '' }}>Ventas por Fecha</option>
                <option value="products" {{ $reportType == 'products' ? 'selected' : '' }}>Productos Más Vendidos</option>
                <option value="categories" {{ $reportType == 'categories' ? 'selected' : '' }}>Ventas por Categoría</option>
            </select>
        </div>
        <div>
            <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
            <input type="date" name="date_from" id="date_from" value="{{ $dateFrom }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
        </div>
        <div>
            <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
            <input type="date" name="date_to" id="date_to" value="{{ $dateTo }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-brand-pink focus:ring focus:ring-brand-pink focus:ring-opacity-50">
        </div>
        <div class="flex items-end">
            <button type="submit" class="w-full bg-brand-pink text-white px-4 py-2 rounded-md hover:bg-brand-heart transition-colors">
                Generar Reporte
            </button>
        </div>
    </form>
</div>

<!-- Results -->
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                @if($reportType === 'sales')
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad Pedidos</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Ventas</th>
                @elseif($reportType === 'products')
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad Vendida</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ingresos Totales</th>
                @elseif($reportType === 'categories')
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad Vendida</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ingresos Totales</th>
                @endif
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($data as $row)
            <tr>
                @if($reportType === 'sales')
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ \Carbon\Carbon::parse($row->date)->format('d/m/Y') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ $row->total_orders }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">${{ number_format($row->total_sales, 0, ',', '.') }}</td>
                @elseif($reportType === 'products')
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ $row->total_quantity }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">${{ number_format($row->total_revenue, 0, ',', '.') }}</td>
                @elseif($reportType === 'categories')
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ $row->total_quantity }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">${{ number_format($row->total_revenue, 0, ',', '.') }}</td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="3" class="px-6 py-4 text-sm text-gray-500 text-center">No hay datos para el rango seleccionado.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
