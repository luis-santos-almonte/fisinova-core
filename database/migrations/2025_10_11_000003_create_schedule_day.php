<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('schedule_days')) {
            Schema::create('schedule_days', function (Blueprint $table) {
                $table->id();
                $table->foreignId('schedule_template_id')->constrained('schedule_templates')->onDelete('cascade');
                $table->integer('day_of_week')->nullable()->comment('1=Lunes, 2=Martes, etc. NULL para flexibles');
                $table->time('start_time');
                $table->time('end_time');
                $table->boolean('is_recurring')->default(true);
                $table->timestamps();
                
                $table->index(['schedule_template_id', 'day_of_week']);
            });

            echo "✅ Tabla schedule_days creada\n";
        } else {
            echo "ℹ️  Tabla schedule_days ya existe\n";
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('schedule_days');
    }
};