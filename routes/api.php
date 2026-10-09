<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\TenantController; 
use App\Http\Controllers\OrganizationController; 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::middleware('role:admin_ti,super_admin')->group(function () {
    
        Route::get('/organizaciones', [OrganizationController::class, 'index']);
        Route::post('/register-tenant', [TenantController::class, 'store']);
        
        
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);

        Route::get('/dashboard/resumen', [DashboardController::class, 'resumen']);
    });
});