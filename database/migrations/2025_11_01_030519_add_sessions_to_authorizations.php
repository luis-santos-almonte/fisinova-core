<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'session_number')) {
                $table->integer('session_number')->nullable()->after('type');
            }
            if (!Schema::hasColumn('appointments', 'total_sessions')) {
                $table->integer('total_sessions')->nullable()->after('session_number');
            }
            if (!Schema::hasColumn('appointments', 'authorization_id')) {
                $table->foreignId('authorization_id')->nullable()->constrained()->onDelete('set null')->after('insurance_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'authorization_id')) {
                $table->dropForeign(['authorization_id']);
                $table->dropColumn('authorization_id');
            }
            if (Schema::hasColumn('appointments', 'session_number')) {
                $table->dropColumn('session_number');
            }
            if (Schema::hasColumn('appointments', 'total_sessions')) {
                $table->dropColumn('total_sessions');
            }
        });
    }
};