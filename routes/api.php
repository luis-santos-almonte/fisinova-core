<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ProcedureController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\InsuranceController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\ScheduleTemplateController;
use App\Http\Controllers\StaffScheduleController;
use App\Http\Controllers\CubicleController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    
    // ========== GESTIÓN DE CONTRASEÑAS ==========
    Route::post('/check-password-reset', [UserController::class, 'checkPasswordReset']);
    Route::post('/change-password', [UserController::class, 'changePassword']);

    // ========== RECURSOS PRINCIPALES ==========
    Route::apiResource('patients', PatientController::class);
    Route::apiResource('appointments', AppointmentController::class);
    Route::apiResource('procedures', ProcedureController::class);
    Route::apiResource('employees', EmployeeController::class)->only(['index', 'show']);
    Route::apiResource('insurances', InsuranceController::class)->only(['index', 'show']);

    // ========== GESTIÓN DE PERSONAL Y HORARIOS ==========
    Route::apiResource('staff', StaffController::class);
    Route::apiResource('schedule-templates', ScheduleTemplateController::class);
    Route::apiResource('staff-schedules', StaffScheduleController::class);
    
    // ========== RECURSOS AUXILIARES ==========
    Route::apiResource('cubicles', CubicleController::class)->only(['index', 'show']);
    Route::apiResource('positions', PositionController::class)->only(['index', 'show']);
    
    // Ruta adicional para horario semanal
    Route::get('staff/{staffId}/weekly-schedule', [StaffScheduleController::class, 'weeklySchedule']);
    
    // ========== GESTIÓN DE USUARIOS ==========
    Route::apiResource('users', UserController::class);
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword']);
    
    // ========== ROLES Y EMPLEADOS DISPONIBLES ==========
    Route::get('roles', [RoleController::class, 'index']);
    Route::get('available-employees', [UserController::class, 'availableEmployees']);
});