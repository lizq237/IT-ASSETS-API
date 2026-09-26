<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        /*
        | --Super Administrador global--
        | Credenciales de desarrollo
        |   email:    super@sysbase.com
        |   password: password123
        */
        User::create([
            'name' => 'Super Admin',
            'email' => 'super@sysbase.com',
            'password' => Hash::make('password123'),
            'organization_id' => null,
            'role' => 'super_admin',
        ]);

        /*Organización de prueba 1*/
        $org1 = Organization::create([
            'company_name' => 'FC Barcelona',
        ]);

        User::create([
            'name' => 'Admin Barça',
            'email' => 'admin@barca.com',
            'password' => Hash::make('password123'),
            'organization_id' => $org1->id,
            'role' => 'admin_ti',
        ]);

        /*Organización de prueba 2*/
        $org2 = Organization::create([
            'company_name' => 'Facultad de Informática Culiacán',
        ]);

        User::create([
            'name' => 'Admin FIC',
            'email' => 'admin@fic.edu.mx',
            'password' => Hash::make('password123'),
            'organization_id' => $org2->id,
            'role' => 'admin_ti',
        ]);
    }
}