<?php
// database/migrations/2025_09_18_214312_add_foreign_key_constraints.php
// ARREGLADA para verificar que no existan constraints antes de crearlas

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Check if a foreign key constraint exists
     */
    protected function foreignKeyExists(string $table, string $column, string $referencedTable): bool
    {
        $connection = Schema::getConnection();
        
        // Para PostgreSQL
        if ($connection->getDriverName() === 'pgsql') {
            $result = $connection->select("
                SELECT constraint_name 
                FROM information_schema.table_constraints 
                WHERE table_name = ? 
                AND constraint_type = 'FOREIGN KEY'
                AND constraint_name LIKE ?
            ", [$table, "%{$column}_foreign%"]);
            
            return !empty($result);
        }
        
        // Para MySQL/MariaDB
        if (in_array($connection->getDriverName(), ['mysql', 'mariadb'])) {
            $dbName = $connection->getDatabaseName();
            $result = $connection->select("
                SELECT CONSTRAINT_NAME 
                FROM information_schema.KEY_COLUMN_USAGE 
                WHERE TABLE_SCHEMA = ? 
                AND TABLE_NAME = ? 
                AND COLUMN_NAME = ?
                AND REFERENCED_TABLE_NAME IS NOT NULL
            ", [$dbName, $table, $column]);
            
            return !empty($result);
        }
        
        // Para SQLite (no tiene constraints nombradas, siempre devolver false)
        return false;
    }

    public function up(): void
    {
        echo "🔍 Verificando foreign keys existentes...\n";

        // 1. Patients -> Insurances
        if (Schema::hasTable('patients') && Schema::hasTable('insurances')) {
            if (Schema::hasColumn('patients', 'insurance_id') && 
                !$this->foreignKeyExists('patients', 'insurance_id', 'insurances')) {
                
                Schema::table('patients', function (Blueprint $table) {
                    $table->foreign('insurance_id')->references('id')->on('insurances');
                });
                echo "✅ Foreign key patients.insurance_id creada\n";
            } else {
                echo "ℹ️  Foreign key patients.insurance_id ya existe\n";
            }
        }

        // 2. Employees -> Users
        if (Schema::hasTable('employees') && Schema::hasTable('users')) {
            if (Schema::hasColumn('employees', 'user_id') && 
                !$this->foreignKeyExists('employees', 'user_id', 'users')) {
                
                Schema::table('employees', function (Blueprint $table) {
                    $table->foreign('user_id')->references('id')->on('users');
                });
                echo "✅ Foreign key employees.user_id creada\n";
            } else {
                echo "ℹ️  Foreign key employees.user_id ya existe\n";
            }
        }

        // 3. Employees -> Positions
        if (Schema::hasTable('employees') && Schema::hasTable('positions')) {
            if (Schema::hasColumn('employees', 'position_id') && 
                !$this->foreignKeyExists('employees', 'position_id', 'positions')) {
                
                Schema::table('employees', function (Blueprint $table) {
                    $table->foreign('position_id')->references('id')->on('positions');
                });
                echo "✅ Foreign key employees.position_id creada\n";
            } else {
                echo "ℹ️  Foreign key employees.position_id ya existe\n";
            }
        }

        // 4. Procedures -> Procedure Types (solo si la tabla procedures existe)
        if (Schema::hasTable('procedures') && Schema::hasTable('procedure_types')) {
            
            // Agregar columna procedure_type_id si no existe
            if (!Schema::hasColumn('procedures', 'procedure_type_id')) {
                Schema::table('procedures', function (Blueprint $table) {
                    $table->foreignId('procedure_type_id')->nullable()->after('employee_id');
                });
                echo "✅ Columna procedures.procedure_type_id agregada\n";
            }

            // Crear foreign key si no existe
            if (!$this->foreignKeyExists('procedures', 'procedure_type_id', 'procedure_types')) {
                Schema::table('procedures', function (Blueprint $table) {
                    $table->foreign('procedure_type_id')->references('id')->on('procedure_types');
                });
                echo "✅ Foreign key procedures.procedure_type_id creada\n";
            } else {
                echo "ℹ️  Foreign key procedures.procedure_type_id ya existe\n";
            }
        }

        echo "✨ Foreign keys verificadas y creadas según necesidad\n";
    }

    public function down(): void
    {
        // Eliminar foreign keys si existen
        if (Schema::hasTable('patients')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->dropForeign(['insurance_id']);
            });
        }

        if (Schema::hasTable('employees')) {
            Schema::table('employees', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropForeign(['position_id']);
            });
        }

        if (Schema::hasTable('procedures')) {
            Schema::table('procedures', function (Blueprint $table) {
                if (Schema::hasColumn('procedures', 'procedure_type_id')) {
                    $table->dropForeign(['procedure_type_id']);
                    $table->dropColumn('procedure_type_id');
                }
            });
        }
    }
};