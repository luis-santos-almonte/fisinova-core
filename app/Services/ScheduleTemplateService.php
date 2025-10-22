<?php

namespace App\Services;

use App\Models\ScheduleTemplate;
use App\Models\ScheduleDay;
use Illuminate\Support\Facades\DB;

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
            $scheduleDays = $data['schedule_days'];
            unset($data['schedule_days']);

            $scheduleTemplate = ScheduleTemplate::create($data);

            foreach ($scheduleDays as $dayData) {
                $scheduleTemplate->scheduleDays()->create($dayData);
            }

            return $scheduleTemplate->load('scheduleDays');
        });
    }

    public function updateScheduleTemplate($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $scheduleTemplate = ScheduleTemplate::findOrFail($id);

            if (isset($data['schedule_days'])) {
                $scheduleDays = $data['schedule_days'];
                unset($data['schedule_days']);

                // Actualizar los datos básicos de la plantilla
                $scheduleTemplate->update($data);

                // Eliminar días anteriores y crear los nuevos
                $scheduleTemplate->scheduleDays()->delete();

                foreach ($scheduleDays as $dayData) {
                    $scheduleTemplate->scheduleDays()->create($dayData);
                }
            } else {
                $scheduleTemplate->update($data);
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

            $scheduleTemplate->scheduleDays()->delete();
            $scheduleTemplate->delete();
            
            return true;
        });
    }
}