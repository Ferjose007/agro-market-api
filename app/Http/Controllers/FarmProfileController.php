<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FarmProfileController extends Controller
{
    public function show(Request $request)
    {
        $data = $request->user()->farmProfile;

        // Si no existe, devolvemos null (el frontend mostrará formulario vacío)
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
            'address' => 'nullable|string|max:255', // Nuevo
            'whatsapp_number' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',   // Nuevo
            'location_lat' => 'nullable|numeric',         // Cambiado a nullable por si no usan GPS
            'location_lng' => 'nullable|numeric',
            'soil_type' => 'nullable|string',
        ]);

        // Guardamos o actualizamos
        $profile = $request->user()->farmProfile()->updateOrCreate(
            ['user_id' => $request->user()->id],
            $validated // Pasamos todo el array validado directamente
        );

        return response()->json(['message' => 'Perfil actualizado', 'profile' => $profile]);
    }
}