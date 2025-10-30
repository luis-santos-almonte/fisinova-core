<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('schedule_templates')) {
            Schema::create('schedule_templates', function (Blueprint $table) {
                $table->id();
                $table->string('name', 100);
                $table->text('description')->nullable();
                $table->timestamps();
                
                $table->index('name');
            });

            echo "✅ Tabla schedule_templates creada\n";
        } else {
            echo "ℹ️  Tabla schedule_templates ya existe\n";
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_templates');
    }
};