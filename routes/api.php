<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\FarmProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ContactController;

// --------------------------------------------------------------------------
// Rutas Públicas (Cualquiera entra)
// --------------------------------------------------------------------------

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/market-products', [ProductController::class, 'publicList']);
Route::get('/market-sellers', [FarmProfileController::class, 'publicList']);
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword']);
Route::post('/contact', [ContactController::class, 'store']);

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

    Route::get('/products', [ProductController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);

    // Categorias
    Route::get('/categories', [CategoryController::class, 'index']);

    // Rutas de PEDIDOS
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/farmer/sales', [OrderController::class, 'indexFarmer']);
    Route::get('/orders', [OrderController::class, 'index']); // Historial Comprador

    // --- INTEGRACIÓN MERCADO PAGO ---
    # Route::post('/create-preference', [PaymentController::class, 'createPreference']);

    // --- INTEGRACIÓN CULQI ---
    Route::post('/process-culqi-payment', [PaymentController::class, 'processCulqiCharge']);
});

// --------------------------------------------------------------------------
// Rutas de Administrador (Super Admin)
// --------------------------------------------------------------------------
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/admin/messages', [ContactController::class, 'index']);
    Route::patch('/admin/messages/{id}/read', [ContactController::class, 'markAsRead']);
    // Route::get('/admin/users', [AdminController::class, 'getAllUsers']);
    // Route::delete('/admin/products/{id}', [AdminController::class, 'forceDeleteProduct']);
});