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
        'schedule_day_id',
        'cubicle_id',
        'assignment_date',
        'end_date',
        'is_override',
        'original_staff_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'assignment_date' => 'date',
        'end_date' => 'date',
        'is_override' => 'boolean',
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function scheduleDay()
    {
        return $this->belongsTo(ScheduleDay::class);
    }

    public function cubicle()
    {
        return $this->belongsTo(Cubicle::class);
    }

    public function originalStaff()
    {
        return $this->belongsTo(Staff::class, 'original_staff_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeRecurring($query)
    {
        return $query->whereNull('assignment_date');
    }

    public function scopeSpecific($query)
    {
        return $query->whereNotNull('assignment_date');
    }
}