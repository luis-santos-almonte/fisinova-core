<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffSchedule extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        'staff_id',
        'schedule_template_id',       // ✅ NUEVO: Referencia al template completo
        'selected_days',               // ✅ NUEVO: Array de días seleccionados [1,2,3] o null=todos
        'cubicle_id',
        'start_date',                  // ✅ RENOMBRADO: Fecha de inicio de vigencia
        'end_date',                    // Fecha de fin de vigencia
        'specific_date',               // ✅ NUEVO: Solo para asignaciones puntuales
        'specific_start_time',         // ✅ NUEVO: Hora inicio para fecha específica
        'specific_end_time',           // ✅ NUEVO: Hora fin para fecha específica
        'is_override',
        'original_staff_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'specific_date' => 'date',
        'selected_days' => 'array',    // ✅ Cast automático a array
        'is_override' => 'boolean',
    ];

    // ========== RELACIONES ==========
    
    public function staff()
    {
        return $this->belongsTo(Employee::class, 'staff_id');
    }

    /**
     * ✅ NUEVO: Relación directa con el template
     */
    public function scheduleTemplate()
    {
        return $this->belongsTo(ScheduleTemplate::class);
    }

    public function cubicle()
    {
        return $this->belongsTo(Cubicle::class);
    }

    public function originalStaff()
    {
        return $this->belongsTo(Employee::class, 'original_staff_id');
    }

    // ========== SCOPES ==========
    
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeRecurring($query)
    {
        return $query->whereNull('specific_date');
    }

    public function scopeSpecific($query)
    {
        return $query->whereNotNull('specific_date');
    }

    // ========== MÉTODOS AUXILIARES ==========
    
    /**
     * ✅ Verifica si esta asignación aplica para una fecha dada
     */
    public function appliesOnDate(\Carbon\Carbon $date): bool
    {
        // Si es asignación específica, solo aplica ese día
        if ($this->specific_date) {
            return $this->specific_date->isSameDay($date);
        }

        // Verificar que esté en el rango de vigencia
        if ($this->start_date && $date->lt($this->start_date)) {
            return false;
        }
        if ($this->end_date && $date->gt($this->end_date)) {
            return false;
        }

        // Si hay días seleccionados, verificar que sea uno de ellos
        if ($this->selected_days && !empty($this->selected_days)) {
            return in_array($date->dayOfWeekIso, $this->selected_days); // 1=Lun, 7=Dom
        }

        // Si no hay días seleccionados, aplica todos los días
        return true;
    }

    /**
     * ✅ Obtiene el horario para un día específico
     */
    public function getScheduleForDate(\Carbon\Carbon $date): ?array
    {
        if (!$this->appliesOnDate($date)) {
            return null;
        }

        // Si es asignación específica, retornar horario específico
        if ($this->specific_date) {
            return [
                'start_time' => $this->specific_start_time,
                'end_time' => $this->specific_end_time,
            ];
        }

        // Buscar el ScheduleDay correspondiente
        $scheduleDay = $this->scheduleTemplate->scheduleDays()
            ->where('day_of_week', $date->dayOfWeekIso)
            ->first();

        if (!$scheduleDay) {
            return null;
        }

        return [
            'start_time' => $scheduleDay->start_time,
            'end_time' => $scheduleDay->end_time,
        ];
    }
}