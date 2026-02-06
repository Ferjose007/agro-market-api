<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            $totalAmount = 0;
            $orderItemsData = [];

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);

                if ($product->stock_quantity < $item['quantity']) {
                    throw new \Exception("Stock insuficiente para: " . $product->name);
                }

                $subtotal = $product->price_per_unit * $item['quantity'];
                $totalAmount += $subtotal;

                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'price' => $product->price_per_unit,
                    'quantity' => $item['quantity'],
                    'subtotal' => $subtotal,
                ];

                $product->decrement('stock_quantity', $item['quantity']);
            }

            $order = Order::create([
                'user_id' => $request->user()->id,
                'total_amount' => $totalAmount,
                'status' => 'pending'
            ]);

            foreach ($orderItemsData as $data) {
                $order->items()->create($data);
            }

            DB::commit();

            return response()->json([
                'message' => 'Pedido realizado con éxito',
                'order_id' => $order->id
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function indexFarmer(Request $request)
    {
        $user = $request->user();
        $farm = $user->farmProfile;

        if (!$farm) {
            return response()->json(['message' => 'ERROR: Este usuario no tiene perfil de granja asociado'], 500);
        }

        $mySales = OrderItem::whereHas('product', function ($query) use ($farm) {
            $query->where('farm_profile_id', $farm->id);
        })
            ->with(['order.user', 'product'])
            ->latest()
            ->get();

        if ($mySales->isEmpty()) {
            return response()->json(['message' => 'Tienes granja (ID: ' . $farm->id . '), pero no se encontraron ventas para tus productos.'], 200);
        }

        return response()->json($mySales);
    }

    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with(['items.product'])
            ->latest()
            ->get();

        return response()->json($orders);
    }
}