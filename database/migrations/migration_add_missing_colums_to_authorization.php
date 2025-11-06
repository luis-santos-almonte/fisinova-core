<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primero, verificar qué columnas existen
        $columns = Schema::getColumnListing('authorizations');
        
        Schema::table('authorizations', function (Blueprint $table) use ($columns) {
            // 1. case_number - Para IDOPPRIL/Riesgo laboral
            if (!in_array('case_number', $columns)) {
                $table->string('case_number', 255)->nullable()->after('authorization_number');
            }

            // 2. payment_type - Para distinguir tipos de pago
            if (!in_array('payment_type', $columns)) {
                $table->enum('payment_type', ['insurance', 'private', 'workplace_risk'])
                    ->default('insurance')
                    ->after('authorization_type');
            }

            // 3. insurance_amount
            if (!in_array('insurance_amount', $columns)) {
                $table->decimal('insurance_amount', 10, 2)->default(0)->after('payment_type');
            }

            // 4. patient_amount
            if (!in_array('patient_amount', $columns)) {
                $table->decimal('patient_amount', 10, 2)->default(0)->after('insurance_amount');
            }

            // 5. total_amount
            if (!in_array('total_amount', $columns)) {
                $table->decimal('total_amount', 10, 2)->default(0)->after('patient_amount');
            }

            // 6. sessions_authorized
            if (!in_array('sessions_authorized', $columns)) {
                $table->integer('sessions_authorized')->default(0)->after('total_amount');
            }

            // 7. sessions_completed
            if (!in_array('sessions_completed', $columns)) {
                $table->integer('sessions_completed')->default(0)->after('sessions_authorized');
            }
        });

        // Agregar índices para mejorar búsquedas
        Schema::table('authorizations', function (Blueprint $table) {
            if (!$this->indexExists('authorizations', 'authorizations_case_number_index')) {
                $table->index('case_number');
            }
            if (!$this->indexExists('authorizations', 'authorizations_payment_type_index')) {
                $table->index('payment_type');
            }
            if (!$this->indexExists('authorizations', 'authorizations_authorization_date_index')) {
                $table->index('authorization_date');
            }
        });

        // Actualizar payment_type para registros existentes con case_number
        DB::statement("
            UPDATE authorizations 
            SET payment_type = 'workplace_risk' 
            WHERE case_number IS NOT NULL 
            AND payment_type != 'workplace_risk'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('authorizations', function (Blueprint $table) {
            // Eliminar índices
            if ($this->indexExists('authorizations', 'authorizations_case_number_index')) {
                $table->dropIndex(['case_number']);
            }
            if ($this->indexExists('authorizations', 'authorizations_payment_type_index')) {
                $table->dropIndex(['payment_type']);
            }
            if ($this->indexExists('authorizations', 'authorizations_authorization_date_index')) {
                $table->dropIndex(['authorization_date']);
            }

            // Eliminar columnas
            $columns = ['case_number', 'payment_type'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('authorizations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Verificar si un índice existe
     */
    private function indexExists(string $table, string $index): bool
    {
        $indexes = DB::select("
            SELECT indexname 
            FROM pg_indexes 
            WHERE tablename = ? 
            AND indexname = ?
        ", [$table, $index]);

        return count($indexes) > 0;
    }
};