<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ProcedureController;
use App\Http\Controllers\EmployeeController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'web'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('patients', PatientController::class);
    Route::apiResource('appointments', AppointmentController::class);
    Route::apiResource('procedures', ProcedureController::class);
    Route::apiResource('employees', EmployeeController::class)->only(['index', 'show']);
});
