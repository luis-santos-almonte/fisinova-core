<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('procedures')) {
            Schema::table('procedures', function (Blueprint $table) {
                // Eliminar columna procedure_type si existe (string)
                if (Schema::hasColumn('procedures', 'procedure_type')) {
                    $table->dropColumn('procedure_type');
                }
                
                // Agregar procedure_type_id si no existe (foreign key)
                if (!Schema::hasColumn('procedures', 'procedure_type_id')) {
                    $table->foreignId('procedure_type_id')->nullable()->constrained('procedure_types');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('procedures')) {
            Schema::table('procedures', function (Blueprint $table) {
                if (Schema::hasColumn('procedures', 'procedure_type_id')) {
                    $table->dropForeign(['procedure_type_id']);
                    $table->dropColumn('procedure_type_id');
                }
                
                if (!Schema::hasColumn('procedures', 'procedure_type')) {
                    $table->string('procedure_type')->nullable();
                }
            });
        }
    }
};