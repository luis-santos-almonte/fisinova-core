<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Position;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            ['name' => 'Médico General', 'description' => 'Médico de medicina general', 'active' => true],
            ['name' => 'Fisioterapeuta', 'description' => 'Especialista en fisioterapia', 'active' => true],
            ['name' => 'Terapeuta Respiratorio', 'description' => 'Especialista en terapia respiratoria', 'active' => true],
            ['name' => 'Recepcionista', 'description' => 'Personal de recepción', 'active' => true],
            ['name' => 'Administrador', 'description' => 'Administrador del sistema', 'active' => true],
        ];

        foreach ($positions as $position) {
            Position::updateOrCreate(['name' => $position['name']], $position);
        }
    }
}