<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ScheduleTemplate;
use App\Models\ScheduleDay;

class ScheduleTemplateSeeder extends Seeder
{
    public function run(): void
    {
        // Plantilla 1: Turno Completo (Lunes a Viernes 8:00-17:00)
        $turnoCompleto = ScheduleTemplate::updateOrCreate(
            ['name' => 'Turno Completo'],
            [
                'name' => 'Turno Completo',
                'description' => 'Horario completo de Lunes a Viernes de 8:00 AM a 5:00 PM',
            ]
        );

        $diasSemana = [1, 2, 3, 4, 5]; // Lunes a Viernes
        foreach ($diasSemana as $dia) {
            ScheduleDay::updateOrCreate(
                [
                    'schedule_template_id' => $turnoCompleto->id,
                    'day_of_week' => $dia,
                ],
                [
                    'start_time' => '08:00',
                    'end_time' => '17:00',
                    'is_recurring' => true,
                ]
            );
        }

        // Plantilla 2: Medio Turno Mañana (Lunes a Viernes 8:00-12:00)
        $medioTurnoManana = ScheduleTemplate::updateOrCreate(
            ['name' => 'Medio Turno Mañana'],
            [
                'name' => 'Medio Turno Mañana',
                'description' => 'Horario matutino de Lunes a Viernes de 8:00 AM a 12:00 PM',
            ]
        );

        foreach ($diasSemana as $dia) {
            ScheduleDay::updateOrCreate(
                [
                    'schedule_template_id' => $medioTurnoManana->id,
                    'day_of_week' => $dia,
                ],
                [
                    'start_time' => '08:00',
                    'end_time' => '12:00',
                    'is_recurring' => true,
                ]
            );
        }

        // Plantilla 3: Medio Turno Tarde (Lunes a Viernes 13:00-17:00)
        $medioTurnoTarde = ScheduleTemplate::updateOrCreate(
            ['name' => 'Medio Turno Tarde'],
            [
                'name' => 'Medio Turno Tarde',
                'description' => 'Horario vespertino de Lunes a Viernes de 1:00 PM a 5:00 PM',
            ]
        );

        foreach ($diasSemana as $dia) {
            ScheduleDay::updateOrCreate(
                [
                    'schedule_template_id' => $medioTurnoTarde->id,
                    'day_of_week' => $dia,
                ],
                [
                    'start_time' => '13:00',
                    'end_time' => '17:00',
                    'is_recurring' => true,
                ]
            );
        }

        // Plantilla 4: Turno Extendido (Lunes a Sábado 7:00-19:00)
        $turnoExtendido = ScheduleTemplate::updateOrCreate(
            ['name' => 'Turno Extendido'],
            [
                'name' => 'Turno Extendido',
                'description' => 'Horario extendido de Lunes a Sábado de 7:00 AM a 7:00 PM',
            ]
        );

        $diasExtendidos = [1, 2, 3, 4, 5, 6]; // Lunes a Sábado
        foreach ($diasExtendidos as $dia) {
            ScheduleDay::updateOrCreate(
                [
                    'schedule_template_id' => $turnoExtendido->id,
                    'day_of_week' => $dia,
                ],
                [
                    'start_time' => '07:00',
                    'end_time' => '19:00',
                    'is_recurring' => true,
                ]
            );
        }

        echo "✅ Plantillas de horarios creadas exitosamente:\n";
        echo "   - Turno Completo (L-V 8:00-17:00)\n";
        echo "   - Medio Turno Mañana (L-V 8:00-12:00)\n";
        echo "   - Medio Turno Tarde (L-V 13:00-17:00)\n";
        echo "   - Turno Extendido (L-S 7:00-19:00)\n";
    }
}