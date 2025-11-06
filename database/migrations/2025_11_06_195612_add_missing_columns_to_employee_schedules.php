<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_schedules', function (Blueprint $table) {
            // Agregar schedule_template_id si no existe
            if (!Schema::hasColumn('employee_schedules', 'schedule_template_id')) {
                $table->foreignId('schedule_template_id')
                    ->nullable()
                    ->after('employee_id')
                    ->constrained('schedule_templates')
                    ->onDelete('cascade');
            }

            // Agregar selected_days
            if (!Schema::hasColumn('employee_schedules', 'selected_days')) {
                $table->json('selected_days')
                    ->nullable()
                    ->after('schedule_template_id');
            }

            // Agregar start_date si no existe
            if (!Schema::hasColumn('employee_schedules', 'start_date')) {
                $table->date('start_date')
                    ->nullable()
                    ->after('selected_days');
            }

            // Agregar specific_date y campos relacionados
            if (!Schema::hasColumn('employee_schedules', 'specific_date')) {
                $table->date('specific_date')->nullable()->after('end_date');
                $table->time('specific_start_time')->nullable()->after('specific_date');
                $table->time('specific_end_time')->nullable()->after('specific_start_time');
            }
        });

        echo "✅ Columnas agregadas a employee_schedules\n";
    }

    public function down(): void
    {
        Schema::table('employee_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('employee_schedules', 'schedule_template_id')) {
                $table->dropForeign(['schedule_template_id']);
                $table->dropColumn('schedule_template_id');
            }
            
            $table->dropColumn([
                'selected_days',
                'specific_date',
                'specific_start_time',
                'specific_end_time'
            ]);
        });
    }
};