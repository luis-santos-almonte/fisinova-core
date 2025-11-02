<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffSchedule extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        'staff_id',  // ✅ Ahora apunta a employees
        'schedule_day_id',
        'cubicle_id',
        'assignment_date',
        'end_date',
        'is_override',
        'original_staff_id',  // ✅ Ahora apunta a employees
        'status',
        'notes',
    ];

    protected $casts = [
        'assignment_date' => 'date',
        'end_date' => 'date',
        'is_override' => 'boolean',
    ];

    // ========== RELACIONES ==========
    
    /**
     * ✅ CAMBIO: staff() ahora devuelve Employee
     */
    public function staff()
    {
        return $this->belongsTo(Employee::class, 'staff_id');
    }

    public function scheduleDay()
    {
        return $this->belongsTo(ScheduleDay::class);
    }

    public function cubicle()
    {
        return $this->belongsTo(Cubicle::class);
    }

    /**
     * ✅ CAMBIO: originalStaff() ahora devuelve Employee
     */
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
        return $query->whereNull('assignment_date');
    }

    public function scopeSpecific($query)
    {
        return $query->whereNotNull('assignment_date');
    }
}