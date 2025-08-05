<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PatientController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'web'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('patients', PatientController::class);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
