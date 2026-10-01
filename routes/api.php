<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Ruta de Login nombrada para evitar el error "Route [login] not defined"
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::middleware('role:admin_ti,super_admin')->group(function () {
        // Rutas para consultar la lista de usuarios / organizaciones
        Route::get('/users', [UserController::class, 'index']);
        Route::get('/organizaciones', [UserController::class, 'index']);
        
        // Ruta para registrar nuevos usuarios / empresas
        Route::post('/users', [UserController::class, 'store']);
    });
});