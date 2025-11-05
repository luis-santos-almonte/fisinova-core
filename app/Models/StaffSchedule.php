<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\Employee;
use App\Models\ScheduleTemplate;
use App\Models\Cubicle;
use Exception;
use Illuminate\Support\Facades\Log;

class StaffSchedule extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        'staff_id',
        'schedule_template_id',
        'selected_days',
        'cubicle_id',
        'start_date',
        'end_date',
        'specific_date',
        'specific_start_time',
        'specific_end_time',
        'is_override',
        'original_staff_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'specific_date' => 'date',
        'selected_days' => 'array',
        'is_override' => 'boolean',
        // ✅ NO castear specific_start_time y specific_end_time
        // Son TIME en DB, se manejan como string
    ];

    // ========== RELACIONES ==========

    public function staff()
    {
        return $this->belongsTo(Employee::class, 'staff_id');
    }

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
     * Verifica si esta asignación aplica para una fecha dada
     */
    public function appliesOnDate(Carbon $date): bool
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
     * Obtiene el horario para un día específico
     * Retorna array con start_time y end_time como strings "HH:mm"
     */
    public function getScheduleForDate(Carbon $date): ?array
    {
        if (!$this->appliesOnDate($date)) {
            return null;
        }

        // Si es asignación específica, retornar horario específico
        if ($this->specific_date) {
            return [
                'start_time' => $this->cleanTime($this->specific_start_time),
                'end_time' => $this->cleanTime($this->specific_end_time),
            ];
        }

        // Buscar el ScheduleDay correspondiente al día de la semana
        $scheduleDay = $this->scheduleTemplate->scheduleDays()
            ->where('day_of_week', $date->dayOfWeekIso)
            ->first();

        if (!$scheduleDay) {
            return null;
        }

        return [
            'start_time' => $this->cleanTime($scheduleDay->start_time),
            'end_time' => $this->cleanTime($scheduleDay->end_time),
        ];
    }

    /**
     * Limpia un valor de tiempo para asegurar formato HH:mm
     */
    private function cleanTime($time): ?string
    {
        if (empty($time)) {
            return null;
        }

        // Si ya es string en formato correcto
        if (is_string($time) && preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $time)) {
            return substr($time, 0, 5); // Retornar solo HH:mm
        }

        // Si es objeto Carbon o DateTime
        if ($time instanceof \Carbon\Carbon || $time instanceof \DateTime) {
            return $time->format('H:i');
        }

        // Intentar parsear como último recurso
        try {
            return Carbon::parse($time)->format('H:i');
        } catch (Exception $e) {
            Log::warning('Could not clean time value', [
                'time' => $time,
                'type' => gettype($time),
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Accessors para asegurar que los tiempos específicos se retornen como HH:mm
     */
    public function getSpecificStartTimeAttribute($value)
    {
        return $this->cleanTime($value);
    }

    public function getSpecificEndTimeAttribute($value)
    {
        return $this->cleanTime($value);
    }
}
