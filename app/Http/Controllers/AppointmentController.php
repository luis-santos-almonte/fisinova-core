<?php

namespace App\Http\Controllers;

use App\Http\Requests\Appointment\IndexAppointmentRequest;
use App\Http\Requests\Appointment\StoreAppointmentRequest;
use App\Http\Requests\Appointment\UpdateAppointmentRequest;
use App\Models\Appointment;
use App\Services\AppointmentService;
use App\Traits\ApiResponse;

class AppointmentController extends Controller
{
    use ApiResponse;

    protected $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    public function index(IndexAppointmentRequest $request)
    {
        $appointments = $this->appointmentService->getAllAppointments($request->validated());
        return $this->successResponse($appointments);
    }

    public function store(StoreAppointmentRequest $request)
    {
        $appointment = $this->appointmentService->createAppointment($request->validated());
        return $this->successResponse($appointment, 201);
    }

    public function show(Appointment $appointment)
    {
        $appointment = $this->appointmentService->getAppointmentById($appointment->id);
        return $this->successResponse($appointment);
    }

    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        $appointment = $this->appointmentService->updateAppointment($appointment->id, $request->validated());
        return $this->successResponse($appointment);
    }

    public function destroy(Appointment $appointment)
    {
        $this->appointmentService->deleteAppointment($appointment->id);
        return $this->successResponse(null, 204);
    }
}
