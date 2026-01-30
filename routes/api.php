<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\FarmProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;

// --------------------------------------------------------------------------
// Rutas Públicas (Cualquiera entra)
// --------------------------------------------------------------------------

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/market-products', [ProductController::class, 'publicList']);
Route::get('/market-sellers', [FarmProfileController::class, 'publicList']);

// --------------------------------------------------------------------------
// Rutas Protegidas (Solo usuarios logueados)
// --------------------------------------------------------------------------
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::get('/dashboard-summary', [DashboardController::class, 'index']);
    Route::get('/farm-profile', [FarmProfileController::class, 'show']);
    Route::post('/farm-profile', [FarmProfileController::class, 'update']);

    // --- PRODUCTOS (Ahora todas están aquí adentro) ---

    // ✅ LA MOVIMOS AQUÍ: Ahora Laravel sabrá quién es el usuario
    Route::get('/products', [ProductController::class, 'index']);

    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // Categorias
    Route::get('/categories', [CategoryController::class, 'index']);
});