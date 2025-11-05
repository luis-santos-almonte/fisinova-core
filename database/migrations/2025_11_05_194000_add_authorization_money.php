<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('authorizations', function (Blueprint $table) {
            // Montos financieros
            $table->decimal('insurance_amount', 10, 2)->default(0)->after('authorization_type');
            $table->decimal('patient_amount', 10, 2)->default(0)->after('insurance_amount');
            $table->decimal('total_amount', 10, 2)->default(0)->after('patient_amount');
            
            // Índices para reportes financieros
            $table->index(['insurance_id', 'authorization_date']);
            $table->index(['insurance_amount', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('authorizations', function (Blueprint $table) {
            $table->dropIndex(['insurance_id', 'authorization_date']);
            $table->dropIndex(['insurance_amount', 'created_at']);
            
            $table->dropColumn([
                'insurance_amount',
                'patient_amount', 
                'total_amount'
            ]);
        });
    }
};