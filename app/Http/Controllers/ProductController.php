<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    // Listar productos (Público)
    public function index(Request $request)
    {
        $user = $request->user();

        // --- BLINDAJE DE SEGURIDAD ---

        // 1. Si $user es NULL (No hay sesión iniciada o token inválido)
        if (!$user) {
            return response()->json(['message' => 'No autorizado'], 401);
        }

        // 2. Si el usuario existe pero NO tiene granja
        if (!$user->farmProfile) {
            return response()->json([]); // Devolvemos lista vacía limpia
        }

        // --- FIN BLINDAJE ---

        // Si llegamos aquí, es 100% seguro consultar
        return Product::where('farm_profile_id', $user->farmProfile->id)
            ->with('category')
            ->latest()
            ->get();
    }

    // --- FUNCIÓN PARA CREAR (STORE) ---
    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user->farmProfile) {
            return response()->json(['message' => 'Primero crea tu granja.'], 403);
        }

        // 1. Validamos SOLO lo que envía el formulario
        // (QUITAMOS farmer_earning, platform_fee, etc. de aquí porque son calculados)
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_per_unit' => 'required|numeric|min:0',
            'stock_quantity' => 'required|numeric|min:0',
            'unit' => 'required|string',
            'farming_type' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // 2. CÁLCULO AUTOMÁTICO DE COSTOS 🧮
        // Definimos las reglas de negocio (puedes ajustar los porcentajes)
        $price = $validated['price_per_unit'];

        $platformFee = $price * 0.10; // 10% Comisión de la plataforma
        $logisticsCost = $price * 0.05; // 5% Costo logístico (ejemplo)
        $farmerEarning = $price - $platformFee - $logisticsCost; // Lo que le queda al agricultor

        // 3. Inyectamos los datos calculados al array
        $validated['farm_profile_id'] = $user->farmProfile->id;
        $validated['platform_fee'] = $platformFee;
        $validated['logistics_cost'] = $logisticsCost;
        $validated['farmer_earning'] = $farmerEarning;

        // 4. Creamos el producto
        $product = Product::create($validated);

        return response()->json(['message' => 'Producto creado', 'product' => $product], 201);
    }

    public function show(string $id)
    {
        // Busca el producto o falla si no existe. 
        // 'priceBreakdown' asegura que traigamos también los costos asociados.
        $product = Product::with('priceBreakdown')->findOrFail($id);

        return response()->json($product);
    }

    // --- FUNCIÓN PARA ACTUALIZAR (UPDATE) ---
    public function update(Request $request, $id)
    {
        $user = $request->user();

        // Buscamos el producto y verificamos que sea DE ESTA GRANJA
        $product = Product::where('id', $id)
            ->where('farm_profile_id', $user->farmProfile->id)
            ->firstOrFail();

        // 1. Validamos
        $validated = $request->validate([
            'category_id' => 'exists:categories,id',
            'name' => 'string|max:255',
            'description' => 'nullable|string',
            'price_per_unit' => 'numeric|min:0',
            'stock_quantity' => 'numeric|min:0',
            'unit' => 'string',
            'farming_type' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // 2. Si cambió el precio, RECALCULAMOS todo 🔄
        if ($request->has('price_per_unit')) {
            $price = $validated['price_per_unit'];
            $validated['platform_fee'] = $price * 0.10;
            $validated['logistics_cost'] = $price * 0.05;
            $validated['farmer_earning'] = $price - $validated['platform_fee'] - $validated['logistics_cost'];
        }

        // 3. Actualizamos
        $product->update($validated);

        return response()->json(['message' => 'Producto actualizado', 'product' => $product]);
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
