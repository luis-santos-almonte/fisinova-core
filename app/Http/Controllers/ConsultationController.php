<?php

namespace App\Http\Controllers;

use App\Services\ConsultationService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ConsultationController extends Controller
{
    use ApiResponse;

    protected $consultationService;

    public function __construct(ConsultationService $consultationService)
    {
        $this->consultationService = $consultationService;
    }

    public function getDashboardStats(Request $request)
    {
        $stats = $this->consultationService->getDashboardStats($request->user()->id);
        return $this->successResponse($stats);
    }

    public function getMyAppointments(Request $request)
    {
        $status = $request->query('status');
        $appointments = $this->consultationService->getMyAppointments($request->user()->id, $status);
        return $this->successResponse($appointments);
    }

    public function startConsultation(Request $request, $appointmentId)
    {
        $appointment = $this->consultationService->startConsultation($appointmentId);
        return $this->successResponse($appointment);
    }

    public function completeConsultation(Request $request, $appointmentId)
    {
        $appointment = $this->consultationService->completeConsultation($appointmentId);
        return $this->successResponse($appointment);
    }
}