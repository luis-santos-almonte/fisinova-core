<?php

namespace App\Http\Controllers;

use App\Http\Requests\Appointment\IndexAppointmentRequest;
use App\Http\Requests\Appointment\StoreAppointmentRequest;
use App\Http\Requests\Appointment\UpdateAppointmentRequest;
use App\Models\Appointment;
use App\Services\AppointmentService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    use ApiResponse;

    protected $appointmentService;

    public function __construct(AppointmentService $appointmentService)
    {
        $this->appointmentService = $appointmentService;
    }

    /**
     * Listar todas las citas con filtros
     */
    public function index(IndexAppointmentRequest $request)
    {
        $appointments = $this->appointmentService->getAllAppointments(
            $request->validated()
        );
        return $this->successResponse($appointments);
    }

    /**
     * Crear nueva cita
     */
    public function store(StoreAppointmentRequest $request)
    {
        $appointment = $this->appointmentService->createAppointment(
            $request->validated()
        );
        return $this->successResponse($appointment, 201);
    }

    /**
     * Mostrar cita específica
     */
    public function show(Appointment $appointment)
    {
        $appointment = $this->appointmentService->getAppointmentById($appointment->id);
        return $this->successResponse($appointment);
    }

    /**
     * Actualizar cita
     */
    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        $appointment = $this->appointmentService->updateAppointment(
            $appointment->id,
            $request->validated()
        );
        return $this->successResponse($appointment);
    }

    /**
     * Eliminar cita
     */
    public function destroy(Appointment $appointment)
    {
        $this->appointmentService->deleteAppointment($appointment->id);
        return $this->successResponse(null, 204);
    }

    /**
     * Obtener disponibilidad del doctor
     */
    public function getDoctorAvailability(Request $request, int $doctorId)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'duration' => 'nullable|integer|min:15|max:240',
        ]);

        $availability = $this->appointmentService->getDoctorAvailability(
            $doctorId,
            $validated['start_date'],
            $validated['end_date'],
            $validated['duration'] ?? 60
        );

        return $this->successResponse($availability);
    }

    /**
     * Validar slot de tiempo
     */
    public function validateTimeSlot(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|integer|exists:employees,id',
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'duration' => 'nullable|integer|min:15|max:240',
            'exclude_appointment_id' => 'nullable|integer|exists:appointments,id',
        ]);

        $validation = $this->appointmentService->validateTimeSlot(
            $validated['employee_id'],
            $validated['date'],
            $validated['time'],
            $validated['duration'] ?? 60,
            $validated['exclude_appointment_id'] ?? null
        );

        return $this->successResponse($validation);
    }

    /**
     * Obtener siguiente slot disponible
     */
    public function getNextAvailableSlot(Request $request, int $doctorId)
    {
        $validated = $request->validate([
            'from_date' => 'nullable|date',
            'duration' => 'nullable|integer|min:15|max:240',
        ]);

        $nextSlot = $this->appointmentService->getNextAvailableSlot(
            $doctorId,
            $validated['from_date'] ?? null,
            $validated['duration'] ?? 60
        );

        if (!$nextSlot) {
            return $this->errorResponse(
                'No hay slots disponibles en los próximos 30 días',
                'NO_SLOTS_AVAILABLE',
                404
            );
        }

        return $this->successResponse($nextSlot);
    }
}
