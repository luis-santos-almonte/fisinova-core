<?php

namespace App\Http\Controllers;

use App\Services\TherapyAppointmentService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class TherapyAppointmentController extends Controller
{
    use ApiResponse;

    protected $therapyService;

    public function __construct(TherapyAppointmentService $therapyService)
    {
        $this->therapyService = $therapyService;
    }

    public function createTherapies(Request $request)
    {
        $validated = $request->validate([
            'consultation_appointment_id' => 'required|exists:appointments,id',
            'therapist_id' => 'required|exists:employees,id',
            'dates' => 'required|array',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        $appointments = $this->therapyService->createTherapyAppointments(
            $validated['consultation_appointment_id'],
            $validated
        );

        return $this->successResponse($appointments, 'Citas de terapia creadas exitosamente', 201);
    }

    public function completeSession(Request $request, int $appointmentId)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        $appointment = $this->therapyService->completeTherapySession($appointmentId, $validated);
        return $this->successResponse($appointment, 'Sesión completada exitosamente');
    }
}