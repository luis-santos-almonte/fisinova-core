<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('staff')) {
            Schema::create('staff', function (Blueprint $table) {
                $table->id();
                $table->string('first_name', 100);
                $table->string('last_name', 100);
                $table->string('email', 255)->unique()->nullable();
                $table->string('phone', 20)->nullable();
                $table->foreignId('position_id')->constrained('positions')->onDelete('restrict');
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->index('is_active');
                $table->index(['first_name', 'last_name']);
                $table->index('position_id');
            });

            echo "✅ Tabla staff creada\n";
        } else {
            echo "ℹ️  Tabla staff ya existe\n";
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};