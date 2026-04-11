<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Verificamos si el usuario está autenticado y si su rol es 'admin'
        if (!auth()->check() || !auth()->user()->isAdmin()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Acceso denegado. Se requieren privilegios de Administrador.'
            ], 403); // 403 significa "Prohibido"
        }

        return $next($request);
    }
}