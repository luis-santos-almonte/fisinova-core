<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('authorizations', function (Blueprint $table) {
            $table->json('diagnosis_codes')->nullable()->after('services_authorized');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('authorizations', function (Blueprint $table) {
            $table->dropColumn('diagnosis_codes');
        });
    }
};
