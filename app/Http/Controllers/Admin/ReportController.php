<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $reportType = $request->input('report_type', 'sales');

        $data = [];

        if ($reportType === 'sales') {
            $data = Order::select(
                DB::raw('DATE(date) as date'), 
                DB::raw('COUNT(*) as total_orders'), 
                DB::raw('SUM(total) as total_sales')
            )
            ->whereBetween('date', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->where('status', '!=', 'cancelled')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        } elseif ($reportType === 'products') {
            $data = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->select(
                    'products.name',
                    DB::raw('SUM(order_items.quantity) as total_quantity'),
                    DB::raw('SUM(order_items.subtotal) as total_revenue')
                )
                ->whereBetween('orders.date', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                ->where('orders.status', '!=', 'cancelled')
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('total_quantity')
                ->get();
        } elseif ($reportType === 'categories') {
            $data = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->select(
                    'categories.name',
                    DB::raw('SUM(order_items.quantity) as total_quantity'),
                    DB::raw('SUM(order_items.subtotal) as total_revenue')
                )
                ->whereBetween('orders.date', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                ->where('orders.status', '!=', 'cancelled')
                ->groupBy('categories.id', 'categories.name')
                ->orderByDesc('total_revenue')
                ->get();
        }

        return view('admin.reports.index', compact('data', 'dateFrom', 'dateTo', 'reportType'));
    }

    public function export(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $reportType = $request->input('report_type', 'sales');

        $fileName = 'reporte_' . $reportType . '_' . date('Ymd_His') . '.csv';
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($dateFrom, $dateTo, $reportType) {
            $file = fopen('php://output', 'w');

            if ($reportType === 'sales') {
                fputcsv($file, ['Fecha', 'Cantidad Pedidos', 'Total Ventas']);
                $data = Order::select(
                    DB::raw('DATE(date) as date'), 
                    DB::raw('COUNT(*) as total_orders'), 
                    DB::raw('SUM(total) as total_sales')
                )
                ->whereBetween('date', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                ->where('status', '!=', 'cancelled')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

                foreach ($data as $row) {
                    fputcsv($file, [$row->date, $row->total_orders, $row->total_sales]);
                }
            } elseif ($reportType === 'products') {
                fputcsv($file, ['Producto', 'Cantidad Vendida', 'Ingresos Totales']);
                $data = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->join('products', 'order_items.product_id', '=', 'products.id')
                    ->select(
                        'products.name',
                        DB::raw('SUM(order_items.quantity) as total_quantity'),
                        DB::raw('SUM(order_items.subtotal) as total_revenue')
                    )
                    ->whereBetween('orders.date', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                    ->where('orders.status', '!=', 'cancelled')
                    ->groupBy('products.id', 'products.name')
                    ->orderByDesc('total_quantity')
                    ->get();

                foreach ($data as $row) {
                    fputcsv($file, [$row->name, $row->total_quantity, $row->total_revenue]);
                }
            } elseif ($reportType === 'categories') {
                fputcsv($file, ['Categoría', 'Cantidad Vendida', 'Ingresos Totales']);
                $data = OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
                    ->join('products', 'order_items.product_id', '=', 'products.id')
                    ->join('categories', 'products.category_id', '=', 'categories.id')
                    ->select(
                        'categories.name',
                        DB::raw('SUM(order_items.quantity) as total_quantity'),
                        DB::raw('SUM(order_items.subtotal) as total_revenue')
                    )
                    ->whereBetween('orders.date', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
                    ->where('orders.status', '!=', 'cancelled')
                    ->groupBy('categories.id', 'categories.name')
                    ->orderByDesc('total_revenue')
                    ->get();

                foreach ($data as $row) {
                    fputcsv($file, [$row->name, $row->total_quantity, $row->total_revenue]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
