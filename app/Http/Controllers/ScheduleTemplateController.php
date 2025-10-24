<?php

namespace App\Http\Controllers;

use App\Http\Requests\Staff\StoreScheduleTemplateRequest;
use App\Http\Requests\Staff\UpdateScheduleTemplateRequest;
use App\Http\Requests\Staff\IndexScheduleTemplateRequest;
use App\Models\ScheduleTemplate;
use App\Services\ScheduleTemplateService;
use App\Traits\ApiResponse;

class ScheduleTemplateController extends Controller
{
    use ApiResponse;

    protected $scheduleTemplateService;

    public function __construct(ScheduleTemplateService $scheduleTemplateService)
    {
        $this->scheduleTemplateService = $scheduleTemplateService;
    }

    /**
     * Obtiene la lista de plantillas de horarios con filtros
     */
    public function index(IndexScheduleTemplateRequest $request)
    {
        $scheduleTemplates = $this->scheduleTemplateService->getAllScheduleTemplates($request->validated());
        return $this->successResponse($scheduleTemplates);
    }

    /**
     * Crea una nueva plantilla de horario
     */
    public function store(StoreScheduleTemplateRequest $request)
    {
        try {
            $scheduleTemplate = $this->scheduleTemplateService->createScheduleTemplate($request->validated());
            return $this->successResponse($scheduleTemplate, 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'SCHEDULE_TEMPLATE_ERROR', 422);
        }
    }

    /**
     * Muestra los detalles de una plantilla de horario específica
     */
    public function show(ScheduleTemplate $scheduleTemplate)
    {
        $scheduleTemplate = $this->scheduleTemplateService->getScheduleTemplateById($scheduleTemplate->id);
        return $this->successResponse($scheduleTemplate);
    }

    /**
     * Actualiza una plantilla de horario existente
     */
    public function update(UpdateScheduleTemplateRequest $request, ScheduleTemplate $scheduleTemplate)
    {
        try {
            $scheduleTemplate = $this->scheduleTemplateService->updateScheduleTemplate($scheduleTemplate->id, $request->validated());
            return $this->successResponse($scheduleTemplate);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'SCHEDULE_TEMPLATE_ERROR', 422);
        }
    }

    /**
     * Elimina una plantilla de horario
     */
    public function destroy(ScheduleTemplate $scheduleTemplate)
    {
        try {
            $this->scheduleTemplateService->deleteScheduleTemplate($scheduleTemplate->id);
            return $this->successResponse(null, 204);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'SCHEDULE_TEMPLATE_ERROR', 422);
        }
    }
}