<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('authorizations', function (Blueprint $table) {
            if (!Schema::hasColumn('authorizations', 'sessions_authorized')) {
                $table->integer('sessions_authorized')->nullable()->after('services_authorized');
            }
            if (!Schema::hasColumn('authorizations', 'sessions_completed')) {
                $table->integer('sessions_completed')->default(0)->after('sessions_authorized');
            }
        });
    }

    public function down(): void
    {
        Schema::table('authorizations', function (Blueprint $table) {
            if (Schema::hasColumn('authorizations', 'sessions_authorized')) {
                $table->dropColumn('sessions_authorized');
            }
            if (Schema::hasColumn('authorizations', 'sessions_completed')) {
                $table->dropColumn('sessions_completed');
            }
        });
    }
};
