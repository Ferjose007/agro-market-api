<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\User;
use App\Models\FarmProfile;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        // Buscamos tu usuario y tu granja
        // (Asegúrate de que el ID 1 sea el tuyo, o cámbialo)
        $miGranja = FarmProfile::first();
        $comprador = User::first(); // Usamos tu mismo usuario como comprador para probar

        if (!$miGranja || !$comprador) {
            $this->command->info('⚠️ No se encontró granja o usuario. Crea uno primero.');
            return;
        }

        // Pedido 1: Completado (Ganancia) 💰
        Order::create([
            'farm_profile_id' => $miGranja->id,
            'user_id' => $comprador->id,
            'total_amount' => 150.50,
            'status' => 'completado',
            'created_at' => now()->subDays(2), // Hace 2 días
        ]);

        // Pedido 2: Pendiente (Alerta) ⚠️
        Order::create([
            'farm_profile_id' => $miGranja->id,
            'user_id' => $comprador->id,
            'total_amount' => 320.00,
            'status' => 'pendiente',
            'created_at' => now()->subHours(5),
        ]);

        // Pedido 3: Otro Completado
        Order::create([
            'farm_profile_id' => $miGranja->id,
            'user_id' => $comprador->id,
            'total_amount' => 85.00,
            'status' => 'completado',
            'created_at' => now()->subDays(1),
        ]);
    }
}