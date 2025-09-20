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
    ];

    protected $casts = [
        'appointment_date' => 'date',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
    ];

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

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
