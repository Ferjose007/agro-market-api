<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\FarmProfileController;

// --------------------------------------------------------------------------
// Rutas Públicas (No requieren Token)
// --------------------------------------------------------------------------

// Autenticación
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Productos (Cualquiera puede ver qué se vende)
Route::get('/products', [ProductController::class, 'index']);


// --------------------------------------------------------------------------
// Rutas Protegidas (Requieren Token de Acceso)
// --------------------------------------------------------------------------
Route::middleware('auth:sanctum')->group(function () {

    // Cerrar sesión
    Route::post('/logout', [AuthController::class, 'logout']);

    // Usuario actual
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // --- Módulo Agricultor ---
    // Actualizar perfil de granja
    Route::post('/farm-profile', [FarmProfileController::class, 'update']);

    // Publicar producto nuevo
    Route::post('/products', [ProductController::class, 'store']);
});