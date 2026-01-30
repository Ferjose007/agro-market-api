<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order; // <--- 1. DESCOMENTA ESTO
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Detección de Granja
        $myFarm = $user->farmProfile;

        if (!$myFarm) {
            return response()->json(['has_farm' => false]);
        }

        $farmId = $myFarm->id;

        // 1. Gráfico de Productos
        $chartData = Product::where('farm_profile_id', $farmId)
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select('categories.name as label', DB::raw('count(*) as total'))
            ->groupBy('categories.name')
            ->get();

        // 2. Stock Bajo
        $lowStockProducts = Product::where('farm_profile_id', $farmId)
            ->orderBy('stock_quantity', 'asc')
            ->take(5)
            ->get(['name', 'stock_quantity', 'unit', 'price_per_unit']);

        // 3. Pedidos / Ventas (REACTIVADO) 🚀
        // Calculamos las ganancias reales sumando los pedidos 'completados'
        $totalSales = Order::where('farm_profile_id', $farmId)
            ->where('status', 'completado')
            ->sum('total_amount');

        // Contamos cuántos están pendientes para el aviso
        $pendingOrders = Order::where('farm_profile_id', $farmId)
            ->where('status', 'pendiente')
            ->count();

        // Traemos los últimos 5 para la lista
        $recentOrders = Order::where('farm_profile_id', $farmId)
            ->with('user:id,name')
            ->latest()
            ->take(5)
            ->get();

        return response()->json([
            'has_farm' => true,
            'chart_data' => $chartData,
            'low_stock' => $lowStockProducts,
            'kpis' => [
                'total_earnings' => $totalSales,
                'pending_orders' => $pendingOrders
            ],
            'recent_orders' => $recentOrders
        ]);
    }
}