<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Primero eliminamos la foreign key existente
        Schema::table('staff_schedules', function (Blueprint $table) {
            // Eliminar FK de staff_id
            $table->dropForeign(['staff_id']);
            
            // Eliminar FK de original_staff_id
            $table->dropForeign(['original_staff_id']);
        });

        // 2. Renombrar las columnas para mayor claridad (opcional pero recomendado)
        // Schema::table('staff_schedules', function (Blueprint $table) {
        //     // Renombrar staff_id a employee_id
        //     $table->renameColumn('staff_id', 'employee_id');
            
        //     // Renombrar original_staff_id a original_employee_id
        //     $table->renameColumn('original_staff_id', 'original_employee_id');
        // });

        // 3. Agregar las nuevas foreign keys hacia employees
        Schema::table('staff_schedules', function (Blueprint $table) {
            // FK para employee_id
            $table->foreign('staff_id')
                ->references('id')
                ->on('employees')
                ->onDelete('cascade');
            
            // FK para original_employee_id
            $table->foreign('original_staff_id')
                ->references('id')
                ->on('employees')
                ->onDelete('set null');
        });

        echo "✅ Foreign keys de staff_schedules actualizadas a employees\n";
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir los cambios en orden inverso
        
        // 1. Eliminar las FK actuales
        Schema::table('staff_schedules', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['original_employee_id']);
        });

        // 2. Renombrar de vuelta las columnas
        Schema::table('staff_schedules', function (Blueprint $table) {
            $table->renameColumn('employee_id', 'staff_id');
            $table->renameColumn('original_employee_id', 'original_staff_id');
        });

        // 3. Restaurar las FK originales hacia staff
        Schema::table('staff_schedules', function (Blueprint $table) {
            $table->foreign('staff_id')
                ->references('id')
                ->on('staff')
                ->onDelete('cascade');
            
            $table->foreign('original_staff_id')
                ->references('id')
                ->on('staff')
                ->onDelete('set null');
        });
    }
};