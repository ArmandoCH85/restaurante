<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SunatService;
use App\Models\Invoice;
use App\Models\AppSetting;

class TestSunatFilenames extends Command
{
    protected $signature = 'sunat:test-filenames {invoice_id?}';
    protected $description = 'Probar la generación de nombres de archivos según formato SUNAT';

    public function handle()
    {
        $invoiceId = $this->argument('invoice_id');

        $this->info('🔍 PRUEBA DE NOMBRES DE ARCHIVOS SUNAT');
        $this->line('');

        if ($invoiceId) {
            $this->testSpecificInvoice($invoiceId);
        } else {
            $this->testSampleInvoices();
        }

        $this->showSunatFormat();

        return 0;
    }

    private function testSpecificInvoice($invoiceId)
    {
        try {
            $invoice = Invoice::findOrFail($invoiceId);

            $this->info("📄 PROBANDO FACTURA #{$invoiceId}");
            $this->line("📋 Comprobante: {$invoice->series}-{$invoice->number}");
            $this->line("📅 Tipo: {$invoice->invoice_type}");
            $this->line('');

            $this->generateAndShowFilenames($invoice);

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
        }
    }

    private function testSampleInvoices()
    {
        $this->info('📊 PROBANDO DIFERENTES TIPOS DE COMPROBANTES');
        $this->line('');

        // Crear facturas de ejemplo para prueba
        $samples = [
            [
                'invoice_type' => 'invoice',
                'series' => 'F001',
                'number' => '00000123',
                'description' => 'Factura'
            ],
            [
                'invoice_type' => 'receipt',
                'series' => 'B001',
                'number' => '00000456',
                'description' => 'Boleta'
            ],
            [
                'invoice_type' => 'sales_note',
                'series' => 'NV',
                'number' => '00000789',
                'description' => 'Nota de Venta'
            ]
        ];

        foreach ($samples as $sample) {
            $this->line("🔖 {$sample['description']} ({$sample['series']}-{$sample['number']}):");

            // Crear objeto mock de factura
            $mockInvoice = (object) $sample;
            $this->generateAndShowFilenames($mockInvoice);
            $this->line('');
        }
    }

    private function generateAndShowFilenames($invoice)
    {
        try {
            $sunatService = new SunatService();

            // Usar reflexión para acceder al método privado
            $reflection = new \ReflectionClass($sunatService);
            $method = $reflection->getMethod('generateFilename');
            $method->setAccessible(true);

            // Obtener RUC de configuración
            $ruc = AppSetting::getSetting('FacturacionElectronica', 'ruc') ?: '20123456789';

            // Generar nombres para diferentes extensiones
            $xmlFilename = $method->invoke($sunatService, $invoice, 'xml');
            $zipFilename = $method->invoke($sunatService, $invoice, 'zip');

            $this->line("  📄 XML: <fg=green>{$xmlFilename}</>");
            $this->line("  📦 ZIP: <fg=blue>{$zipFilename}</>");

            // Validar formato
            $this->validateFilename($zipFilename, $invoice, $ruc);

        } catch (\Exception $e) {
            $this->error("  ❌ Error al generar nombres: " . $e->getMessage());
        }
    }

    private function validateFilename($filename, $invoice, $ruc)
    {
        // Patrón esperado: RUC-TipoComprobante-Serie-Correlativo.extension
        $pattern = '/^(\d{11})-(\d{2})-([A-Z0-9]+)-(\d{8})\.(xml|zip)$/';

        if (preg_match($pattern, $filename, $matches)) {
            $this->line("  ✅ <fg=green>Formato válido</>");

            // Verificar componentes
            $fileRuc = $matches[1];
            $tipoComprobante = $matches[2];
            $serie = $matches[3];
            $correlativo = $matches[4];
            $extension = $matches[5];

            // Validaciones específicas
            if ($fileRuc !== $ruc) {
                $this->line("  ⚠️  <fg=yellow>RUC en archivo ({$fileRuc}) vs configurado ({$ruc})</>");
            }

            $expectedTipo = match($invoice->invoice_type) {
                'invoice' => '01',
                'receipt' => '03',
                default => '01'
            };

            if ($tipoComprobante !== $expectedTipo) {
                $this->line("  ⚠️  <fg=yellow>Tipo comprobante: esperado {$expectedTipo}, encontrado {$tipoComprobante}</>");
            }

            if (strlen($correlativo) !== 8) {
                $this->line("  ❌ <fg=red>Correlativo debe tener 8 dígitos</>");
            }

        } else {
            $this->line("  ❌ <fg=red>Formato inválido</>");
            $this->line("  📝 Patrón esperado: RUC-TipoComprobante-Serie-Correlativo.extension");
        }
    }

    private function showSunatFormat()
    {
        $this->line('');
        $this->info('📋 FORMATO REQUERIDO POR SUNAT:');
        $this->line('');
        $this->line('🔹 Estructura: <RUC>-<TipoComprobante>-<Serie>-<Correlativo>.<extension>');
        $this->line('');
        $this->line('📝 Componentes:');
        $this->line('  • RUC: 11 dígitos del emisor');
        $this->line('  • TipoComprobante: 01=Factura, 03=Boleta, 07=Nota Crédito, 08=Nota Débito');
        $this->line('  • Serie: Serie del comprobante (ej: F001, B001)');
        $this->line('  • Correlativo: 8 dígitos con ceros a la izquierda');
        $this->line('  • Extension: xml o zip (minúsculas)');
        $this->line('');
        $this->line('✅ Ejemplo válido: <fg=green>20123456789-01-F001-00000123.zip</fg=green>');
        $this->line('❌ Ejemplo inválido: <fg=red>F001-00000123_20250525042208.zip</fg=red>');
        $this->line('');
        $this->info('💡 IMPORTANTE:');
        $this->line('  • El archivo XML dentro del ZIP debe tener el mismo nombre');
        $this->line('  • La extensión debe ser en minúsculas');
        $this->line('  • El RUC debe coincidir con el emisor del comprobante');
    }
}
