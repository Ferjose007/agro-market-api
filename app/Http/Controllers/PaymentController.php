<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Exceptions\MPApiException;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function createPreference(Request $request)
    {
        try {
            $token = env('MERCADOPAGO_ACCESS_TOKEN');
            if (empty($token)) {
                throw new \Exception("El Token de Mercado Pago no está configurado.");
            }

            MercadoPagoConfig::setAccessToken($token);

            $client = new PreferenceClient();
            $items = [];

            foreach ($request->items as $item) {
                $price = (float) $item['price'];
                if ($price <= 0) {
                    throw new \Exception("El producto '{$item['name']}' tiene un precio inválido.");
                }

                $items[] = [
                    "id" => (string) ($item['id'] ?? rand(1000, 9999)),
                    "title" => (string) $item['name'],
                    "quantity" => (int) ($item['quantity'] ?? 1),
                    "unit_price" => $price,
                    "currency_id" => "PEN"
                ];
            }

            $preference = $client->create([
                "items" => $items,
                "back_urls" => [
                    "success" => "http://localhost:5173/checkout/success",
                    "failure" => "http://localhost:5173/cart",
                    "pending" => "http://localhost:5173/cart"
                ],
                // "auto_return" => "approved", 
                "statement_descriptor" => "AGROMARKET"
            ]);

            return response()->json([
                'status' => 'success',
                'id' => $preference->id,
                'init_point' => $preference->sandbox_init_point
            ]);

        } catch (MPApiException $e) {
            $response = $e->getApiResponse();
            $errorDetails = $response ? $response->getContent() : 'Sin detalles';

            Log::error('MPApiException: ', ['detalles' => $errorDetails]);

            return response()->json([
                'status' => 'error',
                'message' => 'Mercado Pago rechazó los datos.',
                'mp_error' => $errorDetails
            ], 500);

        } catch (\Exception $e) {
            Log::error('Excepción general: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}