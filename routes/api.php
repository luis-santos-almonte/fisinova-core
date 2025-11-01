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
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\MedicalRecordController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Recursos de pacientes
    Route::apiResource('patients', PatientController::class);

    // Recursos de citas
    Route::apiResource('appointments', AppointmentController::class);

    // Confirmar llegada del paciente (SECRETARIA)
    Route::post('/appointments/{id}/confirm', [AuthorizationController::class, 'confirmAppointment']);

    // Recursos de procedimientos
    Route::apiResource('procedures', ProcedureController::class);

    // Recursos de empleados y seguros
    Route::apiResource('employees', EmployeeController::class)->only(['index', 'show']);
    Route::apiResource('insurances', InsuranceController::class)->only(['index', 'show']);

    // Recursos de autorizaciones
    Route::apiResource('authorizations', AuthorizationController::class);

    // Gestión de personal y horarios
    Route::apiResource('staff', StaffController::class);
    Route::apiResource('schedule-templates', ScheduleTemplateController::class);
    Route::apiResource('staff-schedules', StaffScheduleController::class);

    // Recursos auxiliares
    Route::apiResource('positions', PositionController::class)->only(['index', 'show']);

    // Horario semanal de un staff
    Route::get('staff/{staffId}/weekly-schedule', [StaffScheduleController::class, 'weeklySchedule']);

    // Diagnósticos y Procedimientos Estándar
    Route::get('diagnostic-standards', [DiagnosticStandardController::class, 'index']);
    Route::get('diagnostic-standards/{diagnosticStandard}', [DiagnosticStandardController::class, 'show']);

    Route::get('procedure-standards', [ProcedureStandardController::class, 'index']);
    Route::get('procedure-standards/{procedureStandard}', [ProcedureStandardController::class, 'show']);

    // ✅ NUEVO: Consultas médicas
    Route::prefix('consultations')->group(function () {
        Route::get('/dashboard-stats', [ConsultationController::class, 'getDashboardStats']);
        Route::post('/{appointmentId}/start', [ConsultationController::class, 'startConsultation']);
        Route::post('/{appointmentId}/complete', [ConsultationController::class, 'completeConsultation']);
    });

    // ✅ NUEVO: Registros médicos
    Route::prefix('medical-records')->group(function () {
        Route::post('/', [MedicalRecordController::class, 'store']);
        Route::put('/{medicalRecord}', [MedicalRecordController::class, 'update']);
        Route::get('/appointment/{appointmentId}', [MedicalRecordController::class, 'getByAppointment']);
        Route::get('/patient/{patientId}', [MedicalRecordController::class, 'getPatientHistory']);
    });

    // ✅ NUEVO: Mis citas (para médicos/terapistas)
    Route::get('/my-appointments', [ConsultationController::class, 'getMyAppointments']);
});
