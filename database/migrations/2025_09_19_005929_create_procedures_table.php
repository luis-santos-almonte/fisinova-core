<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procedures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('procedure_type')->nullable(); // Temporal, se cambiará por procedure_type_id
            $table->foreignId('insurance_id')->nullable()->constrained()->onDelete('set null');
            $table->string('insurance_code')->nullable();
            $table->string('case_number')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            // Indexes
            $table->index(['patient_id', 'created_at']);
            $table->index(['employee_id', 'created_at']);
            $table->index(['appointment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procedures');
    }
};
