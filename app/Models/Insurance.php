<?php

namespace App\Models;

use App\Traits\HasActiveScope;
use App\Traits\HasActiveToggle;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insurance extends Model
{
    use HasFactory, HasActiveScope, HasActiveToggle;

    public $timestamps = true;

    protected $fillable = [
        'name',
        'provider_code',
        'active'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    // ========== RELACIONES ==========

    /**
     * Citas asociadas a este seguro
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Pacientes que tienen este seguro
     */
    public function patients()
    {
        return $this->hasMany(Patient::class);
    }

    /**
     * Autorizaciones asociadas a este seguro
     */
    public function authorizations()
    {
        return $this->hasMany(Authorization::class);
    }

    /**
     * Procedimientos asociados a este seguro
     */
    public function procedures()
    {
        return $this->hasMany(Procedure::class);
    }

    // ========== SCOPES ==========

    /**
     * Scope para seguros activos
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope para seguros inactivos
     */
    public function scopeInactive($query)
    {
        return $query->where('active', false);
    }

    /**
     * Scope para búsqueda por nombre o código
     */
    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'ILIKE', "%{$term}%")
              ->orWhere('provider_code', 'ILIKE', "%{$term}%");
        });
    }

    // ========== ACCESSORS Y MÉTODOS AUXILIARES ==========

    /**
     * Obtiene el nombre completo con código
     */
    public function getFullNameAttribute()
    {
        return "{$this->name} ({$this->provider_code})";
    }

    /**
     * Verifica si el seguro tiene pacientes activos
     */
    public function hasActivePatients(): bool
    {
        return $this->patients()->where('active', true)->exists();
    }

    /**
     * Verifica si el seguro tiene citas futuras
     */
    public function hasFutureAppointments(): bool
    {
        return $this->appointments()
            ->where('appointment_date', '>=', now()->toDateString())
            ->where('active', true)
            ->exists();
    }

    /**
     * Cuenta el total de pacientes
     */
    public function getTotalPatientsAttribute(): int
    {
        return $this->patients()->count();
    }

    /**
     * Cuenta pacientes activos
     */
    public function getActivePatientsAttribute(): int
    {
        return $this->patients()->where('active', true)->count();
    }

    /**
     * Cuenta el total de citas
     */
    public function getTotalAppointmentsAttribute(): int
    {
        return $this->appointments()->count();
    }

    /**
     * Cuenta autorizaciones activas
     */
    public function getActiveAuthorizationsAttribute(): int
    {
        return $this->authorizations()->where('active', true)->count();
    }

    /**
     * Verifica si el seguro puede ser eliminado
     */
    public function canBeDeleted(): bool
    {
        return !$this->hasActivePatients() && !$this->hasFutureAppointments();
    }

    /**
     * Obtiene el motivo por el cual no se puede eliminar
     */
    public function getDeletionBlockReason(): ?string
    {
        if ($this->hasActivePatients()) {
            $count = $this->patients()->where('active', true)->count();
            return "Tiene {$count} paciente(s) activo(s) asociado(s)";
        }

        if ($this->hasFutureAppointments()) {
            $count = $this->appointments()
                ->where('appointment_date', '>=', now()->toDateString())
                ->where('active', true)
                ->count();
            return "Tiene {$count} cita(s) futura(s) asociada(s)";
        }

        return null;
    }
}