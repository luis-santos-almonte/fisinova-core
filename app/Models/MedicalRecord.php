<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicalRecord extends Model
{
    protected $fillable = [
        'appointment_id',
        'patient_id',
        'employee_id',
        'chief_complaint',
        'current_illness',
        'blood_pressure_systolic',
        'blood_pressure_diastolic',
        'heart_rate',
        'temperature',
        'respiratory_rate',
        'weight',
        'height',
        'bmi',
        'oxygen_saturation',
        'smokes',
        'smoking_frequency',
        'drinks_alcohol',
        'alcohol_frequency',
        'uses_drugs',
        'drug_type',
        'has_diabetes',
        'has_hypertension',
        'has_asthma',
        'other_conditions',
        'previous_surgeries',
        'current_medications',
        'family_history',
        'allergies',
        'physical_exam',
        'diagnosis_ids',
        'diagnosis_notes',
        'procedure_ids',
        'procedure_notes',
        'treatment_plan',
        'prescriptions',
        'recommendations',
        'general_notes',
        'requires_therapy',
        'therapy_sessions_needed',
        'therapy_reason',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'smokes' => 'boolean',
        'drinks_alcohol' => 'boolean',
        'uses_drugs' => 'boolean',
        'has_diabetes' => 'boolean',
        'has_hypertension' => 'boolean',
        'has_asthma' => 'boolean',
        'diagnosis_ids' => 'array',
        'procedure_ids' => 'array',
        'requires_therapy' => 'boolean',
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
}