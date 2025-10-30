<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cubicle;

class CubicleSeeder extends Seeder
{
    public function run(): void
    {
        $cubicles = [
            [
                'code' => 'CUB-001',
                'name' => 'Consultorio 1',
                'location' => 'Planta Baja - Ala Este',
                'capacity' => 1,
                'features' => json_encode(['Camilla', 'Escritorio', 'Aire Acondicionado']),
                'is_active' => true,
            ],
            [
                'code' => 'CUB-002',
                'name' => 'Consultorio 2',
                'location' => 'Planta Baja - Ala Este',
                'capacity' => 1,
                'features' => json_encode(['Camilla', 'Escritorio', 'Aire Acondicionado']),
                'is_active' => true,
            ],
            [
                'code' => 'CUB-003',
                'name' => 'Sala de Fisioterapia 1',
                'location' => 'Planta Baja - Ala Oeste',
                'capacity' => 2,
                'features' => json_encode(['Camilla', 'Equipos de Rehabilitación', 'Aire Acondicionado']),
                'is_active' => true,
            ],
            [
                'code' => 'CUB-004',
                'name' => 'Sala de Fisioterapia 2',
                'location' => 'Planta Baja - Ala Oeste',
                'capacity' => 2,
                'features' => json_encode(['Camilla', 'Equipos de Rehabilitación', 'Aire Acondicionado']),
                'is_active' => true,
            ],
            [
                'code' => 'CUB-005',
                'name' => 'Consultorio de Emergencias',
                'location' => 'Planta Baja - Entrada Principal',
                'capacity' => 1,
                'features' => json_encode(['Camilla', 'Equipo de Emergencia', 'Oxígeno']),
                'is_active' => true,
            ],
        ];

        foreach ($cubicles as $cubicle) {
            Cubicle::updateOrCreate(
                ['code' => $cubicle['code']],
                $cubicle
            );
        }

        echo "✅ Cubículos creados exitosamente\n";
    }
}