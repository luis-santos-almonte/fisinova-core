<?php
// ==============================================================================
// DATABASE SEEDER - Orden correcto de ejecución
// ==============================================================================
// database/seeders/DatabaseSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Iniciando seeders...');

        $this->call([
            // 1. Tablas base (sin dependencias)
            RoleSeeder::class,
            PositionSeeder::class,
            InsuranceSeeder::class,
            ProcedureTypeSeeder::class,

            // 2. Usuarios y empleados (dependen de roles y positions)
            UserSeeder::class,

            // 3. Pacientes (independiente)
            PatientSeeder::class,

            // 4. Infraestructura
            CubicleSeeder::class,
            ScheduleTemplateSeeder::class,

            // 5. Catálogos médicos (CSV)
            DiagnosesSeeder::class,
            ProceduresSeeder::class,

        ]);

        $this->command->info('✅ Seeders completados exitosamente!');
    }
}

// ==============================================================================
// ROLE SEEDER
// ==============================================================================
// database/seeders/RoleSeeder.php
