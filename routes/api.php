<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ProcedureController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\InsuranceController;
use App\Http\Controllers\AuthorizationController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\ScheduleTemplateController;
use App\Http\Controllers\StaffScheduleController;
use App\Http\Controllers\CubicleController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\DiagnosticStandardController;
use App\Http\Controllers\ProcedureStandardController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Recursos de pacientes
    Route::apiResource('patients', PatientController::class);

    // Recursos de citas
    Route::apiResource('appointments', AppointmentController::class);

    // ✅ NUEVO: Completar consulta médica (MÉDICO)
    Route::post('/appointments/{id}/complete-consultation', [AppointmentController::class, 'completeConsultation']);

    // Confirmar llegada del paciente (SECRETARIA)
    Route::post('/appointments/{id}/confirm', [AuthorizationController::class, 'confirmAppointment']);

    // Recursos de procedimientos
    Route::apiResource('procedures', ProcedureController::class);

    // Recursos de empleados y seguros
    Route::apiResource('employees', EmployeeController::class)->only(['index', 'show']);
    Route::apiResource('insurances', InsuranceController::class)->only(['index', 'show']);

    // Recursos de autorizaciones
    Route::apiResource('authorizations', AuthorizationController::class);

    // ✅ NUEVO: Generar citas de terapia automáticamente (SECRETARIA)
    Route::post(
        '/authorizations/{authorization}/generate-therapy-appointments',
        [AuthorizationController::class, 'generateTherapyAppointments']
    );

    // Gestión de personal y horarios
    Route::apiResource('staff', StaffController::class);
    Route::apiResource('schedule-templates', ScheduleTemplateController::class);
    Route::apiResource('staff-schedules', StaffScheduleController::class);

    // Recursos auxiliares
    // Route::apiResource('cubicles', CubicleController::class)->only(['index', 'show']);
    Route::apiResource('positions', PositionController::class)->only(['index', 'show']);

    // Horario semanal de un staff
    Route::get('staff/{staffId}/weekly-schedule', [StaffScheduleController::class, 'weeklySchedule']);

    // Diagnósticos y Procedimientos Estándar
    Route::get('diagnostic-standards', [DiagnosticStandardController::class, 'index']);
    Route::get('diagnostic-standards/{diagnosticStandard}', [DiagnosticStandardController::class, 'show']);

    Route::get('procedure-standards', [ProcedureStandardController::class, 'index']);
    Route::get('procedure-standards/{procedureStandard}', [ProcedureStandardController::class, 'show']);
});
