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
        Schema::table('appointments', function (Blueprint $table) {
            // Agregar columnas user_id y position_id si no existen
            if (!Schema::hasColumn('appointments', 'guest_firstname')) {
                $table->string('guest_firstname')->nullable()->after('guest_lastname');
            }
            if (!Schema::hasColumn('appointments', 'guest_lastname')) {
                $table->string('guest_lastname')->nullable()->after('guest_firstname');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'guest_firstname')) {
                $table->dropColumn('guest_firstname');
            }
            if (Schema::hasColumn('appointments', 'guest_lastname')) {
                $table->dropColumn('guest_lastname');
            }
        });
    }
};
