<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration{
    public function up(): void{
        Schema::table('assets', function (Blueprint $table){
            // Renombramos la columna para seguir la convención de la tarea
            $table->renameColumn('user_id', 'assigned_user_id');
        });
    }

    public function down(): void{
        Schema::table('assets', function (Blueprint $table){
            $table->renameColumn('assigned_user_id', 'user_id');
        });
    }
};