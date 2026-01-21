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
        // 1. Validaciones (Ajustamos los nombres para coincidir con Vue)
        $validated = $request->validate([
            'name' => 'required|string',
            'description' => 'nullable|string',
            'price_per_unit' => 'required|numeric|min:0', // <--- CAMBIADO
            'stock_quantity' => 'required|integer|min:1',
            'unit' => 'required|string', // <--- CAMBIADO (era unit_type)
            'category_id' => 'required|exists:categories,id', // <--- FALTABA ESTE (Vue lo envía)

            // Transparencia
            'farmer_earning' => 'required|numeric',
            'platform_fee' => 'required|numeric',
            'logistics_cost' => 'required|numeric',
        ]);

        $user = $request->user();

        if (!$user->farmProfile) {
            return response()->json(['error' => 'Debes crear un perfil de granja.'], 403);
        }

        try {
            $product = DB::transaction(function () use ($user, $validated) {
                // A. Crear Producto (Mapeamos los nombres correctamente)
                $newProduct = $user->farmProfile->products()->create([
                    'name' => $validated['name'],
                    'description' => $validated['description'] ?? null,
                    'price_per_unit' => $validated['price_per_unit'], // <--- CAMBIADO
                    'stock_quantity' => $validated['stock_quantity'],
                    'unit' => $validated['unit'], // <--- CAMBIADO
                    'category_id' => $validated['category_id'], // <--- AGREGADO
                ]);

                // B. Desglose (Esto estaba bien)
                $newProduct->priceBreakdown()->create([
                    'farmer_earning' => $validated['farmer_earning'],
                    'platform_fee' => $validated['platform_fee'],
                    'logistics_cost' => $validated['logistics_cost'],
                    'taxes' => 0,
                ]);

                return $newProduct;
            });

            return response()->json(['message' => 'Producto publicado correctamente', 'product' => $product], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function show(string $id)
    {
        // Busca el producto o falla si no existe. 
        // 'priceBreakdown' asegura que traigamos también los costos asociados.
        $product = Product::with('priceBreakdown')->findOrFail($id);

        return response()->json($product);
    }

    public function update(Request $request, string $id)
    {
        // 1. Validar los datos (igual que en store, pero a veces algunos campos son opcionales)
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_per_unit' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'unit' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            // Datos del desglose
            'farmer_earning' => 'required|numeric',
            'platform_fee' => 'required|numeric',
            'logistics_cost' => 'required|numeric',
        ]);

        // 2. Buscar el producto
        $product = Product::findOrFail($id);

        // 3. Verificar que el producto sea del usuario actual (Seguridad)
        if ($product->farm_id !== $request->user()->farm->id) {
            return response()->json(['message' => 'No tienes permiso para editar este producto'], 403);
        }

        // 4. Actualizar datos básicos
        $product->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'price_per_unit' => $validated['price_per_unit'],
            'stock_quantity' => $validated['stock_quantity'],
            'unit' => $validated['unit'],
            'category_id' => $validated['category_id'],
        ]);

        // 5. Actualizar el desglose de precios (Relación)
        $product->priceBreakdown()->update([
            'farmer_earning' => $validated['farmer_earning'],
            'platform_fee' => $validated['platform_fee'],
            'logistics_cost' => $validated['logistics_cost'],
            'taxes' => 0 // O el valor que corresponda
        ]);

        return response()->json(['message' => 'Producto actualizado correctamente', 'product' => $product]);
    }

    public function destroy(string $id)
    {
        $product = Product::findOrFail($id);
        $user = request()->user();

        if (!$user->farmProfile) {
            return response()->json(['message' => 'No tienes perfil de granja.'], 403);
        }

        // CORRECCIÓN: Usamos 'farm_profile_id' en lugar de 'farm_id'
        // Laravel usa snake_case del nombre del modelo padre (FarmProfile -> farm_profile_id)
        if ($product->farm_profile_id !== $user->farmProfile->id) {
            return response()->json(['message' => 'No autorizado. Este producto no es tuyo.'], 403);
        }

        $product->delete();

        return response()->json(['message' => 'Producto eliminado correctamente']);
    }
}
