<?php

namespace App\Queries;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class ProductsReportQuery
{
    public function run(string $dateFrom, string $dateTo): Collection
    {
        return OrderItem::join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select(
                'products.name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.subtotal) as total_revenue')
            )
            ->whereBetween('orders.date', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59'])
            ->where('orders.status', '!=', Order::STATUS_CANCELLED)
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_quantity')
            ->get();
    }
}
