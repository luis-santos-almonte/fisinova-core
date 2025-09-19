<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProcedureType;

class ProcedureTypeSeeder extends Seeder
{
    public function run(): void
    {
        $procedureTypes = [
            ['name' => 'Consulta General', 'description' => 'Consulta médica general', 'active' => true],
            ['name' => 'Fisioterapia', 'description' => 'Sesión de fisioterapia', 'active' => true],
            ['name' => 'Examen Médico', 'description' => 'Examen médico completo', 'active' => true],
            ['name' => 'Terapia Respiratoria', 'description' => 'Terapia respiratoria', 'active' => true],
            ['name' => 'Rehabilitación', 'description' => 'Sesión de rehabilitación', 'active' => true],
        ];

        foreach ($procedureTypes as $type) {
            ProcedureType::updateOrCreate(['name' => $type['name']], $type);
        }
    }
}