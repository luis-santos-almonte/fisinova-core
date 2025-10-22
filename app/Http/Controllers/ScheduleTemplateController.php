<?php

namespace App\Http\Controllers;

// use App\Http\Requests\ScheduleTemplate\StoreScheduleTemplateRequest;
// use App\Http\Requests\ScheduleTemplate\UpdateScheduleTemplateRequest;
// use App\Http\Requests\ScheduleTemplate\IndexScheduleTemplateRequest;
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
        echo "pase por aqui 0";
    }

    public function index(IndexScheduleTemplateRequest $request)
    {
        $scheduleTemplates = $this->scheduleTemplateService->getAllScheduleTemplates($request->validated());
        echo "pase por aqui 1";
        return $this->successResponse($scheduleTemplates);
    }

    public function store(StoreScheduleTemplateRequest $request)
    {
        $scheduleTemplate = $this->scheduleTemplateService->createScheduleTemplate($request->validated());
        echo "pase por aqui 2";
        return $this->successResponse($scheduleTemplate, 201);
    }
    
    public function show(ScheduleTemplate $scheduleTemplate)
    {
        $scheduleTemplate = $this->scheduleTemplateService->getScheduleTemplateById($scheduleTemplate->id);
        echo "pase por aqui 3";
        return $this->successResponse($scheduleTemplate);
    }

    public function update(UpdateScheduleTemplateRequest $request, ScheduleTemplate $scheduleTemplate)
    {
        $scheduleTemplate = $this->scheduleTemplateService->updateScheduleTemplate($scheduleTemplate->id, $request->validated());
        return $this->successResponse($scheduleTemplate);
    }

    public function destroy(ScheduleTemplate $scheduleTemplate)
    {
        $this->scheduleTemplateService->deleteScheduleTemplate($scheduleTemplate->id);
        return $this->successResponse(null, 204);
    }
}