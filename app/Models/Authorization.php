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
        'authorization_number',
        'authorization_date',
        'authorization_type',
        'insurance_amount',      // ✅ NUEVO
        'patient_amount',        // ✅ NUEVO
        'total_amount',          // ✅ NUEVO
        'sessions_authorized',
        'sessions_completed',
        'notes',
        'active',
        'patient_name',
        'patient_last_name',
        'patient_dni',
        'patient_insurance_code',
        'patient_gender',
        'PSS_code',
        'city',
        'stablishment_phone',
        'medic_name',
        'medic_specialty',
        'diagnosis_codes',
        'services_authorized',
        'case_number',          // Para riesgo laboral
    ];

    protected $casts = [
        'authorization_date' => 'date',
        'insurance_amount' => 'decimal:2',   // ✅ NUEVO
        'patient_amount' => 'decimal:2',     // ✅ NUEVO
        'total_amount' => 'decimal:2',       // ✅ NUEVO
        'sessions_authorized' => 'integer',
        'sessions_completed' => 'integer',
        'active' => 'boolean',
        'diagnosis_codes' => 'array',
        'services_authorized' => 'array',
    ];

    // Relaciones
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

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function medic()
    {
        return $this->belongsTo(Employee::class, 'medic_id');
    }

    public function therapyAppointments()
    {
        return $this->hasMany(Appointment::class, 'authorization_number', 'authorization_number')
            ->where('type', 'therapy');
    }

    // ✅ NUEVO: Calcular total automáticamente
    public static function boot()
    {
        parent::boot();

        static::saving(function ($authorization) {
            $authorization->total_amount =
                $authorization->insurance_amount + $authorization->patient_amount;
        });
    }

    // ✅ NUEVO: Scope para reportes por rango de fechas
    public function scopeForReport($query, $insuranceId, $startDate, $endDate)
    {
        return $query->where('insurance_id', $insuranceId)
            ->whereBetween('authorization_date', [$startDate, $endDate])
            ->where('active', true)
            ->with(['patient', 'insurance', 'appointment']);
    }

    // ✅ NUEVO: Obtener tipo de servicio
    public function getServiceTypeAttribute()
    {
        if ($this->appointment) {
            return $this->appointment->type;
        }

        // Si tiene sesiones es terapia
        if ($this->sessions_authorized > 0) {
            return 'therapy';
        }

        return 'consultation';
    }

    // ✅ NUEVO: Obtener descripción del procedimiento
    public function getProcedureDescriptionAttribute()
    {
        $type = $this->service_type;

        $labels = [
            'consultation' => 'CONSULTA',
            'therapy' => 'TERAPIA',
            'admission' => 'INTERNAMIENTO',
        ];

        return $labels[$type] ?? 'SERVICIO';
    }
}
