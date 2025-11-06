<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Hacer description nullable
        DB::statement('ALTER TABLE procedure_details ALTER COLUMN description DROP NOT NULL');
        
        echo "✅ description ahora es nullable en procedure_details\n";
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE procedure_details ALTER COLUMN description SET NOT NULL');
    }
};