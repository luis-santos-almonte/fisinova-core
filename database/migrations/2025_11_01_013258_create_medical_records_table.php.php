// database/migrations/2025_10_31_000001_create_medical_records_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            
            // Motivo de consulta
            $table->text('chief_complaint')->nullable();
            $table->text('current_illness')->nullable();
            
            // Signos vitales
            $table->decimal('blood_pressure_systolic', 5, 2)->nullable();
            $table->decimal('blood_pressure_diastolic', 5, 2)->nullable();
            $table->decimal('heart_rate', 5, 2)->nullable();
            $table->decimal('temperature', 4, 2)->nullable();
            $table->decimal('respiratory_rate', 5, 2)->nullable();
            $table->decimal('weight', 6, 2)->nullable();
            $table->decimal('height', 5, 2)->nullable();
            $table->decimal('bmi', 5, 2)->nullable();
            $table->decimal('oxygen_saturation', 5, 2)->nullable();
            
            // Antecedentes personales
            $table->boolean('smokes')->default(false);
            $table->string('smoking_frequency')->nullable();
            $table->boolean('drinks_alcohol')->default(false);
            $table->string('alcohol_frequency')->nullable();
            $table->boolean('uses_drugs')->default(false);
            $table->string('drug_type')->nullable();
            $table->boolean('has_diabetes')->default(false);
            $table->boolean('has_hypertension')->default(false);
            $table->boolean('has_asthma')->default(false);
            $table->text('other_conditions')->nullable();
            $table->text('previous_surgeries')->nullable();
            $table->text('current_medications')->nullable();
            
            // Antecedentes familiares
            $table->text('family_history')->nullable();
            
            // Alergias
            $table->text('allergies')->nullable();
            
            // Examen físico
            $table->text('physical_exam')->nullable();
            
            // Diagnósticos (JSON array de IDs)
            $table->json('diagnosis_ids')->nullable();
            $table->text('diagnosis_notes')->nullable();
            
            // Procedimientos (JSON array de IDs)
            $table->json('procedure_ids')->nullable();
            $table->text('procedure_notes')->nullable();
            
            // Plan y tratamiento
            $table->text('treatment_plan')->nullable();
            $table->text('prescriptions')->nullable();
            $table->text('recommendations')->nullable();
            
            // Notas generales
            $table->text('general_notes')->nullable();
            
            $table->boolean('active')->default(true);
            $table->timestamps();
            
            $table->index(['patient_id', 'created_at']);
            $table->index(['employee_id', 'created_at']);
            $table->index('appointment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};