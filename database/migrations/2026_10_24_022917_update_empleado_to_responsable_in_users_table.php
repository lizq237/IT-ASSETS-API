<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration{
    public function up(): void{
        DB::table('users')->where('role', 'empleado')->update(['role' => 'responsable']);
    }
    public function down(): void{
        DB::table('users')->where('role', 'responsable')->update(['role' => 'empleado']);
    }
};
