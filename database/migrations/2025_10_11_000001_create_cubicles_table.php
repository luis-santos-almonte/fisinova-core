<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cubicles')) {
            Schema::create('cubicles', function (Blueprint $table) {
                $table->id();
                $table->string('code', 20)->unique();
                $table->string('name', 100);
                $table->string('location', 200)->nullable();
                $table->integer('capacity')->default(1);
                $table->json('features')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->index('is_active');
                $table->index('code');
            });

            echo "✅ Tabla cubicles creada\n";
        } else {
            echo "ℹ️  Tabla cubicles ya existe\n";
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cubicles');
    }
};