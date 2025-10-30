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

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Recursos existentes
    Route::apiResource('patients', PatientController::class);
    Route::apiResource('appointments', AppointmentController::class);
    Route::apiResource('procedures', ProcedureController::class);
    Route::apiResource('employees', EmployeeController::class)->only(['index', 'show']);
    Route::apiResource('insurances', InsuranceController::class)->only(['index', 'show']);
    Route::apiResource('authorizations', AuthorizationController::class);
    Route::post('/appointments/{id}/confirm', [AuthorizationController::class, 'confirmAppointment']);

    // Nuevos recursos de gestión de horarios
    Route::apiResource('staff', StaffController::class);
    Route::apiResource('schedule-templates', ScheduleTemplateController::class);
    Route::apiResource('staff-schedules', StaffScheduleController::class);

    // Recursos auxiliares - CORREGIDO
    Route::apiResource('cubicles', CubicleController::class)->only(['index', 'show']);
    Route::apiResource('positions', PositionController::class)->only(['index', 'show']);

    // Ruta adicional para obtener horario semanal de un staff
    Route::get('staff/{staffId}/weekly-schedule', [StaffScheduleController::class, 'weeklySchedule']);
});
