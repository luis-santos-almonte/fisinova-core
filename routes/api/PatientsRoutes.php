<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PatientController;

Route::prefix('patients')->name('patients.')->group(function () {
    Route::apiResource('/', PatientController::class);
    Route::get('active', [PatientController::class, 'active'])->name('active');
    Route::get('inactive', [PatientController::class, 'inactive'])->name('inactive');
    Route::get('by-date-range', [PatientController::class, 'byDateRange'])->name('byDateRange');
});
