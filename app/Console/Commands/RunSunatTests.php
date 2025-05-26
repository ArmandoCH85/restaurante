<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class RunSunatTests extends Command
{
    protected $signature = 'sunat:run-tests {--filter=} {--coverage} {--detail} {--safe}';
    protected $description = 'Ejecutar todos los tests relacionados con SUNAT';

    public function handle()
    {
        $this->info('🧪 Ejecutando Tests del Sistema de Facturación Electrónica SUNAT');
        $this->line('');

        $filter = $this->option('filter');
        $coverage = $this->option('coverage');
        $detail = $this->option('detail');
        $safe = $this->option('safe');

        // Si es modo seguro, usar comandos específicos
        if ($safe) {
            return $this->runSafeTests();
        }

        // Configurar comando base
        $command = 'test';
        $options = [];

        if ($filter) {
            $options['--filter'] = $filter;
        } else {
            // Ejecutar solo tests relacionados con SUNAT
            $options['--testsuite'] = 'Feature,Unit';
            $options['--filter'] = 'Sunat|Invoice|Command';
        }

        if ($coverage) {
            $options['--coverage-text'] = true;
            $options['--coverage-html'] = 'tests/coverage';
        }

        if ($detail) {
            $options['--verbose'] = true;
        }

        // Mostrar información de los tests que se van a ejecutar
        $this->showTestInfo();

        // Ejecutar tests
        $this->line('');
        $this->info('🚀 Iniciando ejecución de tests...');
        $this->line('');

        $exitCode = Artisan::call($command, $options);

        // Mostrar resultado
        $this->line('');
        if ($exitCode === 0) {
            $this->info('✅ Todos los tests pasaron exitosamente!');
            $this->showSuccessMessage();
        } else {
            $this->error('❌ Algunos tests fallaron. Revisa los detalles arriba.');
            $this->showFailureMessage();
        }

        if ($coverage) {
            $this->line('');
            $this->info('📊 Reporte de cobertura generado en: tests/coverage/index.html');
        }

        return $exitCode;
    }

    private function runSafeTests()
    {
        $this->info('🛡️ Ejecutando Tests Seguros (sin afectar BD actual)');
        $this->line('');

        // Ejecutar tests seguros
        $exitCode = $this->call('sunat:test-safe');

        $this->line('');
        if ($exitCode === 0) {
            $this->info('✅ Tests seguros completados exitosamente!');
            $this->line('');
            $this->line('🚀 Comandos adicionales disponibles:');
            $this->line('  • php artisan sunat:test-functionality --generate');
            $this->line('  • php artisan sunat:test-functionality');
            $this->line('  • php artisan sunat:use-cases');
        } else {
            $this->error('❌ Algunos tests seguros fallaron.');
        }

        return $exitCode;
    }

    private function showTestInfo()
    {
        $this->info('📋 Tests que se ejecutarán:');
        $this->line('');

        $tests = [
            '🔧 Unit Tests:' => [
                'SunatServiceTest' => 'Validación del servicio principal de SUNAT',
                'InvoiceModelTest' => 'Validación del modelo Invoice y sus métodos'
            ],
            '🏗️ Feature Tests:' => [
                'SunatCommandsTest' => 'Validación de comandos Artisan',
                'SunatIntegrationTest' => 'Tests de integración completos'
            ]
        ];

        foreach ($tests as $category => $testList) {
            $this->line("<fg=yellow>{$category}</>");
            foreach ($testList as $testName => $description) {
                $this->line("  • <fg=cyan>{$testName}:</> {$description}");
            }
            $this->line('');
        }
    }

    private function showSuccessMessage()
    {
        $this->line('');
        $this->info('🎉 Sistema de Facturación Electrónica validado exitosamente!');
        $this->line('');
        $this->line('✅ Funcionalidades verificadas:');
        $this->line('  • Diferenciación automática Factura vs Boleta');
        $this->line('  • Validación de tipos de documento');
        $this->line('  • Restricción de Notas de Venta');
        $this->line('  • Generación automática de comprobantes');
        $this->line('  • Cálculos de totales e IGV');
        $this->line('  • Numeración de series');
        $this->line('  • Comandos Artisan');
        $this->line('');
        $this->info('🚀 El sistema está listo para producción!');
    }

    private function showFailureMessage()
    {
        $this->line('');
        $this->error('⚠️  Se encontraron problemas en el sistema.');
        $this->line('');
        $this->line('🔍 Pasos para solucionar:');
        $this->line('  1. Revisa los errores mostrados arriba');
        $this->line('  2. Ejecuta tests específicos: php artisan sunat:run-tests --filter=NombreTest');
        $this->line('  3. Verifica la configuración de la base de datos de testing');
        $this->line('  4. Asegúrate de que todas las migraciones estén ejecutadas');
        $this->line('');
        $this->line('💡 Comandos útiles:');
        $this->line('  • php artisan migrate:fresh --env=testing');
        $this->line('  • php artisan sunat:run-tests --detail');
        $this->line('  • php artisan sunat:run-tests --filter=SunatServiceTest');
    }
}
