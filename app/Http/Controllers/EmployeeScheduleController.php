<?php

namespace App\Http\Controllers;

use App\Services\EmployeeScheduleService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class EmployeeScheduleController extends Controller
{
    use ApiResponse;

    protected $scheduleService;

    public function __construct(EmployeeScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    public function index(Request $request)
    {
        $schedules = $this->scheduleService->getAllSchedules($request->all());
        return $this->successResponse($schedules);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'schedule_template_id' => 'required|exists:schedule_templates,id',
            'cubicle_id' => 'nullable|exists:cubicles,id',
        ]);

        $schedule = $this->scheduleService->createSchedule($validated);
        return $this->successResponse($schedule, 201, 'Horario asignado exitosamente'); // ✅ CORRECTO
    }

    public function update(Request $request, int $id)
    {
        $schedule = $this->scheduleService->updateSchedule($id, $request->all());
        return $this->successResponse($schedule, 200, 'Horario actualizado exitosamente'); // ✅ CORRECTO
    }

    public function destroy(int $id)
    {
        $this->scheduleService->deleteSchedule($id);
        return $this->successResponse(null, 200, 'Horario eliminado exitosamente'); // ✅ CORRECTO
    }
}