<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FarmProfile;

class FarmProfileController extends Controller
{
    public function show(Request $request)
    {
        $data = $request->user()->farmProfile;

        if (!$data) {
            return response()->json(null);
        }

        return response()->json($data);
    }

    public function update(Request $request)
    {
        // Validamos usando TUS nombres de campos
        $validated = $request->validate([
            'farm_name' => 'required|string|max:255',
            'bio' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'whatsapp_number' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'location_lat' => 'nullable|numeric',
            'location_lng' => 'nullable|numeric',
            'soil_type' => 'nullable|string',
        ]);

        $profile = $request->user()->farmProfile()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $validated
        );

        return response()->json(['message' => 'Perfil actualizado', 'profile' => $profile]);
    }
    // --- LISTA PÚBLICA DE VENDEDORES ---
    public function publicList()
    {

        $profiles = FarmProfile::withCount([
            'products' => function ($query) {
                $query->where('is_active', true);
            }
        ])
            ->orderBy('products_count', 'desc')
            ->get();

        return response()->json($profiles);
    }
}