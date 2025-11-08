<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // PASO 1: Verificar si la columna existe
        $columnExists = DB::select("
            SELECT column_name 
            FROM information_schema.columns 
            WHERE table_name = 'employee_schedules' 
            AND column_name = 'schedule_day_id'
        ");

        if (empty($columnExists)) {
            echo "ℹ️  schedule_day_id ya no existe\n";
            return;
        }

        // PASO 2: Hacer la columna nullable
        DB::statement('ALTER TABLE employee_schedules ALTER COLUMN schedule_day_id DROP NOT NULL');
        echo "✅ schedule_day_id ahora es nullable\n";

        // PASO 3: Buscar TODAS las FK que referencien schedule_day_id
        $foreignKeys = DB::select("
            SELECT con.conname as constraint_name
            FROM pg_constraint con
            INNER JOIN pg_class rel ON rel.oid = con.conrelid
            INNER JOIN pg_attribute att ON att.attrelid = con.conrelid 
                AND att.attnum = ANY(con.conkey)
            WHERE rel.relname = 'employee_schedules'
            AND att.attname = 'schedule_day_id'
            AND con.contype = 'f'
        ");
        
        foreach ($foreignKeys as $fk) {
            try {
                DB::statement("ALTER TABLE employee_schedules DROP CONSTRAINT {$fk->constraint_name}");
                echo "✅ FK {$fk->constraint_name} eliminada\n";
            } catch (\Exception $e) {
                echo "⚠️  No se pudo eliminar {$fk->constraint_name}: {$e->getMessage()}\n";
            }
        }

        // PASO 4: Eliminar la columna
        DB::statement('ALTER TABLE employee_schedules DROP COLUMN schedule_day_id CASCADE');
        echo "✅ schedule_day_id eliminado con CASCADE\n";
    }

    public function down(): void
    {
        Schema::table('employee_schedules', function (Blueprint $table) {
            $table->foreignId('schedule_day_id')
                ->nullable()
                ->after('employee_id');
        });
    }
};