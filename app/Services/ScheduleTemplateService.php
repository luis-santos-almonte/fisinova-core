<?php

namespace App\Services;

use App\Models\ScheduleTemplate;
use App\Models\ScheduleDay;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScheduleTemplateService
{
    public function getAllScheduleTemplates(array $filters = [])
    {
        $query = ScheduleTemplate::query();

        if (!empty($filters['search'])) {
            $query->where('name', 'ILIKE', "%{$filters['search']}%");
        }

        $pagination = $filters['paginate'] ?? 15;

        return $query->with(['scheduleDays'])
            ->orderBy('name')
            ->simplePaginate($pagination);
    }

    public function getScheduleTemplateById($id)
    {
        return ScheduleTemplate::with(['scheduleDays'])
            ->findOrFail($id);
    }

    public function createScheduleTemplate(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Log para debugging
            Log::info('Creating schedule template', ['data' => $data]);

            // Extraer los días del horario
            $scheduleDays = $data['schedule_days'] ?? [];
            
            // Crear la plantilla sin los días
            $templateData = [
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ];

            $scheduleTemplate = ScheduleTemplate::create($templateData);

            // Crear cada día de horario
            foreach ($scheduleDays as $dayData) {
                $scheduleTemplate->scheduleDays()->create([
                    'day_of_week' => $dayData['day_of_week'] ?? null,
                    'start_time' => $dayData['start_time'],
                    'end_time' => $dayData['end_time'],
                    'is_recurring' => $dayData['is_recurring'] ?? true,
                ]);
            }

            // Recargar con las relaciones
            return $scheduleTemplate->load('scheduleDays');
        });
    }

    public function updateScheduleTemplate($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $scheduleTemplate = ScheduleTemplate::findOrFail($id);

            // Actualizar datos básicos de la plantilla
            $templateData = [
                'name' => $data['name'] ?? $scheduleTemplate->name,
                'description' => $data['description'] ?? $scheduleTemplate->description,
            ];

            $scheduleTemplate->update($templateData);

            // Si se proporcionan nuevos días, reemplazarlos
            if (isset($data['schedule_days'])) {
                // Eliminar días anteriores
                $scheduleTemplate->scheduleDays()->delete();

                // Crear los nuevos días
                foreach ($data['schedule_days'] as $dayData) {
                    $scheduleTemplate->scheduleDays()->create([
                        'day_of_week' => $dayData['day_of_week'] ?? null,
                        'start_time' => $dayData['start_time'],
                        'end_time' => $dayData['end_time'],
                        'is_recurring' => $dayData['is_recurring'] ?? true,
                    ]);
                }
            }

            return $scheduleTemplate->load('scheduleDays');
        });
    }

    public function deleteScheduleTemplate($id)
    {
        return DB::transaction(function () use ($id) {
            $scheduleTemplate = ScheduleTemplate::findOrFail($id);
            
            // Verificar si hay asignaciones de personal usando este template
            $hasAssignments = $scheduleTemplate->scheduleDays()
                ->whereHas('staffSchedules')
                ->exists();

            if ($hasAssignments) {
                throw new \Exception('No se puede eliminar la plantilla porque tiene asignaciones de personal activas.');
            }

            // Eliminar los días primero
            $scheduleTemplate->scheduleDays()->delete();
            
            // Eliminar la plantilla
            $scheduleTemplate->delete();
            
            return true;
        });
    }
}