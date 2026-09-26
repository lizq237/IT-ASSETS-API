<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Hace nullable la columna organization_id en users, para permitir
     * usuarios globales (super_admin) que no pertenecen a ningún tenant.
     *
     * Estrategia: se elimina la FK, se cambia la columna, y se vuelve a crear
     * la FK preservando el comportamiento original (cascadeOnDelete).
     */
    public function up(): void
    {
        // 1. Eliminar FK existente (nombre autogenerado por Laravel)
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
        });

        // 2. Modificar la columna a nullable
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable()->change();
        });

        // 3. Re-crear FK con el mismo comportamiento
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('organization_id')
                  ->references('id')
                  ->on('organizations')
                  ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('organization_id')->nullable(false)->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('organization_id')
                  ->references('id')
                  ->on('organizations')
                  ->cascadeOnDelete();
        });
    }
};