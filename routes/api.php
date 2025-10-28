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

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::apiResource('patients', PatientController::class);
    Route::apiResource('appointments', AppointmentController::class);
    Route::apiResource('procedures', ProcedureController::class);
    Route::apiResource('employees', EmployeeController::class)->only(['index', 'show']);
    Route::apiResource('insurances', InsuranceController::class)->only(['index', 'show']);
    Route::apiResource('authorizations', AuthorizationController::class);
    Route::post('/appointments/{id}/confirm', [AuthorizationController::class, 'confirmAppointment']);
});
