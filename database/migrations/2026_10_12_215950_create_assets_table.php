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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            //columnas con relaciones a otra tablas 
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            //identificacion unica del producto
            $table->string('code');
            $table->string('serial_number')->nullable();

            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('location')->nullable();

            $table->enum('status', ['activo', 'mantenimiento', 'baja'])->default('activo');
            $table->date('purchase_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

            //no repetir el codigo y serial number por organizacion

            $table->unique(['code', 'organization_id']);
            $table->unique(['serial_number', 'organization_id']);


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
