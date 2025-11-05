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

        // Índice trigram (GIN) para búsquedas parciales en description
        DB::statement(<<<SQL
            CREATE INDEX IF NOT EXISTS idx_diagnostic_description_trgm
            ON diagnostic_standards
            USING gin (description gin_trgm_ops);
        SQL
        );

        // Índices de expresión LOWER(...) para acelerar ILIKE en code y category
        DB::statement(<<<SQL
            CREATE INDEX IF NOT EXISTS idx_diagnostic_code_lower
            ON diagnostic_standards (LOWER(code));
        SQL
        );

        DB::statement(<<<SQL
            CREATE INDEX IF NOT EXISTS idx_diagnostic_category_lower
            ON diagnostic_standards (LOWER(category));
        SQL
        );

        // Índice para el filtro booleano chronic (si existe la columna)
        // Si no tienes chronic, la creación fallará; en caso de duda, comprobar columna antes.
        $hasChronic = (bool) DB::selectOne("SELECT 1 FROM information_schema.columns WHERE table_name='diagnostic_standards' AND column_name='chronic'");
        if ($hasChronic) {
            DB::statement(<<<SQL
                CREATE INDEX IF NOT EXISTS idx_diagnostic_chronic
                ON diagnostic_standards (chronic);
            SQL
            );
        }
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS idx_diagnostic_description_trgm;');
        DB::statement('DROP INDEX IF EXISTS idx_diagnostic_code_lower;');
        DB::statement('DROP INDEX IF EXISTS idx_diagnostic_category_lower;');
        DB::statement('DROP INDEX IF EXISTS idx_diagnostic_chronic;');

        // No borramos la extensión pg_trgm porque puede ser usada por otros objetos.
    }
};
