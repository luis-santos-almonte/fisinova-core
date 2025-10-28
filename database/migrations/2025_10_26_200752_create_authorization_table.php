<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('authorizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained()->onDelete('cascade');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('insurance_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('medic_id')->constrained('employees')->onDelete('cascade');
            
            $table->string('patient_name');
            $table->string('patient_last_name')->nullable();
            $table->string('patient_dni')->nullable();
            $table->string('patient_insurance_code')->nullable();
            $table->string('patient_gender')->nullable();
            $table->string('city')->nullable();


            $table->string('authorization_number')->unique();
            $table->string('authorization_type')->nullable();
            $table->date('authorization_date');
            $table->string('PSS_code')->nullable();
            $table->string('stablishment_phone')->nullable();
            $table->string('medic_name')->nullable();
            $table->string('medic_specialty')->nullable();
            

            $table->text('notes')->nullable();
            $table->text('services_authorized')->nullable();

            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['appointment_id', 'active']);
            $table->index(['patient_id', 'authorization_date']);
            $table->index(['insurance_id', 'authorization_date']);
            $table->index('authorization_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('authorizations');
    }
};
