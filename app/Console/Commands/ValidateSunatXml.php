<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SunatService;
use App\Models\Invoice;

class ValidateSunatXml extends Command
{
    protected $signature = 'sunat:validate-xml {invoice_id?}';
    protected $description = 'Validar que el XML generado cumple con especificaciones SUNAT';

    public function handle()
    {
        $invoiceId = $this->argument('invoice_id');
        
        if (!$invoiceId) {
            $this->info('🔍 Validando XML de SUNAT - Especificaciones');
            $this->line('');
            $this->showSunatRequirements();
            return 0;
        }

        $this->validateInvoiceXml($invoiceId);
        return 0;
    }
    
    private function showSunatRequirements()
    {
        $this->info('📋 CAMPOS OBLIGATORIOS SEGÚN SUNAT:');
        $this->line('');
        
        $requirements = [
            '✅ UBL Version: 2.1',
            '✅ Tipo Operación: 0101 (Venta interna)',
            '✅ Tipo Documento: 01 (Factura) / 03 (Boleta)',
            '✅ Serie y Correlativo',
            '✅ Fecha de Emisión',
            '✅ Tipo de Moneda: PEN',
            '✅ Datos del Emisor (Company)',
            '✅ Datos del Cliente (Client)',
            '✅ Note con monto en letras',
            '✅ Legend con código 1000',
            '✅ Detalles con IGV calculado',
            '✅ Totales: MtoOperGravadas, MtoIGV, etc.'
        ];
        
        foreach ($requirements as $req) {
            $this->line("  {$req}");
        }
        
        $this->line('');
        $this->info('🔖 FORMATO DEL NOTE OBLIGATORIO:');
        $this->line('  <cbc:Note languageLocaleID="1000"><![CDATA[SON CUARENTA Y TRES CON 66/100 SOLES]]></cbc:Note>');
        
        $this->line('');
        $this->info('🏷️  FORMATO DE LA LEYENDA OBLIGATORIA:');
        $this->line('  <cac:TaxTotal>');
        $this->line('    <cbc:TaxAmount currencyID="PEN">7.63</cbc:TaxAmount>');
        $this->line('    <cac:TaxSubtotal>');
        $this->line('      <cbc:TaxableAmount currencyID="PEN">42.37</cbc:TaxableAmount>');
        $this->line('      <cbc:TaxAmount currencyID="PEN">7.63</cbc:TaxAmount>');
        $this->line('    </cac:TaxSubtotal>');
        $this->line('  </cac:TaxTotal>');
    }
    
    private function validateInvoiceXml($invoiceId)
    {
        try {
            $invoice = Invoice::with(['details.product', 'customer'])->findOrFail($invoiceId);
            
            $this->info("🔍 Validando XML para Factura #{$invoice->id}");
            $this->line("📄 Tipo: {$invoice->invoice_type}");
            $this->line("💰 Total: S/ " . number_format($invoice->total, 2));
            $this->line('');
            
            // Simular generación de XML
            $sunatService = new SunatService();
            
            // Usar reflexión para acceder al método privado
            $reflection = new \ReflectionClass($sunatService);
            $method = $reflection->getMethod('createGreenterInvoice');
            $method->setAccessible(true);
            
            $greenterInvoice = $method->invoke($sunatService, $invoice);
            
            $this->info('✅ XML generado exitosamente');
            $this->line('');
            
            // Validar campos obligatorios
            $this->validateRequiredFields($greenterInvoice, $invoice);
            
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
        }
    }
    
    private function validateRequiredFields($greenterInvoice, $invoice)
    {
        $this->info('🔍 Validando campos obligatorios:');
        
        // Validar UBL Version
        $this->checkField('UBL Version', $greenterInvoice->getUblVersion(), '2.1');
        
        // Validar Tipo Operación
        $this->checkField('Tipo Operación', $greenterInvoice->getTipoOperacion(), '0101');
        
        // Validar Tipo Documento
        $expectedTipoDoc = $invoice->invoice_type === 'invoice' ? '01' : '03';
        $this->checkField('Tipo Documento', $greenterInvoice->getTipoDoc(), $expectedTipoDoc);
        
        // Validar Serie
        $this->checkField('Serie', $greenterInvoice->getSerie(), $invoice->series);
        
        // Validar Moneda
        $this->checkField('Tipo Moneda', $greenterInvoice->getTipoMoneda(), 'PEN');
        
        // Validar que tenga leyendas
        $legends = $greenterInvoice->getLegends();
        if ($legends && count($legends) > 0) {
            $this->line("  ✅ Leyendas: " . count($legends) . " encontrada(s)");
            foreach ($legends as $legend) {
                $this->line("    🏷️  Código: {$legend->getCode()}, Valor: {$legend->getValue()}");
            }
        } else {
            $this->line("  ❌ Leyendas: No encontradas");
        }
        
        // Validar detalles
        $details = $greenterInvoice->getDetails();
        if ($details && count($details) > 0) {
            $this->line("  ✅ Detalles: " . count($details) . " item(s)");
        } else {
            $this->line("  ❌ Detalles: No encontrados");
        }
    }
    
    private function checkField($fieldName, $actual, $expected)
    {
        if ($actual === $expected) {
            $this->line("  ✅ {$fieldName}: {$actual}");
        } else {
            $this->line("  ❌ {$fieldName}: Esperado '{$expected}', encontrado '{$actual}'");
        }
    }
}
