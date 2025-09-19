<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            // Evitar duplicados por empleado y día
            $table->unique(['employee_id', 'day_of_week']);
            $table->index(['employee_id', 'day_of_week', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
