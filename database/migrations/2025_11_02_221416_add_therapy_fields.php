<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            // Campo para asignar una terapista diferente al médico de la consulta
            $table->foreignId('therapist_id')
                ->nullable()
                ->after('employee_id')
                ->constrained('employees')
                ->onDelete('set null')
                ->comment('Terapista asignado (solo para terapias)');

            // Hora real de inicio de la sesión (diferente a la programada)
            $table->time('actual_start_time')
                ->nullable()
                ->after('start_time')
                ->comment('Hora real de inicio de la sesión');

            // Hora real de fin de la sesión
            $table->time('actual_end_time')
                ->nullable()
                ->after('actual_start_time')
                ->comment('Hora real de fin de la sesión');

            // Índices para optimizar consultas
            $table->index('therapist_id');
            $table->index(['appointment_date', 'therapist_id']);
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['appointment_date', 'therapist_id']);
            $table->dropIndex(['therapist_id']);
            
            $table->dropForeign(['therapist_id']);
            $table->dropColumn([
                'therapist_id',
                'actual_start_time',
                'actual_end_time'
            ]);
        });
    }
};