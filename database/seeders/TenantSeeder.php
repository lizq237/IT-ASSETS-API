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
        /* Organización de prueba 1 */
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

        /* Organización de prueba 2 */
        $org2 = Organization::create([
            'company_name' => 'Facultad de Informática Culiacán',
        ]);

        /* Organización de prueba 3 */
        $org3 = Organization::create([
            'company_name' => 'Facultad de Medicina Culiacán',
        ]);

        User::create([
            'name' => 'alejandro mellado',
            'email' => 'alemellado@gmail.com',
            'password' => Hash::make('password123'),
            'organization_id' => $org3->id,
            'role' => 'admin_ti',
        ]);
    }
}