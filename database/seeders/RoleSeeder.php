<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;


class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'description' => 'Administrador del sistema - Acceso total',
                'active' => true
            ],
            [
                'name' => 'medic',
                'description' => 'Médico - Gestión de consultas y expedientes',
                'active' => true
            ],
            [
                'name' => 'therapist',
                'description' => 'Terapista - Gestión de terapias y rehabilitación',
                'active' => true
            ],
            [
                'name' => 'secretary',
                'description' => 'Secretaria - Gestión de citas y pacientes',
                'active' => true
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                $role
            );
        }

        $this->command->info('✅ Roles creados: admin, medic, therapist, secretary');
    }
}
