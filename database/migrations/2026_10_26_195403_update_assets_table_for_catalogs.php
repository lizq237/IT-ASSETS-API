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
    Schema::table('assets', function (Blueprint $table) {
        $table->dropColumn(['brand', 'location']);
        $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
        $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete();
    });
}

    public function down(): void
{
    
    Schema::table('assets', function (Blueprint $table) {
        $table->dropForeign(['brand_id']);
        $table->dropForeign(['location_id']);
        $table->dropColumn(['brand_id', 'location_id']);
        $table->string('brand')->nullable();
        $table->string('location')->nullable();
    });
}
};
