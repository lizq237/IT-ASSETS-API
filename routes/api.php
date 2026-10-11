<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

//Rutas públicas

Route::post('/login', [AuthController::class, 'login'])->name('login');

//Rutas protegidas (requieren token Bearer)

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    //Categorías — LECTURA | Cualquier usuario autenticado (admin_ti, técnico, empleado)
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);

    //Categorías — ESCRITURA | Solo admin_ti
    Route::middleware('role:admin_ti')->group(function () {
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
    });

    // Organizaciones y alta de tenants
    // Solo super_admin (exclusivo, no admin_ti)
    Route::middleware('role:super_admin')->group(function () {
        Route::get('/organizaciones', [OrganizationController::class, 'index']);
        Route::post('/register-tenant', [TenantController::class, 'store']);
        Route::delete('/organizaciones/{organization}', [OrganizationController::class, 'destroy']);
    });

    //Usuarios y dashboard
    //Compartido: admin_ti y super_admin
    Route::middleware('role:admin_ti,super_admin')->group(function () {
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);

        Route::get('/dashboard/resumen', [DashboardController::class, 'resumen']);
    });
});