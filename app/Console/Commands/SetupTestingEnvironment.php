<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class SetupTestingEnvironment extends Command
{
    protected $signature = 'test:setup';
    protected $description = 'Configurar entorno de testing para SUNAT';

    public function handle()
    {
        $this->info('🔧 Configurando entorno de testing...');
        $this->line('');

        // 1. Crear base de datos de testing
        $this->createTestingDatabase();

        // 2. Copiar estructura de la base de datos principal
        $this->copyDatabaseStructure();

        // 3. Insertar datos básicos
        $this->seedBasicData();

        $this->line('');
        $this->info('✅ Entorno de testing configurado exitosamente!');
        $this->line('');
        $this->line('🚀 Ahora puedes ejecutar:');
        $this->line('  • php artisan test --filter=Sunat');
        $this->line('  • php artisan sunat:run-tests');
        $this->line('  • php artisan sunat:use-cases');

        return 0;
    }

    private function createTestingDatabase()
    {
        $this->line('1️⃣ Creando base de datos de testing...');

        try {
            $pdo = new \PDO('mysql:host=127.0.0.1;port=3306', 'root', '1234');
            $pdo->exec('DROP DATABASE IF EXISTS restaurant_testing');
            $pdo->exec('CREATE DATABASE restaurant_testing');
            $this->line('   ✅ Base de datos restaurant_testing creada');
        } catch (\Exception $e) {
            $this->error('   ❌ Error creando base de datos: ' . $e->getMessage());
            return false;
        }

        return true;
    }

    private function copyDatabaseStructure()
    {
        $this->line('2️⃣ Copiando estructura de base de datos...');

        // Configurar conexión temporal a testing
        config(['database.connections.testing' => [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => 'restaurant_testing',
            'username' => 'root',
            'password' => '1234',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]]);

        try {
            // Obtener todas las tablas de la BD principal
            $tables = DB::select('SHOW TABLES');
            $databaseName = DB::getDatabaseName();
            $tableKey = "Tables_in_{$databaseName}";

            foreach ($tables as $table) {
                $tableName = $table->$tableKey;
                
                // Obtener CREATE TABLE statement
                $createStatement = DB::select("SHOW CREATE TABLE `{$tableName}`")[0];
                $createSQL = $createStatement->{'Create Table'};
                
                // Ejecutar en la BD de testing
                DB::connection('testing')->statement($createSQL);
                
                $this->line("   ✅ Tabla {$tableName} copiada");
            }

        } catch (\Exception $e) {
            $this->error('   ❌ Error copiando estructura: ' . $e->getMessage());
            return false;
        }

        return true;
    }

    private function seedBasicData()
    {
        $this->line('3️⃣ Insertando datos básicos...');

        try {
            // Cambiar a conexión de testing
            DB::purge('testing');
            
            // Insertar configuraciones básicas
            DB::connection('testing')->table('app_settings')->insert([
                [
                    'tab' => 'FacturacionElectronica',
                    'key' => 'environment',
                    'value' => 'beta',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'tab' => 'FacturacionElectronica',
                    'key' => 'ruc',
                    'value' => '20123456789',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'tab' => 'FacturacionElectronica',
                    'key' => 'razon_social',
                    'value' => 'Q RICO SAC POLLO TEST',
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);

            // Insertar series de documentos
            DB::connection('testing')->table('document_series')->insert([
                [
                    'document_type' => 'invoice',
                    'series' => 'F001',
                    'current_number' => 1,
                    'active' => true,
                    'description' => 'Facturas Test',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'document_type' => 'receipt',
                    'series' => 'B001',
                    'current_number' => 1,
                    'active' => true,
                    'description' => 'Boletas Test',
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            ]);

            // Insertar categorías básicas
            if (Schema::connection('testing')->hasTable('categories')) {
                DB::connection('testing')->table('categories')->insert([
                    [
                        'id' => 1,
                        'name' => 'Bebidas',
                        'description' => 'Bebidas y refrescos',
                        'created_at' => now(),
                        'updated_at' => now()
                    ],
                    [
                        'id' => 2,
                        'name' => 'Comidas',
                        'description' => 'Platos principales',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                ]);
            }

            $this->line('   ✅ Datos básicos insertados');

        } catch (\Exception $e) {
            $this->error('   ❌ Error insertando datos: ' . $e->getMessage());
            return false;
        }

        return true;
    }
}
