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
        'hour_start',
        'minute_start',
        'hour_end',
        'minute_end',
        'appointment_date',
        'active',
        'notes',
        'dni',
        'phone',
        'passport',
        'insurance_code',
        'insurance_id',
    ];

    protected $casts = [
        'appointment_date' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function insurance()
    {
        return $this->belongsTo(Insurance::class);
    }
}
