<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('therapy_records', function (Blueprint $table) {
            // Campos de autorización y cobro
            $table->string('authorization_number')->nullable()->after('authorization_id');
            $table->date('authorization_date')->nullable()->after('authorization_number');
            $table->decimal('insurance_amount', 10, 2)->default(0)->after('authorization_date');
            $table->decimal('patient_amount', 10, 2)->default(0)->after('insurance_amount');
            $table->decimal('total_amount', 10, 2)->default(0)->after('patient_amount');
            
            // Procedimientos seleccionados de la consulta (IDs de procedure_details)
            $table->json('selected_procedure_detail_ids')->nullable()->after('procedure_ids');
            
            // Estado de autorización
            $table->boolean('is_authorized')->default(false)->after('total_amount');
            $table->timestamp('authorized_at')->nullable()->after('is_authorized');
            $table->foreignId('authorized_by')->nullable()->after('authorized_at');
            
            $table->foreign('authorized_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('therapy_records', function (Blueprint $table) {
            $table->dropForeign(['authorized_by']);
            $table->dropColumn([
                'authorization_number',
                'authorization_date',
                'insurance_amount',
                'patient_amount',
                'total_amount',
                'selected_procedure_detail_ids',
                'is_authorized',
                'authorized_at',
                'authorized_by',
            ]);
        });
    }
};
