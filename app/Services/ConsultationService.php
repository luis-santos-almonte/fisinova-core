<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class ConsultationService
{
    public function getDashboardStats($userId)
    {
        $employee = Employee::where('user_id', $userId)->firstOrFail();
        
        $today = now()->toDateString();
        
        $pending = Appointment::where('employee_id', $employee->id)
            ->where('appointment_date', $today)
            ->where('status', 'confirmada')
            ->count();
            
        $inProgress = Appointment::where('employee_id', $employee->id)
            ->where('appointment_date', $today)
            ->where('status', 'en_atencion')
            ->count();
            
        $completedToday = Appointment::where('employee_id', $employee->id)
            ->where('appointment_date', $today)
            ->where('status', 'completada')
            ->count();
            
        $totalToday = Appointment::where('employee_id', $employee->id)
            ->where('appointment_date', $today)
            ->count();
        
        return [
            'pending' => $pending,
            'in_progress' => $inProgress,
            'completed_today' => $completedToday,
            'total_today' => $totalToday,
        ];
    }

    public function getMyAppointments($userId, $status = null)
    {
        $employee = Employee::where('user_id', $userId)->firstOrFail();
        
        $query = Appointment::where('employee_id', $employee->id)
            ->where('appointment_date', '>=', now()->toDateString());
            
        if ($status) {
            $query->where('status', $status);
        }
        
        return $query->with(['patient', 'insurance'])
            ->orderBy('appointment_date')
            ->orderBy('start_time')
            ->get();
    }

    public function startConsultation($appointmentId)
    {
        return DB::transaction(function () use ($appointmentId) {
            $appointment = Appointment::findOrFail($appointmentId);
            $appointment->status = 'en_atencion';
            $appointment->save();
            
            return $appointment->load(['patient', 'employee', 'insurance']);
        });
    }

    public function completeConsultation($appointmentId)
    {
        return DB::transaction(function () use ($appointmentId) {
            $appointment = Appointment::findOrFail($appointmentId);
            $appointment->status = 'completada';
            $appointment->save();
            
            return $appointment->load(['patient', 'employee', 'insurance']);
        });
    }
}