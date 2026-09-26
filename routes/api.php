<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\DashboardController;

/*Rutas públicas*/

Route::post('/login', [AuthController::class, 'login']);

/*Rutas protegidas (requieren token Bearer)*/

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/dashboard/resumen', [DashboardController::class, 'resumen']);
    /*Solo super_admin*/
    Route::middleware('role:super_admin')->group(function () {
        Route::post('/register-tenant', [TenantController::class, 'registerTenant']);
        Route::get('/organizaciones', [OrganizationController::class, 'index']);
        Route::delete('/organizaciones/{organization}', [OrganizationController::class, 'destroy']);
    });
});