<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Agregar FK consultation_appointment_id a appointments
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('consultation_appointment_id')->nullable()->after('id');
            $table->foreign('consultation_appointment_id')->references('id')->on('appointments')->onDelete('set null');
            $table->foreignId('procedure_detail_id')->nullable()->after('consultation_appointment_id');
            $table->foreign('procedure_detail_id')->references('id')->on('procedure_details')->onDelete('set null');
        });

        // 2. Agregar procedure_id a medical_records
        Schema::table('medical_records', function (Blueprint $table) {
            $table->foreignId('procedure_id')->nullable()->after('employee_id');
            $table->foreign('procedure_id')->references('id')->on('procedures')->onDelete('set null');
        });

        // 3. Agregar campos de control a procedure_details
        Schema::table('procedure_details', function (Blueprint $table) {
            $table->integer('sessions_authorized')->default(1)->after('procedure_standard_id');
            $table->integer('sessions_completed')->default(0)->after('sessions_authorized');
            $table->string('status', 50)->default('pending')->after('sessions_completed');
        });

        // 4. Asegurar que procedures tiene appointment_id
        if (!Schema::hasColumn('procedures', 'appointment_id')) {
            Schema::table('procedures', function (Blueprint $table) {
                $table->foreignId('appointment_id')->nullable()->after('id');
                $table->foreign('appointment_id')->references('id')->on('appointments')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['consultation_appointment_id']);
            $table->dropColumn('consultation_appointment_id');
            $table->dropForeign(['procedure_detail_id']);
            $table->dropColumn(['procedure_detail_id', 'session_number', 'total_sessions']);
        });

        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropForeign(['procedure_id']);
            $table->dropColumn('procedure_id');
        });

        Schema::table('procedure_details', function (Blueprint $table) {
            $table->dropColumn(['sessions_authorized', 'sessions_completed', 'status']);
        });
    }
};
