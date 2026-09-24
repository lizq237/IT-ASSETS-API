<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            //Campos de la tabla de Organizations
            $table->id(); // Primary key, el id de la empresa
            $table->string('company_name', 255); // Nombre de la empresa
            $table->string('RFC', 13)->unique()->nullable(); //El RFC de la empresa
            $table->timestamps();// Fecha de creación

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
