<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $farm = $user->farmProfile;

        if (!$farm) {
            return response()->json([
                'has_farm' => false,
                'chart_data' => [],
                'low_stock' => [],
                'kpis' => ['total_earnings' => 0, 'pending_orders' => 0],
                'recent_orders' => []
            ]);
        }

        $categories = Category::withCount([
            'products' => function ($query) use ($farm) {
                $query->where('farm_profile_id', $farm->id);
            }
        ])->get();

        $chartData = $categories->map(function ($cat) {
            return [
                'label' => $cat->name,
                'total' => $cat->products_count
            ];
        })->filter(function ($item) {
            return $item['total'] > 0;
        })->values();


        $lowStock = Product::where('farm_profile_id', $farm->id)
            ->where('stock_quantity', '<', 20)
            ->orderBy('stock_quantity', 'asc')
            ->take(4)
            ->get(['id', 'name', 'stock_quantity', 'unit']);

        $totalEarnings = OrderItem::whereHas('product', function ($query) use ($farm) {
            $query->where('farm_profile_id', $farm->id);
        })->sum('subtotal');

        $pendingOrders = OrderItem::whereHas('product', function ($query) use ($farm) {
            $query->where('farm_profile_id', $farm->id);
        })->whereHas('order', function ($q) {
            $q->where('status', 'pending');
        })->distinct('order_id')->count('order_id');

        $recentOrders = Order::whereHas('items.product', function ($q) use ($farm) {
            $q->where('farm_profile_id', $farm->id);
        })
            ->with('user:id,name')
            ->latest()
            ->take(5)
            ->get(['id', 'user_id', 'status', 'created_at']);

        return response()->json([
            'has_farm' => true,
            'chart_data' => $chartData,
            'low_stock' => $lowStock,
            'kpis' => [
                'total_earnings' => number_format($totalEarnings, 2),
                'pending_orders' => $pendingOrders
            ],
            'recent_orders' => $recentOrders
        ]);
    }
}