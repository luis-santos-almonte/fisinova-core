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
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\DiagnosticStandardController;
use App\Http\Controllers\ProcedureStandardController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\MedicalRecordController;
use App\Http\Controllers\TherapyController;
use App\Services\AppointmentService;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {

    // ========== AUTENTICACIÓN ==========
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // ========== GESTIÓN DE CONTRASEÑAS ==========
    Route::post('/check-password-reset', [UserController::class, 'checkPasswordReset']);
    Route::post('/change-password', [UserController::class, 'changePassword']);

    // ========== RECURSOS PRINCIPALES ==========
    Route::apiResource('patients', PatientController::class);
    Route::apiResource('appointments', AppointmentController::class);
    Route::apiResource('procedures', ProcedureController::class);

    // Confirmar llegada del paciente (SECRETARIA)
    Route::post('/appointments/{id}/confirm', [AuthorizationController::class, 'confirmAppointment']);

    // Recursos de empleados y seguros
    Route::apiResource('employees', EmployeeController::class)->only(['index', 'show']);
    Route::apiResource('insurances', InsuranceController::class)->only(['index', 'show']);

    // Recursos de autorizaciones
    Route::apiResource('authorizations', AuthorizationController::class);
    Route::post('/authorizations/{appointmentId}/authorize-therapy', [AuthorizationController::class, 'authorizeTherapy']);

    // ========== GESTIÓN DE PERSONAL Y HORARIOS ==========
    Route::apiResource('staff', StaffController::class);
    Route::apiResource('schedule-templates', ScheduleTemplateController::class);
    Route::apiResource('staff-schedules', StaffScheduleController::class);

    // Horario semanal de un staff
    Route::get('staff/{staffId}/weekly-schedule', [StaffScheduleController::class, 'weeklySchedule']);

    // ========== RECURSOS AUXILIARES ==========
    Route::apiResource('cubicles', CubicleController::class)->only(['index', 'show']);
    Route::apiResource('positions', PositionController::class)->only(['index', 'show']);

    // ========== CONSULTAS MÉDICAS ==========
    Route::prefix('consultations')->group(function () {
        Route::get('/dashboard-stats', [ConsultationController::class, 'getDashboardStats']);
        Route::post('/{appointmentId}/start', [ConsultationController::class, 'startConsultation']);
        Route::post('/{appointmentId}/complete', [ConsultationController::class, 'completeConsultation']);
    });

    // Mis citas (para médicos/terapistas)
    Route::get('/my-appointments', [ConsultationController::class, 'getMyAppointments']);

    // ========== REGISTROS MÉDICOS ==========
    Route::prefix('medical-records')->group(function () {
        Route::post('/', [MedicalRecordController::class, 'store']);
        Route::put('/{medicalRecord}', [MedicalRecordController::class, 'update']);
        Route::get('/appointment/{appointmentId}', [MedicalRecordController::class, 'getByAppointment']);
        Route::get('/patient/{patientId}/history', [MedicalRecordController::class, 'getPatientHistory']);
    });

    // ========== ESTÁNDARES MÉDICOS ==========
    Route::get('diagnostic-standards', [DiagnosticStandardController::class, 'index']);
    Route::get('diagnostic-standards/{diagnosticStandard}', [DiagnosticStandardController::class, 'show']);
    Route::get('procedure-standards', [ProcedureStandardController::class, 'index']);
    Route::get('procedure-standards/{procedureStandard}', [ProcedureStandardController::class, 'show']);

    // ========== GESTIÓN DE USUARIOS Y ROLES ==========
    Route::apiResource('users', UserController::class);
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword']);
    Route::get('roles', [RoleController::class, 'index']);
    Route::get('available-employees', [UserController::class, 'availableEmployees']);

    Route::get('/therapies/my-therapies', [TherapyController::class, 'getMyTherapies']);
    Route::get('/therapies/{appointment}', [TherapyController::class, 'getSession']);
    Route::post('/therapies/{appointment}/start', [TherapyController::class, 'startSession']);
    Route::post('/therapies/{appointment}/complete', [TherapyController::class, 'completeSession']);

    // ========== APPOINTMENTS (CITAS) ==========
    Route::prefix('appointments')->group(function () {
        // Rutas de disponibilidad (deben ir ANTES de las rutas de recurso)
        Route::get('availability/{doctorId}', [AppointmentController::class, 'getDoctorAvailability']);
        Route::post('validate-slot', [AppointmentController::class, 'validateTimeSlot']);
        Route::get('next-available/{doctorId}', [AppointmentController::class, 'getNextAvailableSlot']);
    });

    // Rutas de recurso estándar
    Route::apiResource('appointments', AppointmentController::class);
});
