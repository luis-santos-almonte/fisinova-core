<?php
// database/migrations/2025_09_18_183925_fix-procedure-table.php
// ACTUALIZADA para que funcione correctamente

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('procedures', function (Blueprint $table) {
            // Eliminar columna procedure_type si existe (string)
            if (Schema::hasColumn('procedures', 'procedure_type')) {
                $table->dropColumn('procedure_type');
            }
            
            // Agregar procedure_type_id si no existe (foreign key)
            if (!Schema::hasColumn('procedures', 'procedure_type_id')) {
                $table->foreignId('procedure_type_id')->nullable()->constrained('procedure_types');
            }

            // Agregar columnas que faltan del modelo
            if (!Schema::hasColumn('procedures', 'authorization_code')) {
                $table->string('authorization_code')->nullable()->after('notes');
            }
            
            if (!Schema::hasColumn('procedures', 'dni')) {
                $table->string('dni')->nullable()->after('authorization_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('procedures', function (Blueprint $table) {
            // Eliminar foreign key y columna procedure_type_id
            if (Schema::hasColumn('procedures', 'procedure_type_id')) {
                $table->dropForeign(['procedure_type_id']);
                $table->dropColumn('procedure_type_id');
            }
            
            // Restaurar columna procedure_type string
            if (!Schema::hasColumn('procedures', 'procedure_type')) {
                $table->string('procedure_type')->nullable();
            }

            // Eliminar columnas agregadas
            if (Schema::hasColumn('procedures', 'authorization_code')) {
                $table->dropColumn('authorization_code');
            }
            
            if (Schema::hasColumn('procedures', 'dni')) {
                $table->dropColumn('dni');
            }
        });
    }
};