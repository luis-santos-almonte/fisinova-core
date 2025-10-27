<?php

namespace App\Http\Controllers;

use App\Http\Requests\Staff\StoreStaffScheduleRequest;
use App\Http\Requests\Staff\UpdateStaffScheduleRequest;
use App\Http\Requests\Staff\IndexStaffScheduleRequest;
use App\Models\StaffSchedule;
use App\Services\StaffScheduleService;
use App\Traits\ApiResponse;

class StaffScheduleController extends Controller
{
    use ApiResponse;

    protected $staffScheduleService;

    public function __construct(StaffScheduleService $staffScheduleService)
    {
        $this->staffScheduleService = $staffScheduleService;
    }

    /**
     * Obtiene la lista de asignaciones de horarios con filtros
     */
    public function index(IndexStaffScheduleRequest $request)
    {
        $staffSchedules = $this->staffScheduleService->getAllStaffSchedules($request->validated());
        return $this->successResponse($staffSchedules);
    }

    /**
     * Crea una nueva asignación de horario para el personal
     */
    public function store(StoreStaffScheduleRequest $request)
    {
        try {
            $staffSchedule = $this->staffScheduleService->createStaffSchedule($request->validated());
            return $this->successResponse($staffSchedule, 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'SCHEDULE_CONFLICT', 422);
        }
    }

    /**
     * Muestra los detalles de una asignación de horario específica
     */
    public function show(StaffSchedule $staffSchedule)
    {
        $staffSchedule = $this->staffScheduleService->getStaffScheduleById($staffSchedule->id);
        return $this->successResponse($staffSchedule);
    }

    /**
     * Actualiza una asignación de horario existente
     */
    public function update(UpdateStaffScheduleRequest $request, StaffSchedule $staffSchedule)
    {
        try {
            $updated = $this->staffScheduleService->updateStaffSchedule($staffSchedule->id, $request->validated());
            return $this->successResponse($updated);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'SCHEDULE_CONFLICT', 422);
        }
    }

    /**
     * Elimina una asignación de horario
     */
    public function destroy(StaffSchedule $staffSchedule)
    {
        $this->staffScheduleService->deleteStaffSchedule($staffSchedule->id);
        return $this->successResponse(null, 204);
    }

    /**
     * Obtiene el horario semanal de un personal específico
     * 
     * @param int $staffId ID del personal
     * @return \Illuminate\Http\JsonResponse
     */
    public function weeklySchedule($staffId)
    {
        $startDate = request('start_date', now()->startOfWeek()->format('Y-m-d'));
        $schedule = $this->staffScheduleService->getWeeklyScheduleForStaff($staffId, $startDate);
        return $this->successResponse($schedule);
    }
}