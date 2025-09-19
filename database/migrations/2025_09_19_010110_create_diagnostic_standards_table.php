<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('diagnostic_standards', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->string('category')->nullable();
            $table->string('standard')->nullable();
            $table->string('grade')->nullable();
            $table->boolean('chronic')->default(false);
            $table->string('type')->nullable();
            $table->string('code')->nullable()->unique();
            $table->boolean('active')->default(true);
            $table->timestamps();

            // Indexes para búsquedas médicas
            $table->index(['code', 'active']);
            $table->index(['category', 'active']);
            $table->index(['chronic', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('diagnostic_standards');
    }
};
