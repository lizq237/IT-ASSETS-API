<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\LocationController;

//Rutas públicas

Route::post('/login', [AuthController::class, 'login'])->name('login');

//Rutas protegidas (requieren token Bearer)

Route::middleware('auth:sanctum')->group(function (){

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    //Catálogos (Categorías, Marcas, Ubicaciones) — LECTURA | Cualquier usuario autenticado
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/categories/{id}', [CategoryController::class, 'show']);

    Route::get('/brands', [BrandController::class, 'index']);
    Route::get('/brands/{id}', [BrandController::class, 'show']);

    Route::get('/locations', [LocationController::class, 'index']);
    Route::get('/locations/{id}', [LocationController::class, 'show']);


    //Categorías — ESCRITURA | Solo admin_ti
    Route::middleware('role:admin_ti')->group(function (){
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);

        Route::post('/brands', [BrandController::class, 'store']);
        Route::put('/brands/{id}', [BrandController::class, 'update']);
        Route::delete('/brands/{id}', [BrandController::class, 'destroy']);

        Route::post('/locations', [LocationController::class, 'store']);
        Route::put('/locations/{id}', [LocationController::class, 'update']);
        Route::delete('/locations/{id}', [LocationController::class, 'destroy']);
    });

    //Equipos (Assets) — LECTURA | Cualquier usuario autenticado (admin_ti, técnico, empleado)
    // HU-09 / Tarea #95: equipos asignados al usuario autenticado.
    Route::get('/employee/assets', [AssetController::class, 'employeeAssets']);

    Route::get('/assets', [AssetController::class, 'index']);
    Route::get('/assets/{id}', [AssetController::class, 'show']);

    //Equipos (Assets) — ESCRITURA | admin_ti
    Route::middleware('role:admin_ti')->group(function (){
        Route::post('/assets', [AssetController::class, 'store']);
        Route::put('/assets/{id}', [AssetController::class, 'update']);
        Route::delete('/assets/{id}', [AssetController::class, 'destroy']);
        Route::post('/assets/{id}/assign', [AssetController::class, 'assign']);
    });

    // Equipos (Assets) — ESCRITURA / GESTIÓN
    // El admin_ti puede todo. El técnico solo puede cambiar el estado de lo que tiene asignado.
    Route::middleware('role:admin_ti,tecnico')->group(function () {
        Route::patch('/assets/{id}/status', [AssetController::class, 'updateStatus']);
    });

    // Organizaciones y alta de tenants
    // Solo super_admin (exclusivo, no admin_ti)
    Route::middleware('role:super_admin')->group(function (){
        Route::get('/organizaciones', [OrganizationController::class, 'index']);
        Route::post('/register-tenant', [TenantController::class, 'store']);
        Route::delete('/organizaciones/{organization}', [OrganizationController::class, 'destroy']);
    });

    // Usuarios de la organización — solo admin_ti
    Route::middleware('role:admin_ti')->group(function (){
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
});

    Route::get('/dashboard/resumen', [DashboardController::class, 'resumen']);
});