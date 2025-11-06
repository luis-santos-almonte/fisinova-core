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
}