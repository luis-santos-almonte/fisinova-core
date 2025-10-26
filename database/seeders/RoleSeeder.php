<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'admin', 'description' => 'Administrador', 'active' => true],
            ['name' => 'medic', 'description' => 'Medico', 'active' => true],
            ['name' => 'therapist', 'description' => 'Terapista', 'active' => true],
            ['name' => 'secretary', 'description' => 'Secretaria', 'active' => true], // ✅ NUEVO
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role['name']], $role);
        }
    }
}