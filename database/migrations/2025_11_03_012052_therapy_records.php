<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('therapy_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('therapist_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('authorization_id')->nullable()->constrained()->onDelete('set null');
            
            // Al INICIAR la terapia
            $table->text('initial_patient_state')->nullable(); // Estado inicial del paciente
            $table->text('initial_observations')->nullable(); // Observaciones iniciales
            $table->timestamp('started_at')->nullable(); // Hora real de inicio
            
            // Procedimientos aplicados (JSON de IDs de procedure_standards)
            $table->json('procedure_ids')->nullable();
            $table->text('procedure_notes')->nullable(); // Notas sobre los procedimientos
            
            // Al CERRAR la terapia
            $table->text('final_patient_state')->nullable(); // Estado final del paciente
            $table->text('final_observations')->nullable(); // Observaciones finales
            $table->text('next_session_recommendation')->nullable(); // Recomendación para siguiente sesión
            $table->timestamp('ended_at')->nullable(); // Hora real de fin
            
            // Otros
            $table->integer('duration_minutes')->nullable(); // Duración real
            $table->string('intensity')->nullable(); // low, moderate, high
            $table->boolean('completed')->default(false);
            $table->boolean('active')->default(true);
            $table->timestamps();
            
            $table->index(['patient_id', 'created_at']);
            $table->index(['therapist_id', 'created_at']);
            $table->index('appointment_id');
            $table->index('authorization_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('therapy_records');
    }
};