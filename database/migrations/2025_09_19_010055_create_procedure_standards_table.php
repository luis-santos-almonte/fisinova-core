<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('procedure_standards', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->string('category')->nullable();
            $table->string('standard')->nullable()->index();
            $table->boolean('active')->default(true);
            $table->timestamps();

            // Indexes para búsquedas comunes
            $table->index(['active', 'category']);
            $table->index(['standard', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('procedure_standards');
    }
};
