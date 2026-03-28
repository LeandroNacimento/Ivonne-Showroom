<?php

namespace App\Queries;

use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SalesReportQuery
{
    public function run(string $dateFrom, string $dateTo): Collection
    {
        return Order::select(
            DB::raw('DATE(date) as date'),
            DB::raw('COUNT(*) as total_orders'),
            DB::raw('SUM(total) as total_sales')
        )
            ->whereBetween('date', [$dateFrom.' 00:00:00', $dateTo.' 23:59:59'])
            ->where('status', '!=', Order::STATUS_CANCELLED)
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }
}
