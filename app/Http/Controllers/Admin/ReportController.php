<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Queries\CategoriesReportQuery;
use App\Queries\ProductsReportQuery;
use App\Queries\SalesReportQuery;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $reportType = $request->input('report_type', 'sales');

        $data = match ($reportType) {
            'sales' => app(SalesReportQuery::class)->run($dateFrom, $dateTo),
            'products' => app(ProductsReportQuery::class)->run($dateFrom, $dateTo),
            'categories' => app(CategoriesReportQuery::class)->run($dateFrom, $dateTo),
            default => collect(),
        };

        return view('admin.reports.index', compact('data', 'dateFrom', 'dateTo', 'reportType'));
    }

    public function export(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->input('date_to', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $reportType = $request->input('report_type', 'sales');

        $fileName = 'reporte_'.$reportType.'_'.date('Ymd_His').'.csv';

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$fileName",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($dateFrom, $dateTo, $reportType) {
            $file = fopen('php://output', 'w');

            if ($reportType === 'sales') {
                fputcsv($file, ['Fecha', 'Cantidad Pedidos', 'Total Ventas']);
                $data = app(SalesReportQuery::class)->run($dateFrom, $dateTo);

                foreach ($data as $row) {
                    fputcsv($file, [$row->date, $row->total_orders, $row->total_sales]);
                }
            } elseif ($reportType === 'products') {
                fputcsv($file, ['Producto', 'Cantidad Vendida', 'Ingresos Totales']);
                $data = app(ProductsReportQuery::class)->run($dateFrom, $dateTo);

                foreach ($data as $row) {
                    fputcsv($file, [$row->name, $row->total_quantity, $row->total_revenue]);
                }
            } elseif ($reportType === 'categories') {
                fputcsv($file, ['Categoría', 'Cantidad Vendida', 'Ingresos Totales']);
                $data = app(CategoriesReportQuery::class)->run($dateFrom, $dateTo);

                foreach ($data as $row) {
                    fputcsv($file, [$row->name, $row->total_quantity, $row->total_revenue]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
