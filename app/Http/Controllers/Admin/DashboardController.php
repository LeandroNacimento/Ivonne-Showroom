<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Settings
        $minStock = Setting::where('key', 'min_stock')->value('value') ?? 5;

        // Stats
        $todaySales = Order::whereDate('date', Carbon::today())
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        $monthSales = Order::whereMonth('date', Carbon::now()->month)
            ->whereYear('date', Carbon::now()->year)
            ->where('status', '!=', 'cancelled')
            ->sum('total');

        $pendingOrders = Order::whereIn('status', ['draft', 'reserved'])->count();

        // Low Stock
        // Since total_stock is an accessor, we fetch products and filter. 
        // For better performance in large DBs, we would use a subquery or aggregate.
        $lowStockProducts = Product::with('variations')->get()
            ->filter(function ($product) use ($minStock) {
                return $product->total_stock < $minStock;
            });

        // Recent Orders
        $recentOrders = Order::with('client')->latest('date')->take(5)->get();

        // Top Products
        $topProducts = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select('products.name', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'todaySales',
            'monthSales',
            'pendingOrders',
            'lowStockProducts',
            'recentOrders',
            'topProducts',
            'minStock'
        ));
    }
}
