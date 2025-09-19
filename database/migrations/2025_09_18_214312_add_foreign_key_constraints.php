<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Check if a foreign key exists on a table.
     */
    protected function foreignKeyExists(string $table, string $foreignKey): bool
    {
        $connection = Schema::getConnection();
        $dbName = $connection->getDatabaseName();
        $result = $connection->select(
            "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?",
            [$dbName, $table, $foreignKey]
        );
        return !empty($result);
    }

    public function up(): void
    {
        // Check if tables exist first
        if (Schema::hasTable('insurances')) {
            Schema::table('patients', function (Blueprint $table) {
                if (
                    Schema::hasColumn('patients', 'insurance_id') &&
                    !$this->foreignKeyExists('patients', 'patients_insurance_id_foreign')
                ) {
                    $table->foreign('insurance_id')->references('id')->on('insurances');
                }
            });
        }

        if (Schema::hasTable('users') && Schema::hasTable('employees')) {
            Schema::table('employees', function (Blueprint $table) {
                if (
                    Schema::hasColumn('employees', 'user_id') &&
                    !$this->foreignKeyExists('employees', 'employees_user_id_foreign')
                ) {
                    $table->foreign('user_id')->references('id')->on('users');
                }
            });
        }

        if (Schema::hasTable('positions') && Schema::hasTable('employees')) {
            Schema::table('employees', function (Blueprint $table) {
                if (
                    Schema::hasColumn('employees', 'position_id') &&
                    !$this->foreignKeyExists('employees', 'employees_position_id_foreign')
                ) {
                    $table->foreign('position_id')->references('id')->on('positions');
                }
            });
        }

        if (Schema::hasTable('procedures') && Schema::hasTable('procedure_types')) {
            Schema::table('procedures', function (Blueprint $table) {
                if (!Schema::hasColumn('procedures', 'procedure_type_id')) {
                    $table->unsignedBigInteger('procedure_type_id')->nullable();
                }

                if (
                    Schema::hasColumn('procedures', 'procedure_type_id') &&
                    !$this->foreignKeyExists('procedures', 'procedures_procedure_type_id_foreign')
                ) {
                    $table->foreign('procedure_type_id')->references('id')->on('procedure_types');
                }
            });
        }
    }
};
