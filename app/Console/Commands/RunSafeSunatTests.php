<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Product;
use App\Models\DocumentSeries;
use App\Models\AppSetting;
use App\Helpers\SunatServiceHelper;

class RunSafeSunatTests extends Command
{
    protected $signature = 'sunat:test-safe {--type=all}';
    protected $description = 'Ejecutar tests seguros de SUNAT sobre la BD actual (sin borrar datos)';

    public function handle()
    {
        $this->info('🧪 Ejecutando Tests Seguros de SUNAT');
        $this->info('✅ Sin borrar datos existentes');
        $this->info('✅ Usando BD actual: ' . config('database.connections.mysql.database'));
        $this->line('');

        $type = $this->option('type');

        switch ($type) {
            case 'config':
                return $this->testConfiguration();
            case 'types':
                return $this->testDocumentTypes();
            case 'series':
                return $this->testSeries();
            case 'service':
                return $this->testSunatService();
            case 'validation':
                return $this->testValidations();
            case 'all':
            default:
                return $this->runAllTests();
        }
    }

    private function runAllTests()
    {
        $this->info('🚀 Ejecutando todos los tests...');
        $this->line('');

        $tests = [
            'Configuración SUNAT' => 'testConfiguration',
            'Tipos de Documento' => 'testDocumentTypes',
            'Series de Documentos' => 'testSeries',
            'Servicio SUNAT' => 'testSunatService',
            'Validaciones' => 'testValidations'
        ];

        $passed = 0;
        $failed = 0;

        foreach ($tests as $testName => $method) {
            $this->line("🔍 {$testName}...");
            try {
                $result = $this->$method();
                if ($result) {
                    $this->line("   ✅ PASÓ");
                    $passed++;
                } else {
                    $this->line("   ❌ FALLÓ");
                    $failed++;
                }
            } catch (\Exception $e) {
                $this->line("   ❌ ERROR: " . $e->getMessage());
                $failed++;
            }
            $this->line('');
        }

        $this->line('');
        $this->info("📊 Resultados: {$passed} pasaron, {$failed} fallaron");

        if ($failed === 0) {
            $this->info('🎉 ¡Todos los tests pasaron exitosamente!');
        } else {
            $this->warn('⚠️  Algunos tests fallaron. Revisa la configuración.');
        }

        return $failed === 0 ? 0 : 1;
    }

    private function testConfiguration()
    {
        $this->line('🔧 Test: Configuración SUNAT');

        // Verificar configuraciones básicas
        $requiredSettings = [
            'environment' => 'Entorno (beta/production)',
            'ruc' => 'RUC de la empresa',
            'razon_social' => 'Razón social'
        ];

        $allPassed = true;

        foreach ($requiredSettings as $key => $description) {
            $setting = AppSetting::where('tab', 'FacturacionElectronica')
                ->where('key', $key)
                ->first();

            if ($setting && !empty($setting->value)) {
                $this->line("   ✅ {$description}: {$setting->value}");
            } else {
                $this->line("   ❌ {$description}: NO CONFIGURADO");
                $allPassed = false;
            }
        }

        return $allPassed;
    }

    private function testDocumentTypes()
    {
        $this->line('📄 Test: Tipos de Documento');

        // Crear datos temporales para testing
        $testCustomers = [
            [
                'type' => 'RUC',
                'number' => '20999999998',
                'name' => 'EMPRESA TEST RUC',
                'expected' => 'factura'
            ],
            [
                'type' => 'DNI',
                'number' => '99999998',
                'name' => 'PERSONA TEST DNI',
                'expected' => 'boleta'
            ]
        ];

        $allPassed = true;

        // Crear instancia sin inicializar Greenter (para evitar error de certificado)
        $sunatService = SunatServiceHelper::createIfNotTesting();
        if ($sunatService === null) {
            $this->line("   ⚠️  Servicio SUNAT no disponible (modo testing o certificado faltante)");
            $this->line("   🔍 Probando solo lógica de diferenciación...");
        }

        foreach ($testCustomers as $testData) {
            // Crear cliente temporal
            $customer = Customer::create([
                'document_type' => $testData['type'],
                'document_number' => $testData['number'],
                'name' => $testData['name']
            ]);

            // Crear factura temporal
            $invoice = Invoice::create([
                'customer_id' => $customer->id,
                'invoice_type' => $testData['expected'] === 'factura' ? 'invoice' : 'receipt',
                'series' => 'TEST',
                'number' => '99999998',
                'issue_date' => now()->format('Y-m-d'),
                'taxable_amount' => 100.00,
                'tax' => 18.00,
                'total' => 118.00,
                'tax_authority_status' => 'pending'
            ]);

            // Test del servicio
            try {
                $reflection = new \ReflectionClass($sunatService);
                $method = $reflection->getMethod('determinarTipoComprobante');
                $method->setAccessible(true);
                $result = $method->invoke($sunatService, $invoice);

                if ($result === $testData['expected']) {
                    $this->line("   ✅ {$testData['type']} → {$result}");
                } else {
                    $this->line("   ❌ {$testData['type']} → {$result} (esperado: {$testData['expected']})");
                    $allPassed = false;
                }
            } catch (\Exception $e) {
                $this->line("   ❌ Error con {$testData['type']}: " . $e->getMessage());
                $allPassed = false;
            }

            // Limpiar datos temporales
            $invoice->delete();
            $customer->delete();
        }

        return $allPassed;
    }

    private function testSeries()
    {
        $this->line('📊 Test: Series de Documentos');

        $requiredSeries = [
            'invoice' => 'Facturas',
            'receipt' => 'Boletas'
        ];

        $allPassed = true;

        foreach ($requiredSeries as $type => $description) {
            $series = DocumentSeries::where('document_type', $type)
                ->where('active', true)
                ->first();

            if ($series) {
                $this->line("   ✅ {$description}: {$series->series} (actual: {$series->current_number})");
            } else {
                $this->line("   ❌ {$description}: NO HAY SERIE ACTIVA");
                $allPassed = false;
            }
        }

        return $allPassed;
    }

    private function testSunatService()
    {
        $this->line('🛠️ Test: Servicio SUNAT');

        $allPassed = true;

        $sunatService = SunatServiceHelper::createIfNotTesting();
        if ($sunatService === null) {
            $this->line("   ⚠️  SunatService no disponible en modo testing");
            $allPassed = false;
        } else {
            $this->line("   ✅ SunatService se instancia correctamente");

            // Test método convertir números a letras
            $reflection = new \ReflectionClass($sunatService);
            $method = $reflection->getMethod('convertirNumeroALetras');
            $method->setAccessible(true);

            $result = $method->invoke($sunatService, 100.50);
            if (is_string($result) && !empty($result)) {
                $this->line("   ✅ Conversión números a letras funciona");
            } else {
                $this->line("   ❌ Conversión números a letras falla");
                $allPassed = false;
            }
        }

        return $allPassed;
    }

    private function testValidations()
    {
        $this->line('🛡️ Test: Validaciones');

        $allPassed = true;

        // Test validación de notas de venta
        $customer = Customer::create([
            'document_type' => 'DNI',
            'document_number' => '99999997',
            'name' => 'CLIENTE TEST VALIDACION'
        ]);

        $salesNote = Invoice::create([
            'customer_id' => $customer->id,
            'invoice_type' => 'sales_note',
            'series' => 'TEST',
            'number' => '99999997',
            'issue_date' => now()->format('Y-m-d'),
            'taxable_amount' => 50.00,
            'tax' => 9.00,
            'total' => 59.00,
            'tax_authority_status' => 'pending'
        ]);

        $sunatService = SunatServiceHelper::createIfNotTesting();
        if ($sunatService === null) {
            $this->line("   ⚠️  Saltando validación en modo testing");
        } else {
            try {
                $sunatService->emitirFactura($salesNote->id);
                $this->line("   ❌ Nota de venta NO fue bloqueada (debería fallar)");
                $allPassed = false;
            } catch (\Exception $e) {
                if (str_contains($e->getMessage(), 'Solo se pueden enviar Boletas y Facturas')) {
                    $this->line("   ✅ Nota de venta correctamente bloqueada");
                } else {
                    $this->line("   ❌ Error inesperado: " . $e->getMessage());
                    $allPassed = false;
                }
            }
        }

        // Limpiar datos temporales
        $salesNote->delete();
        $customer->delete();

        return $allPassed;
    }
}
