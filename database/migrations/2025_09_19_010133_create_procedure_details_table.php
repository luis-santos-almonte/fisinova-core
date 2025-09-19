<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procedure_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('procedure_id')->constrained()->onDelete('cascade');
            $table->foreignId('procedure_standard_id')->nullable()->constrained()->onDelete('set null');
            $table->string('description');
            $table->text('notes')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            // Indexes para reportes financieros
            $table->index(['procedure_id', 'active']);
            $table->index(['procedure_standard_id', 'active']);
            $table->index(['amount', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procedure_details');
    }
};
