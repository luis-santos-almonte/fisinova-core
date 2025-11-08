<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TherapyRecord extends Model
{
    protected $fillable = [
        'appointment_id',
        'patient_id',
        'therapist_id',
        'authorization_id',
        'initial_patient_state',
        'initial_observations',
        'procedure_ids',
        'procedure_notes',
        'final_patient_state',
        'final_observations',
        'next_session_recommendation',
        'intensity',
        'started_at',
        'ended_at',
        'duration_minutes',
        'completed',
        'active',
        'selected_procedure_detail_ids', // NUEVO - qué procedimientos se realizaron
    ];
    
    protected $casts = [
        'procedure_ids' => 'array',
        'selected_procedure_detail_ids' => 'array', // NUEVO
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'completed' => 'boolean',
        'active' => 'boolean',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function therapist()
    {
        return $this->belongsTo(Employee::class, 'therapist_id');
    }

    public function authorization()
    {
        return $this->belongsTo(Authorization::class);
    }

    public function isStarted(): bool
    {
        return $this->started_at !== null;
    }

    public function isCompleted(): bool
    {
        return $this->completed && $this->ended_at !== null;
    }
}
