<?php
// database/migrations/2025_10_28_000001_add_active_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'active')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('active')->default(true)->after('password');
                $table->index('active');
            });
            
            echo "✅ Columna 'active' agregada a la tabla users\n";
        } else {
            echo "ℹ️  Columna 'active' ya existe en users\n";
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'active')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex(['active']);
                $table->dropColumn('active');
            });
        }
    }
};