<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    const TYPE_CONSULTATION = 'consultation';
    const TYPE_THERAPY = 'therapy';
    const PAYMENT_INSURANCE = 'insurance';
    const PAYMENT_PRIVATE = 'private';
    const PAYMENT_WORKPLACE_RISK = 'workplace_risk';

    protected $fillable = [
        'employee_id',
        'patient_id',
        'appointment_date',
        'start_time',
        'end_time',
        'therapist_id', // ✅ NUEVO
        'actual_start_time', // ✅ NUEVO
        'actual_end_time', // ✅ NUEVO
        'status',
        'notes',
        'dni',
        'phone',
        'passport',
        'insurance_code',
        'insurance_id',
        'guest_firstname',
        'guest_lastname',
        'active',
        'type',
        'payment_type',
        'authorization_number',
        'case_number',
        'confirmed_at',
        'confirmed_by',
        'authorization_id',
        'session_number',
        'total_sessions',
        'consultation_appointment_id', // ✅ Relación con consulta padre
        'procedure_detail_id'
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'actual_start_time' => 'datetime:H:i:s', // ✅ NUEVO
        'actual_end_time' => 'datetime:H:i:s', // ✅ NUEVO
        'confirmed_at' => 'datetime',
        'active' => 'boolean',
    ];

    /**
     * Médico referente o que creó la cita
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * ✅ NUEVA RELACIÓN: Terapista asignado (solo para terapias)
     */
    public function therapist()
    {
        return $this->belongsTo(Employee::class, 'therapist_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function insurance()
    {
        return $this->belongsTo(Insurance::class);
    }

    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function medicalRecord()
    {
        return $this->hasOne(MedicalRecord::class);
    }

    public function authorizations()
    {
        return $this->hasMany(Authorization::class);
    }

    /**
     * Autorización principal de esta cita de terapia
     */
    public function authorization()
    {
        return $this->belongsTo(Authorization::class);
    }

    public function procedures()
    {
        return $this->hasMany(Procedure::class, 'appointment_id');
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function therapyRecord()
    {
        return $this->hasOne(TherapyRecord::class);
    }

    public function confirm(User $user, ?String $authNumber = null)
    {
        $this->status = 'confirmada';
        $this->confirmed_at = now();
        $this->confirmed_by = $user->id();
        if ($authNumber) {
            $this->authorization_number = $authNumber;
        }
        $this->save();
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmada';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completada';
    }

    /**
     * ✅ NUEVO: Marcar hora real de inicio
     */
    public function markActualStartTime()
    {
        $this->actual_start_time = now()->format('H:i:s');
        $this->save();
    }

    /**
     * ✅ NUEVO: Marcar hora real de fin
     */
    public function markActualEndTime()
    {
        $this->actual_end_time = now()->format('H:i:s');
        $this->status = 'completada';
        $this->save();
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function isConsultation(): bool
    {
        return $this->type === self::TYPE_CONSULTATION;
    }


    public function isTherapy(): bool
    {
        return $this->type === self::TYPE_THERAPY;
    }

    public function requiresPriorAuthorization(): bool
    {
        return $this->type === self::TYPE_THERAPY &&
            $this->payment_type === self::PAYMENT_INSURANCE;
    }

    public function isWorkplaceRisk(): bool
    {
        return $this->payment_type === self::PAYMENT_WORKPLACE_RISK;
    }

    /**
     * ✅ NUEVO: Obtener el profesional que debe atender la cita
     * Para consultas: employee
     * Para terapias: therapist (si existe), sino employee
     */
    public function getAttendingProfessional()
    {
        if ($this->isTherapy() && $this->therapist_id) {
            return $this->therapist;
        }
        return $this->employee;
    }

    /**
     * ✅ NUEVO: Verificar si hay diferencia entre hora programada y real
     */
    public function hasTimeDifference(): bool
    {
        if (!$this->actual_start_time || !$this->actual_end_time) {
            return false;
        }

        return $this->start_time !== $this->actual_start_time ||
            $this->end_time !== $this->actual_end_time;
    }

    /**
     * ✅ NUEVO: Calcular duración programada (en minutos)
     */
    public function getScheduledDuration(): int
    {
        $start = \Carbon\Carbon::parse($this->start_time);
        $end = \Carbon\Carbon::parse($this->end_time);
        return $end->diffInMinutes($start);
    }

    /**
     * ✅ NUEVO: Calcular duración real (en minutos)
     */
    public function getActualDuration(): ?int
    {
        if (!$this->actual_start_time || !$this->actual_end_time) {
            return null;
        }

        $start = \Carbon\Carbon::parse($this->actual_start_time);
        $end = \Carbon\Carbon::parse($this->actual_end_time);
        return $end->diffInMinutes($start);
    }

    public function consultationAppointment()
    {
        return $this->belongsTo(Appointment::class, 'consultation_appointment_id');
    }

    public function therapyAppointments()
    {
        return $this->hasMany(Appointment::class, 'consultation_appointment_id');
    }

    public function procedureDetail()
    {
        return $this->belongsTo(ProcedureDetail::class);
    }
}
