// database/migrations/2025_10_31_000001_add_diagnostic_procedure_support.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Las tablas ya existen, solo verificamos estructura

        // Asegurar que procedure_diagnostics tiene la estructura correcta
        if (Schema::hasTable('procedure_diagnostics')) {
            Schema::table('procedure_diagnostics', function (Blueprint $table) {
                if (!Schema::hasColumn('procedure_diagnostics', 'procedure_id')) {
                    $table->foreignId('procedure_id')->after('id')->constrained()->onDelete('cascade');
                }
                if (!Schema::hasColumn('procedure_diagnostics', 'diagnostic_id')) {
                    $table->foreignId('diagnostic_id')->after('procedure_id')->constrained('diagnostic_standards')->onDelete('cascade');
                }
            });
        }

        // Agregar índices para búsqueda
        Schema::table('diagnostic_standards', function (Blueprint $table) {
            if (!Schema::hasColumn('diagnostic_standards', 'description')) {
                $table->index('description');
            }
            if (!Schema::hasColumn('diagnostic_standards', 'code')) {
                $table->index('code');
            }
        });

        Schema::table('procedure_standards', function (Blueprint $table) {
            if (!Schema::hasColumn('procedure_standards', 'description')) {
                $table->index('description');
            }
            if (!Schema::hasColumn('procedure_standards', 'standard')) {
                $table->index('standard');
            }
        });
    }

    public function down(): void
    {
        // No eliminar nada, las tablas ya existían
    }
};
