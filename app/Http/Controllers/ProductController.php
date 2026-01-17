<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    // Listar productos (Público)
    public function index()
    {
        // Cargamos también el perfil de la granja y el desglose de precios
        return Product::with(['farmProfile', 'priceBreakdown'])
            ->where('is_active', true)
            ->latest()
            ->get();
    }

    // Crear producto (Solo Agricultores)
    public function store(Request $request)
    {
        // 1. Validaciones
        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:1',
            'unit_type' => 'required|string', // kg, saco

            // Validación de Transparencia
            'farmer_earning' => 'required|numeric',
            'platform_fee' => 'required|numeric',
            'logistics_cost' => 'required|numeric',
        ]);

        $user = $request->user();

        // Verificar si el usuario tiene perfil de granja
        if (!$user->farmProfile) {
            return response()->json(['error' => 'Debes crear un perfil de granja antes de vender.'], 403);
        }

        // 2. Transacción de Base de Datos
        try {
            $product = DB::transaction(function () use ($user, $validated) {
                // A. Crear el Producto
                $newProduct = $user->farmProfile->products()->create([
                    'name' => $validated['name'],
                    'description' => $validated['description'] ?? null,
                    'price' => $validated['price'],
                    'stock_quantity' => $validated['stock_quantity'],
                    'unit_type' => $validated['unit_type'],
                ]);

                // B. Crear el Desglose de Precios (Transparencia)
                $newProduct->priceBreakdown()->create([
                    'farmer_earning' => $validated['farmer_earning'],
                    'platform_fee' => $validated['platform_fee'],
                    'logistics_cost' => $validated['logistics_cost'],
                    'taxes' => 0, // Taxes lo dejamos en 0 por ahora o lo calculamos
                ]);

                return $newProduct;
            });

            return response()->json(['message' => 'Producto publicado correctamente', 'product' => $product], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al guardar el producto: ' . $e->getMessage()], 500);
        }
    }
}
