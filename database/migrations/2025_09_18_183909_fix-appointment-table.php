<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        if (!Schema::hasTable('employees')) {
            Schema::create('employees', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained();
                $table->foreignId('position_id')->nullable()->constrained('positions');
                $table->string('firstname');
                $table->string('lastname');
                $table->string('dni')->nullable();
                $table->string('cellphone')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->string('address')->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('appointments')) {
            Schema::table('appointments', function (Blueprint $table) {
                // Agregar columnas que faltan si no existen
                if (!Schema::hasColumn('appointments', 'start_time')) {
                    $table->time('start_time')->after('appointment_date');
                }
                if (!Schema::hasColumn('appointments', 'end_time')) {
                    $table->time('end_time')->after('start_time');
                }
                if (!Schema::hasColumn('appointments', 'status')) {
                    $table->string('status')->default('scheduled')->after('end_time');
                }

                // Eliminar columnas viejas si existen
                if (Schema::hasColumn('appointments', 'hour_start')) {
                    $table->dropColumn(['hour_start', 'minute_start', 'hour_end', 'minute_end']);
                }
            });
        } else {
            // Si no existe la tabla appointments, crearla completa
            Schema::create('appointments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('employee_id')->constrained()->onDelete('cascade');
                $table->foreignId('patient_id')->nullable()->constrained()->onDelete('cascade');
                $table->date('appointment_date');
                $table->time('start_time');
                $table->time('end_time');
                $table->string('status')->default('scheduled');
                $table->text('notes')->nullable();
                $table->string('dni')->nullable();
                $table->string('phone')->nullable();
                $table->string('passport')->nullable();
                $table->string('insurance_code')->nullable();
                $table->foreignId('insurance_id')->nullable()->constrained();
                $table->boolean('active')->default(true);
                $table->timestamps();

                // Indexes para mejor performance
                $table->index(['appointment_date', 'employee_id']);
                $table->index(['patient_id', 'appointment_date']);
                $table->index(['status', 'appointment_date']);
            });
        }
    }


    public function down(): void
    {
        if (Schema::hasTable('appointments')) {
            Schema::table('appointments', function (Blueprint $table) {
                // Si se crearon las columnas nuevas, eliminarlas
                if (Schema::hasColumn('appointments', 'start_time')) {
                    $table->dropColumn(['start_time', 'end_time', 'status']);
                }

                // Restaurar columnas viejas si es necesario
                if (!Schema::hasColumn('appointments', 'hour_start')) {
                    $table->integer('hour_start')->nullable();
                    $table->integer('minute_start')->nullable();
                    $table->integer('hour_end')->nullable();
                    $table->integer('minute_end')->nullable();
                }
            });
        }
    }
};
