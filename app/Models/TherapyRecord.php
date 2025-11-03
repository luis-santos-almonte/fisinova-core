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
        // Inicio
        'initial_patient_state',
        'initial_observations',
        'started_at',
        // Procedimientos
        'procedure_ids',
        'procedure_notes',
        // Cierre
        'final_patient_state',
        'final_observations',
        'next_session_recommendation',
        'ended_at',
        // Otros
        'duration_minutes',
        'intensity',
        'completed',
        'active',
    ];

    protected $casts = [
        'procedure_ids' => 'array',
        'completed' => 'boolean',
        'active' => 'boolean',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
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
