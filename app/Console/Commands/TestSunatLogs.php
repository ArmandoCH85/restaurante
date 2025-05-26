<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SunatService;
use App\Models\Invoice;

class TestSunatLogs extends Command
{
    protected $signature = 'sunat:test-logs {invoice_id}';
    protected $description = 'Probar logs de SUNAT sin enviar realmente';

    public function handle()
    {
        $invoiceId = $this->argument('invoice_id');
        
        try {
            $invoice = Invoice::with(['details.product', 'customer', 'employee'])
                ->findOrFail($invoiceId);
            
            $this->info("🔍 PROBANDO LOGS PARA FACTURA #{$invoiceId}");
            $this->line("📋 Comprobante: {$invoice->series}-{$invoice->number}");
            $this->line("📅 Tipo: {$invoice->invoice_type}");
            $this->line('');
            
            // Crear instancia del servicio SUNAT
            $sunatService = new SunatService();
            
            // Usar reflexión para acceder a métodos privados
            $reflection = new \ReflectionClass($sunatService);
            
            // Probar setupCompany
            $this->info('1️⃣ Probando configuración de empresa...');
            $setupMethod = $reflection->getMethod('setupCompany');
            $setupMethod->setAccessible(true);
            $setupMethod->invoke($sunatService);
            
            // Probar createGreenterInvoice
            $this->info('2️⃣ Probando creación de factura Greenter...');
            $createMethod = $reflection->getMethod('createGreenterInvoice');
            $createMethod->setAccessible(true);
            $greenterInvoice = $createMethod->invoke($sunatService, $invoice);
            
            // Probar generateFilename
            $this->info('3️⃣ Probando generación de nombres de archivo...');
            $filenameMethod = $reflection->getMethod('generateFilename');
            $filenameMethod->setAccessible(true);
            
            $xmlFilename = $filenameMethod->invoke($sunatService, $invoice, 'xml');
            $zipFilename = $filenameMethod->invoke($sunatService, $invoice, 'zip');
            
            $this->line("📄 XML: {$xmlFilename}");
            $this->line("📦 ZIP: {$zipFilename}");
            
            $this->line('');
            $this->info('✅ Logs generados. Revisa storage/logs/laravel.log para ver los detalles.');
            $this->line('');
            $this->info('💡 Para ver los logs en tiempo real:');
            $this->line('   tail -f storage/logs/laravel.log | grep -E "(Configurando datos|Empresa configurada|Generando nombre)"');
            
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            $this->line("📍 Archivo: " . $e->getFile());
            $this->line("📍 Línea: " . $e->getLine());
        }
        
        return 0;
    }
}
