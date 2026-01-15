<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FarmProfileController extends Controller
{
    // Crear o Actualizar perfil
    public function update(Request $request)
    {
        $request->validate([
            'farm_name' => 'required|string',
            'location_lat' => 'required|numeric',
            'location_lng' => 'required|numeric',
            'bio' => 'nullable|string',
            'whatsapp_number' => 'nullable|string'
        ]);

        // updateOrCreate busca si existe por 'user_id', si no, crea uno nuevo
        $profile = $request->user()->farmProfile()->updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'farm_name' => $request->farm_name,
                'location_lat' => $request->location_lat,
                'location_lng' => $request->location_lng,
                'bio' => $request->bio,
                'whatsapp_number' => $request->whatsapp_number
            ]
        );

        return response()->json(['message' => 'Perfil de granja actualizado', 'profile' => $profile]);
    }
}