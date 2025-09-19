<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Position;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            ['name' => 'Médico', 'description' => 'Médico', 'active' => true],
            ['name' => 'Terapista', 'description' => 'Especialista en fisioterapia', 'active' => true],
            ['name' => 'Secretaria', 'description' => 'Personal de recepción', 'active' => true],
            ['name' => 'Administrador', 'description' => 'Administrador del sistema', 'active' => true],
        ];

        foreach ($positions as $position) {
            Position::updateOrCreate(['name' => $position['name']], $position);
        }
    }
}