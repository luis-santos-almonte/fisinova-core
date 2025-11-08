<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeSchedule extends Model
{
    protected $fillable = [
        'employee_id',
        'schedule_template_id',
        'selected_days',
        'cubicle_id',
        'start_date',
        'end_date',
        'specific_date',
        'specific_start_time',
        'specific_end_time',
        'is_override',
        'original_employee_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'selected_days' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
        'specific_date' => 'date',
        'is_override' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function scheduleTemplate()
    {
        return $this->belongsTo(ScheduleTemplate::class);
    }

    public function cubicle()
    {
        return $this->belongsTo(Cubicle::class);
    }

    public function originalEmployee()
    {
        return $this->belongsTo(Employee::class, 'original_employee_id');
    }

    /**
     * Verifica si el horario aplica para una fecha específica
     */
    public function appliesOnDate($date)
    {
        // Si es asignación específica
        if ($this->specific_date) {
            return $this->specific_date->isSameDay($date);
        }

        // Verificar rango de vigencia
        if ($this->start_date && $date->lt($this->start_date)) {
            return false;
        }

        if ($this->end_date && $date->gt($this->end_date)) {
            return false;
        }

        // Verificar días seleccionados
        if ($this->selected_days && !empty($this->selected_days)) {
            return in_array($date->dayOfWeek, $this->selected_days);
        }

        // Si no hay días específicos, verificar el template
        if ($this->scheduleTemplate && $this->scheduleTemplate->scheduleDays) {
            return $this->scheduleTemplate->scheduleDays
                ->contains('day_of_week', $date->dayOfWeek);
        }

        return false;
    }

    /**
     * Obtiene los horarios (start_time, end_time) para una fecha específica
     */
    public function getScheduleForDate($date)
    {
        // Si es asignación específica con horarios propios
        if ($this->specific_date && $this->specific_start_time && $this->specific_end_time) {
            return [
                'start_time' => $this->specific_start_time,
                'end_time' => $this->specific_end_time,
            ];
        }

        // Buscar en el template el día correspondiente
        if (!$this->scheduleTemplate || !$this->scheduleTemplate->scheduleDays) {
            return [];
        }

        $scheduleDay = $this->scheduleTemplate->scheduleDays
            ->firstWhere('day_of_week', $date->dayOfWeek);

        if (!$scheduleDay) {
            return [];
        }

        return [
            'start_time' => $scheduleDay->start_time,
            'end_time' => $scheduleDay->end_time,
        ];
    }

    /**
     * Alias para compatibilidad con código legacy
     */
    public function staff()
    {
        return $this->employee();
    }
}
