// database/migrations/2025_11_02_000001_add_therapy_fields_to_medical_records.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->boolean('requires_therapy')->default(false)->after('general_notes');
            $table->integer('therapy_sessions_needed')->nullable()->after('requires_therapy');
            $table->text('therapy_reason')->nullable()->after('therapy_sessions_needed');
        });
    }

    public function down(): void
    {
        Schema::table('medical_records', function (Blueprint $table) {
            $table->dropColumn(['requires_therapy', 'therapy_sessions_needed', 'therapy_reason']);
        });
    }
};