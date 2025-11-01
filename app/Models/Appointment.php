<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use App\Traits\HasActiveToggle;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory, HasActiveScope, HasActiveToggle;
    public $timestamps = true;

    protected $fillable = [
        'employee_id',
        'patient_id',
        'start_time',
        'end_time',
        'appointment_date',
        'active',
        'notes',
        'dni',
        'phone',
        'passport',
        'insurance_code',
        'insurance_id',
        'guest_firstname',
        'guest_lastname',
        'status',
        'payment_type',
        'authorization_number',
        'case_number',
        'confirmed_at',
        'confirmed_by',
        'type',
        // ✅ NUEVOS CAMPOS
        'session_number',
        'total_sessions',
        'authorization_id',
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'active' => 'boolean',
        'confirmed_at' => 'datetime',
    ];

    const TYPE_CONSULTATION = 'consultation';
    const TYPE_THERAPY = 'therapy';

    const PAYMENT_INSURANCE = 'insurance';
    const PAYMENT_PRIVATE = 'private';
    const PAYMENT_WORKPLACE_RISK = 'workplace_risk';

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function insurance()
    {
        return $this->belongsTo(Insurance::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function procedures()
    {
        return $this->hasMany(Procedure::class);
    }

    public function authorizations()
    {
        return $this->hasMany(Authorization::class);
    }

    /**
     * ✅ NUEVA RELACIÓN: Autorización principal de esta cita de terapia
     */
    public function authorization()
    {
        return $this->belongsTo(Authorization::class);
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
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

    /**
     * ✅ NUEVO: Verificar si es una consulta completada
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completada';
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

    /**
     * Una cita requiere autorización previa si:
     * - Es terapia Y es por seguro
     */
    public function requiresPriorAuthorization(): bool
    {
        return $this->type === self::TYPE_THERAPY &&
            $this->payment_type === self::PAYMENT_INSURANCE;
    }

    /**
     * Verificar si es riesgo laboral
     */
    public function isWorkplaceRisk(): bool
    {
        return $this->payment_type === self::PAYMENT_WORKPLACE_RISK;
    }

    /**
     * Verificar si requiere esperar autorización de IDOPPRIL
     */
    public function requiresIdopprilAuthorization(): bool
    {
        return $this->isConsultation() && $this->isWorkplaceRisk();
    }

    /**
     * ✅ NUEVO: Verificar si es parte de una serie de terapias
     */
    public function isPartOfSeries(): bool
    {
        return $this->session_number !== null && $this->total_sessions !== null;
    }

    /**
     * ✅ NUEVO: Obtener información de progreso de la serie
     */
    public function getSeriesProgress(): ?array
    {
        if (!$this->isPartOfSeries()) {
            return null;
        }

        return [
            'current' => $this->session_number,
            'total' => $this->total_sessions,
            'percentage' => round(($this->session_number / $this->total_sessions) * 100, 2),
            'remaining' => $this->total_sessions - $this->session_number,
        ];
    }

    /**
     * ✅ NUEVO: Scope para citas de terapia de una serie específica
     */
    public function scopeBySeries($query, int $authorizationId)
    {
        return $query->where('authorization_id', $authorizationId)
            ->orderBy('session_number');
    }

    /**
     * ✅ NUEVO: Scope para consultas completadas
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completada');
    }

    /**
     * ✅ NUEVO: Scope para consultas pendientes de autorización
     */
    public function scopePendingAuthorization($query)
    {
        return $query->where('type', self::TYPE_CONSULTATION)
            ->where('status', 'completada')
            ->whereDoesntHave('authorizations');
    }
}