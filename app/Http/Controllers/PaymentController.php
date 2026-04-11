<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function processCulqiCharge(Request $request)
    {
        try {
            // 1. Validar que Vue nos envíe el token de la tarjeta y el monto
            $request->validate([
                'token' => 'required|string',
                'amount' => 'required|numeric',
                'email' => 'required|email'
            ]);

            // 2. Culqi requiere que el monto se envíe en céntimos (ej. S/ 10.50 -> 1050)
            $amountInCents = (int) round($request->amount * 100);

            // 3. Hacer la petición de cobro (Cargo) a la API de Culqi
            $response = Http::withToken(env('CULQI_SECRET_KEY'))
                ->post('https://api.culqi.com/v2/charges', [
                    'amount' => $amountInCents,
                    'currency_code' => 'PEN',
                    'email' => $request->email,
                    'source_id' => $request->token,
                    'antifraud_details' => [
                        'first_name' => 'Cliente', // Aquí podrías pasar el nombre real
                        'last_name' => 'AgroMarket',
                        'phone_number' => '999999999'
                    ]
                ]);

            $result = $response->json();

            // 4. Evaluar la respuesta de Culqi
            if ($response->successful()) {
                // El cobro fue exitoso
                return response()->json([
                    'status' => 'success',
                    'charge_id' => $result['id'],
                    'message' => 'Pago procesado correctamente'
                ]);
            } else {
                // Culqi rechazó el pago (fondos insuficientes, tarjeta inválida, etc.)
                Log::error('Culqi Charge Error: ', $result);
                return response()->json([
                    'status' => 'error',
                    'message' => $result['user_message'] ?? 'El pago fue rechazado por el procesador.'
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Excepción en Culqi: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Ocurrió un error interno en el servidor.'
            ], 500);
        }
    }
}