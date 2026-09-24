<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        // Empresa 1
        $org1 = Organization::create([
            'company_name' => 'FC Barcelona'
        ]);

        User::create([
            'name' => 'Admin Barça',
            'email' => 'admin@barca.com',
            'password' => Hash::make('password123'),
            'organization_id' => $org1->id,
            'role' => 'Administrador de TI',
        ]);

        // Empresa 2
        $org2 = Organization::create([
            'company_name' => 'Facultad de Informática Culiacán'
        ]);

        User::create([
            'name' => 'Admin FIC',
            'email' => 'admin@fic.edu.mx',
            'password' => Hash::make('password123'),
            'organization_id' => $org2->id,
            'role' => 'Administrador de TI',
        ]);
    }
}