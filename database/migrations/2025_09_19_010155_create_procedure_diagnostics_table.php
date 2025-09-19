<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procedure_diagnostics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procedure_id')->constrained()->onDelete('cascade');
            $table->foreignId('diagnostic_id')->constrained('diagnostic_standards')->onDelete('cascade');
            $table->foreignId('procedure_detail_id')->nullable()->constrained()->onDelete('set null');
            $table->string('description')->nullable();
            $table->string('type')->nullable();
            $table->string('severity')->nullable();
            $table->boolean('chronic')->default(false);
            $table->string('standard')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            // Indexes para análisis médico
            $table->index(['procedure_id', 'active']);
            $table->index(['diagnostic_id', 'active']);
            $table->index(['chronic', 'severity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procedure_diagnostics');
    }
};
