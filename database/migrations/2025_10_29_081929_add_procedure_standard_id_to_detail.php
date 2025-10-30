<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('procedure_details', function (Blueprint $table) {
            if (!Schema::hasColumn('procedure_details', 'procedure_standard_id')) {
                $table->foreignId('procedure_standard_id')
                    ->nullable()
                    ->after('procedure_id')
                    ->constrained('procedure_standards')
                    ->onDelete('set null');
            }
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('procedure_details', function (Blueprint $table) {
            $table->dropForeign(['procedure_standard_id']);
            $table->dropColumn('procedure_standard_id');
        });
    }
};
