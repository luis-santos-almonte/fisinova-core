<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Agregar columna case_number para riesgo laboral
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('case_number')->nullable()->after('authorization_number');
            $table->index('case_number');
        });

        // Actualizar el enum de payment_type para incluir workplace_risk
        DB::statement("ALTER TABLE appointments DROP CONSTRAINT IF EXISTS appointments_payment_type_check");
        DB::statement("ALTER TABLE appointments ALTER COLUMN payment_type TYPE VARCHAR(50)");
        
        // Recrear constraint con los tres valores
        DB::statement("
            ALTER TABLE appointments 
            ADD CONSTRAINT appointments_payment_type_check 
            CHECK (payment_type IN ('insurance', 'private', 'workplace_risk'))
        ");
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['case_number']);
            $table->dropColumn('case_number');
        });

        // Restaurar enum original
        DB::statement("ALTER TABLE appointments DROP CONSTRAINT IF EXISTS appointments_payment_type_check");
        DB::statement("
            ALTER TABLE appointments 
            ADD CONSTRAINT appointments_payment_type_check 
            CHECK (payment_type IN ('insurance', 'private'))
        ");
    }
};