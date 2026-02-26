<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;

class ForgotPasswordController extends Controller
{
    // 1. SOLICITAR EL LINK (Forgot Password)
    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Verificar si el usuario existe
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // Por seguridad, no decimos si el correo existe o no, pero retornamos éxito simulado o error genérico.
            // Para desarrollo, retornaremos 404 para que sepas qué pasa.
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        // Generar Token Único
        $token = Str::random(64);

        // Guardar Token en la tabla password_reset_tokens
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email], // Busca por email
            [
                'email' => $request->email,
                'token' => $token,
                'created_at' => Carbon::now()
            ]
        );

        // Generar la URL del Frontend (Vue)
        // IMPORTANTE: Asegúrate de que esta URL coincida con tu puerto de Vue (5173 normalmente)
        $url = "http://localhost:5173/reset-password?token=" . $token . "&email=" . $request->email;

        // Enviar el correo
        try {
            Mail::send('emails.password_reset', ['url' => $url, 'email' => $request->email], function ($message) use ($request) {
                $message->to($request->email);
                $message->subject('Recuperación de Contraseña - AgroMarket');
            });

            return response()->json(['message' => 'Correo enviado correctamente']);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al enviar el correo: ' . $e->getMessage()], 500);
        }
    }

    // 2. CAMBIAR LA CONTRASEÑA (Reset Password)
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|min:8|confirmed', // 'confirmed' busca password_confirmation automáticamente
        ]);

        // Verificar si el token es válido en la base de datos
        $resetRecord = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$resetRecord) {
            return response()->json(['message' => 'El token es inválido o ha expirado'], 400);
        }

        // Verificar expiración (ej: 60 minutos)
        if (Carbon::parse($resetRecord->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json(['message' => 'El token ha expirado'], 400);
        }

        // Actualizar la contraseña del usuario
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        // Borrar el token para que no se pueda usar de nuevo
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Contraseña actualizada correctamente']);
    }
}