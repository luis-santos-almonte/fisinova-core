<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleDay extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        'schedule_template_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_recurring',
    ];

    protected $casts = [
        'is_recurring' => 'boolean',
        'day_of_week' => 'integer',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

    public function scheduleTemplate()
    {
        return $this->belongsTo(ScheduleTemplate::class);
    }

    public function staffSchedules()
    {
        return $this->hasMany(StaffSchedule::class);
    }
}