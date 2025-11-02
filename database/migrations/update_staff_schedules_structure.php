<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        echo "🔄 Actualizando estructura de staff_schedules...\n";

        Schema::table('staff_schedules', function (Blueprint $table) {
            // ========== PASO 1: Eliminar schedule_day_id ==========
            if (Schema::hasColumn('staff_schedules', 'schedule_day_id')) {
                echo "  ➡️  Eliminando schedule_day_id...\n";
                
                // Verificar si existe la FK antes de eliminarla
                $foreignKeys = DB::select("
                    SELECT constraint_name 
                    FROM information_schema.table_constraints 
                    WHERE table_name = 'staff_schedules' 
                    AND constraint_type = 'FOREIGN KEY'
                    AND constraint_name LIKE '%schedule_day_id%'
                ");
                
                if (!empty($foreignKeys)) {
                    $table->dropForeign(['schedule_day_id']);
                }
                
                $table->dropColumn('schedule_day_id');
                echo "  ✅ schedule_day_id eliminado\n";
            }

            // ========== PASO 2: Agregar schedule_template_id ==========
            if (!Schema::hasColumn('staff_schedules', 'schedule_template_id')) {
                echo "  ➡️  Agregando schedule_template_id...\n";
                $table->foreignId('schedule_template_id')
                ->default(1)
                    ->after('staff_id')
                    ->constrained('schedule_templates')
                    ->onDelete('cascade');
                echo "  ✅ schedule_template_id agregado\n";
            }

            // ========== PASO 3: Agregar selected_days (JSON) ==========
            if (!Schema::hasColumn('staff_schedules', 'selected_days')) {
                echo "  ➡️  Agregando selected_days...\n";
                $table->json('selected_days')
                    ->nullable()
                    ->after('schedule_template_id')
                    ->comment('Array de días: [1,2,3] para Lun,Mar,Mie. NULL=todos');
                echo "  ✅ selected_days agregado\n";
            }

            // ========== PASO 4: Renombrar assignment_date a start_date ==========
            if (Schema::hasColumn('staff_schedules', 'assignment_date')) {
                echo "  ➡️  Renombrando assignment_date → start_date...\n";
                $table->renameColumn('assignment_date', 'start_date');
                echo "  ✅ Renombrado a start_date\n";
            } else if (!Schema::hasColumn('staff_schedules', 'start_date')) {
                echo "  ➡️  Creando start_date...\n";
                $table->date('start_date')
                    ->nullable()
                    ->after('selected_days')
                    ->comment('Fecha de inicio de vigencia');
                echo "  ✅ start_date creado\n";
            }

            // ========== PASO 5: Agregar campos para asignaciones específicas ==========
            if (!Schema::hasColumn('staff_schedules', 'specific_date')) {
                echo "  ➡️  Agregando campos para asignaciones específicas...\n";
                
                $table->date('specific_date')
                    ->nullable()
                    ->after('end_date')
                    ->comment('Para asignaciones puntuales (un solo día)');
                
                $table->time('specific_start_time')
                    ->nullable()
                    ->after('specific_date');
                
                $table->time('specific_end_time')
                    ->nullable()
                    ->after('specific_start_time');
                
                echo "  ✅ Campos específicos agregados\n";
            }
        });

        // ========== PASO 6: Actualizar índices ==========
        echo "  ➡️  Actualizando índices...\n";
        
        Schema::table('staff_schedules', function (Blueprint $table) {
            // Eliminar índices viejos si existen
            try {
                $table->dropIndex(['staff_id', 'status']);
            } catch (\Exception $e) {
                // El índice no existe, continuar
            }
            
            try {
                $table->dropIndex(['assignment_date', 'status']);
            } catch (\Exception $e) {
                // El índice no existe, continuar
            }

            // Crear nuevos índices
            $table->index(['staff_id', 'schedule_template_id'], 'staff_schedules_staff_template_idx');
            $table->index(['start_date', 'end_date'], 'staff_schedules_date_range_idx');
            $table->index('specific_date', 'staff_schedules_specific_date_idx');
        });

        echo "  ✅ Índices actualizados\n";
        echo "✅ Tabla staff_schedules actualizada exitosamente\n";
    }

    public function down(): void
    {
        echo "🔄 Revirtiendo cambios en staff_schedules...\n";

        Schema::table('staff_schedules', function (Blueprint $table) {
            // Eliminar índices nuevos
            try {
                $table->dropIndex('staff_schedules_staff_template_idx');
                $table->dropIndex('staff_schedules_date_range_idx');
                $table->dropIndex('staff_schedules_specific_date_idx');
            } catch (\Exception $e) {
                // Ignorar si no existen
            }

            // Eliminar columnas nuevas
            if (Schema::hasColumn('staff_schedules', 'schedule_template_id')) {
                $table->dropForeign(['schedule_template_id']);
                $table->dropColumn('schedule_template_id');
            }

            $table->dropColumn([
                'selected_days',
                'specific_date',
                'specific_start_time',
                'specific_end_time'
            ]);

            // Renombrar start_date de vuelta a assignment_date
            if (Schema::hasColumn('staff_schedules', 'start_date')) {
                $table->renameColumn('start_date', 'assignment_date');
            }

            // Restaurar schedule_day_id
            $table->foreignId('schedule_day_id')
                ->after('staff_id')
                ->constrained('schedule_days')
                ->onDelete('cascade');

            // Restaurar índices viejos
            $table->index(['staff_id', 'status']);
            $table->index(['assignment_date', 'status']);
        });

        echo "✅ Cambios revertidos\n";
    }
};