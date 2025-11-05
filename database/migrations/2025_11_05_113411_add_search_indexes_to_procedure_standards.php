<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Habilitar la extensión pg_trgm (si no existe)
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm;');

        // Índice trigram (GIN) para description
        DB::statement(<<<SQL
            CREATE INDEX IF NOT EXISTS idx_procedure_description_trgm
            ON procedure_standards
            USING gin (description gin_trgm_ops);
        SQL
        );

        // Índices de expresión LOWER(...) para acelerar ILIKE en standard y category
        DB::statement(<<<SQL
            CREATE INDEX IF NOT EXISTS idx_procedure_standard_lower
            ON procedure_standards (LOWER(standard));
        SQL
        );

        DB::statement(<<<SQL
            CREATE INDEX IF NOT EXISTS idx_procedure_category_lower
            ON procedure_standards (LOWER(category));
        SQL
        );
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_procedure_description_trgm;');
        DB::statement('DROP INDEX IF EXISTS idx_procedure_standard_lower;');
        DB::statement('DROP INDEX IF EXISTS idx_procedure_category_lower;');
    }
};
