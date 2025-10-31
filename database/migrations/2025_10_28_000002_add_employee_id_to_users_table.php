<?php
// database/migrations/2025_10_28_000002_add_employee_id_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'employee_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('employee_id')
                    ->nullable()
                    ->after('password')
                    ->constrained('employees')
                    ->onDelete('set null');
                
                $table->unique('employee_id');
                $table->index('employee_id');
            });
            
            echo "✅ Columna 'employee_id' agregada a la tabla users\n";
        } else {
            echo "ℹ️  Columna 'employee_id' ya existe en users\n";
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'employee_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['employee_id']);
                $table->dropUnique(['employee_id']);
                $table->dropIndex(['employee_id']);
                $table->dropColumn('employee_id');
            });
        }
    }
};