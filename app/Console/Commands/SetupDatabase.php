<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use PDO;
use Exception;

class SetupDatabase extends Command
{
    /**
     * Nombre del comando que se usará desde la consola.
     */
    protected $signature = 'db:setup';

    /**
     * Descripción del comando.
     */
    protected $description = 'Crea la base de datos PostgreSQL si no existe y ejecuta las migraciones.';

    public function handle()
    {
        $database = Config::get('database.connections.pgsql.database');
        $username = Config::get('database.connections.pgsql.username');
        $password = Config::get('database.connections.pgsql.password');
        $host     = Config::get('database.connections.pgsql.host');
        $port     = Config::get('database.connections.pgsql.port');

        try {
            // Conexión directa al servidor sin especificar una base de datos
            $pdo = new PDO("pgsql:host=$host;port=$port", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Verificar si la DB ya existe
            $exists = $pdo->query("SELECT 1 FROM pg_database WHERE datname = '$database'")->fetch();

            if (!$exists) {
                $this->info("🚀 Creando base de datos '$database'...");
                $pdo->exec("CREATE DATABASE \"$database\";");
                $this->info("✅ Base de datos creada con éxito.");
            } else {
                $this->info("✔️ La base de datos '$database' ya existe.");
            }

            $pdo = null;
        } catch (Exception $e) {
            $this->error("❌ Error al conectar o crear la base de datos: " . $e->getMessage());
            return Command::FAILURE;
        }

        // Ejecutar las migraciones
        $this->info("🏗️ Ejecutando migraciones...");
        Artisan::call('migrate', [], $this->getOutput());
        $this->info("🎉 Migraciones completadas correctamente.");

        return Command::SUCCESS;
    }
}
