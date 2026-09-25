<?php

namespace Database\Seeders;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Soporte Sysbase', //nombre de la empresa
            'email' => 'superadmin007@sysbase.com', // correo electronico del super admin
            'password' => Hash::make('SuperAdmin123'), // contraseña
            'role' => 'super_admin', //rol que tenemos
            'organization_id' => null, // vacio 
        ]);
    }
}
