<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Authorization extends Model
{
    protected $fillable = [
        'appointment_id',
        'patient_id',
        'insurance_id',
        'created_by',
        'medic_id',
        'patient_name',
        'patient_last_name',
        'patient_dni',
        'patient_insurance_code',
        'patient_gender',
        'city',
        'authorization_number',
        'authorization_type',
        'authorization_date',
        'PSS_code',
        'stablishment_phone',
        'medic_name',
        'medic_specialty',
        'notes',
        'services_authorized',
        'diagnosis_codes',
        'active',
    ];

    protected $casts = [
        'authorization_date' => 'date',
        'active' => 'boolean',
        'services_authorized' => 'array',
        'diagnosis_codes' => 'array',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function insurance()
    {
        return $this->belongsTo(Insurance::class);
    }

    public function medic()
    {
        return $this->belongsTo(Employee::class, 'medic_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
