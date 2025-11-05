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
        // ✅ NO castear start_time y end_time - dejar como string
        // Las columnas son TIME en DB, Laravel las retorna como string "HH:mm:ss"
    ];

    public function scheduleTemplate()
    {
        return $this->belongsTo(ScheduleTemplate::class);
    }

    public function staffSchedules()
    {
        return $this->hasMany(StaffSchedule::class);
    }

    /**
     * Accessor para obtener solo HH:mm (sin segundos)
     */
    public function getStartTimeAttribute($value)
    {
        if (!$value) return null;
        return substr($value, 0, 5); // Retorna solo HH:mm
    }

    /**
     * Accessor para obtener solo HH:mm (sin segundos)
     */
    public function getEndTimeAttribute($value)
    {
        if (!$value) return null;
        return substr($value, 0, 5); // Retorna solo HH:mm
    }
}