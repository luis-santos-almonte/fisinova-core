<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('procedures') && !Schema::hasColumn('procedures', 'appointment_id')) {
            Schema::table('procedures', function (Blueprint $table) {
                $table->foreignId('appointment_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('appointments')
                    ->onDelete('set null');

                $table->index(['appointment_id', 'active']);
            });

            echo "✅ Columna appointment_id agregada a la tabla procedures\n";
        } else {
            echo "ℹ️ La columna appointment_id ya existe en procedures\n";
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('procedures') && Schema::hasColumn('procedures', 'appointment_id')) {
            Schema::table('procedures', function (Blueprint $table) {
                // Eliminar el índice primero
                $table->dropIndex(['appointment_id', 'active']);

                // Eliminar la foreign key constraint
                $table->dropForeign(['appointment_id']);

                // Eliminar la columna
                $table->dropColumn('appointment_id');
            });

            echo "✅ Columna appointment_id eliminada de la tabla procedures\n";
        }
    }
};
