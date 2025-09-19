<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Crear tabla insurances primero
        if (!Schema::hasTable('insurances')) {
            Schema::create('insurances', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('provider_code');
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        // Crear tabla positions
        if (!Schema::hasTable('positions')) {
            Schema::create('positions', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('description')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        // Crear tabla procedure_types
        if (!Schema::hasTable('procedure_types')) {
            Schema::create('procedure_types', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('description')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        // Ahora modificar la tabla patients
        Schema::table('patients', function (Blueprint $table) {
            if (!Schema::hasColumn('patients', 'insurance_code')) {
                $table->string('insurance_code')->nullable();
            }
            if (!Schema::hasColumn('patients', 'insurance_id')) {
                $table->foreignId('insurance_id')->nullable()->constrained();
            }
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            if (Schema::hasColumn('patients', 'insurance_id')) {
                $table->dropForeign(['insurance_id']);
                $table->dropColumn('insurance_id');
            }
            if (Schema::hasColumn('patients', 'insurance_code')) {
                $table->dropColumn('insurance_code');
            }
        });

        Schema::dropIfExists('procedure_types');
        Schema::dropIfExists('positions');
        Schema::dropIfExists('insurances');
    }
};