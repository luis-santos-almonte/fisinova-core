<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('therapy_records', function (Blueprint $table) {
            // Solo agregar procedimientos seleccionados (qué se realizó)
            $table->json('selected_procedure_detail_ids')->nullable()->after('procedure_ids');
        });
    }

    public function down(): void
    {
        Schema::table('therapy_records', function (Blueprint $table) {
            $table->dropColumn('selected_procedure_detail_ids');
        });
    }
};