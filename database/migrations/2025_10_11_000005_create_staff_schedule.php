<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('staff_schedules')) {
            Schema::create('staff_schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('staff_id')->constrained('staff')->onDelete('cascade');
                $table->foreignId('schedule_day_id')->constrained('schedule_days')->onDelete('cascade');
                $table->foreignId('cubicle_id')->nullable()->constrained('cubicles')->onDelete('set null');
                $table->date('assignment_date')->nullable()->comment('NULL para horarios recurrentes');
                $table->date('end_date')->nullable()->comment('Para asignaciones temporales');
                $table->boolean('is_override')->default(false)->comment('Si es una suplencia/excepción');
                $table->foreignId('original_staff_id')->nullable()->constrained('staff')->onDelete('set null')->comment('Para suplencias');
                $table->string('status', 20)->default('active')->comment('active, cancelled, completed');
                $table->text('notes')->nullable();
                $table->timestamps();
                
                $table->index(['staff_id', 'status']);
                $table->index(['assignment_date', 'status']);
                $table->index('schedule_day_id');
                $table->index('cubicle_id');
            });

            echo "✅ Tabla staff_schedules creada\n";
        } else {
            echo "ℹ️  Tabla staff_schedules ya existe\n";
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_schedules');
    }
};