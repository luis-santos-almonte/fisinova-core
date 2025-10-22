<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PositionSeeder::class,
            InsuranceSeeder::class,
            ProcedureTypeSeeder::class,
            UserSeeder::class,
            PatientSeeder::class,
            CubicleSeeder::class,
            ScheduleTemplateSeeder::class,
        ]);
    }
}