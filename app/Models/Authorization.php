<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Authorization extends Model
{
    protected $fillable = [
        'appointment_id',
        'patient_id',
        'insurance_id',
        'created_by',
        'medic_id',
        'patient_name',
        'patient_last_name',
        'patient_dni',
        'patient_insurance_code',
        'patient_gender',
        'city',
        'authorization_number',
        'authorization_type',
        'authorization_date',
        'PSS_code',
        'stablishment_phone',
        'medic_name',
        'medic_specialty',
        'notes',
        'services_authorized',
        'diagnosis_codes',
        'active',
        // ✅ NUEVOS CAMPOS
        'sessions_authorized',
        'sessions_completed',
    ];

    protected $casts = [
        'authorization_date' => 'date',
        'active' => 'boolean',
        'services_authorized' => 'array',
        'diagnosis_codes' => 'array',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function insurance()
    {
        return $this->belongsTo(Insurance::class);
    }

    public function medic()
    {
        return $this->belongsTo(Employee::class, 'medic_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * ✅ NUEVA RELACIÓN: Citas de terapia generadas de esta autorización
     */
    public function therapyAppointments()
    {
        return $this->hasMany(Appointment::class, 'authorization_id')
            ->where('type', Appointment::TYPE_THERAPY)
            ->orderBy('session_number');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * ✅ NUEVO: Verificar si tiene sesiones autorizadas
     */
    public function hasSessions(): bool
    {
        return $this->sessions_authorized !== null && $this->sessions_authorized > 0;
    }

    /**
     * ✅ NUEVO: Obtener sesiones restantes
     */
    public function getRemainingSessionsAttribute(): int
    {
        if (!$this->hasSessions()) {
            return 0;
        }

        return $this->sessions_authorized - $this->sessions_completed;
    }

    /**
     * ✅ NUEVO: Obtener porcentaje de progreso
     */
    public function getProgressPercentageAttribute(): float
    {
        if (!$this->hasSessions() || $this->sessions_authorized == 0) {
            return 0;
        }

        return round(($this->sessions_completed / $this->sessions_authorized) * 100, 2);
    }

    /**
     * ✅ NUEVO: Verificar si todas las sesiones están completadas
     */
    public function isCompleted(): bool
    {
        return $this->hasSessions() && 
               $this->sessions_completed >= $this->sessions_authorized;
    }

    /**
     * ✅ NUEVO: Incrementar contador de sesiones completadas
     */
    public function incrementCompletedSessions(): void
    {
        if ($this->sessions_completed < $this->sessions_authorized) {
            $this->sessions_completed++;
            $this->save();
        }
    }

    /**
     * ✅ NUEVO: Scope para autorizaciones con sesiones pendientes
     */
    public function scopeWithPendingSessions($query)
    {
        return $query->whereNotNull('sessions_authorized')
            ->whereColumn('sessions_completed', '<', 'sessions_authorized');
    }

    /**
     * ✅ NUEVO: Scope para autorizaciones completadas
     */
    public function scopeCompletedSessions($query)
    {
        return $query->whereNotNull('sessions_authorized')
            ->whereColumn('sessions_completed', '>=', 'sessions_authorized');
    }

    /**
     * ✅ NUEVO: Obtener estadísticas de las citas generadas
     */
    public function getTherapyStats(): array
    {
        $appointments = $this->therapyAppointments;

        return [
            'total' => $appointments->count(),
            'completed' => $appointments->where('status', 'completada')->count(),
            'confirmed' => $appointments->where('status', 'confirmada')->count(),
            'scheduled' => $appointments->where('status', 'programada')->count(),
            'cancelled' => $appointments->where('status', 'cancelada')->count(),
        ];
    }
}