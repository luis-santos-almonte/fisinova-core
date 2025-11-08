<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Renombrar staff_schedules a employee_schedules
        if (Schema::hasTable('staff_schedules')) {
            Schema::rename('staff_schedules', 'employee_schedules');
        }

        // Renombrar columna staff_id a employee_id en employee_schedules
        if (Schema::hasTable('employee_schedules') && Schema::hasColumn('employee_schedules', 'staff_id')) {
            Schema::table('employee_schedules', function (Blueprint $table) {
                $table->renameColumn('staff_id', 'employee_id');
            });
            
            Schema::table('employee_schedules', function (Blueprint $table) {
                $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            });
        }

        // Renombrar original_staff_id si existe
        if (Schema::hasTable('employee_schedules') && Schema::hasColumn('employee_schedules', 'original_staff_id')) {
            Schema::table('employee_schedules', function (Blueprint $table) {
                $table->renameColumn('original_staff_id', 'original_employee_id');
            });
            
            Schema::table('employee_schedules', function (Blueprint $table) {
                $table->foreign('original_employee_id')->references('id')->on('employees')->onDelete('set null');
            });
        }
    }

    public function down(): void
    {
        Schema::rename('employee_schedules', 'staff_schedules');
        
        Schema::table('staff_schedules', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->renameColumn('employee_id', 'staff_id');
        });
        
        Schema::table('staff_schedules', function (Blueprint $table) {
            $table->foreign('staff_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }
};