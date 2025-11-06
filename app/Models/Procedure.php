<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Procedure extends Model
{
    public $timestamps = true;

    protected $fillable = [
        'appointment_id',
        'patient_id',
        'employee_id',
        'procedure_type_id', // ✅ Fixed to use procedure_types table
        'insurance_code',
        'insurance_id',
        'case_number',
        'notes',
        'active',
        'authorization_code',
        'dni'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function procedureType()
    {
        return $this->belongsTo(ProcedureType::class);
    }

    public function insurance()
    {
        return $this->belongsTo(Insurance::class);
    }

    public function procedureDetails()
    {
        return $this->hasMany(ProcedureDetail::class);
    }

    public function procedureDiagnostics()
    {
        return $this->hasMany(ProcedureDiagnostic::class);
    }

    public function medicalRecord()
    {
        return $this->hasOne(MedicalRecord::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}
