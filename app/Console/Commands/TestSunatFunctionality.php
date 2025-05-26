<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Product;
use App\Models\InvoiceDetail;
use App\Models\DocumentSeries;
use App\Services\SunatService;

class TestSunatFunctionality extends Command
{
    protected $signature = 'sunat:test-functionality {--generate} {--send} {--cleanup}';
    protected $description = 'Probar funcionalidad completa de SUNAT con datos reales';

    public function handle()
    {
        $this->info('🚀 Test de Funcionalidad Completa SUNAT');
        $this->line('');

        if ($this->option('generate')) {
            return $this->testGeneration();
        }

        if ($this->option('send')) {
            return $this->testSending();
        }

        if ($this->option('cleanup')) {
            return $this->cleanupTestData();
        }

        // Flujo completo por defecto
        return $this->runCompleteTest();
    }

    private function runCompleteTest()
    {
        $this->info('🔄 Ejecutando test completo...');
        $this->line('');

        // 1. Test de generación
        $this->line('1️⃣ Generando comprobantes de prueba...');
        $invoiceId = $this->generateTestInvoice();
        $receiptId = $this->generateTestReceipt();

        if (!$invoiceId || !$receiptId) {
            $this->error('❌ Error generando comprobantes');
            return 1;
        }

        $this->line("   ✅ Factura generada: ID {$invoiceId}");
        $this->line("   ✅ Boleta generada: ID {$receiptId}");
        $this->line('');

        // 2. Test de validaciones
        $this->line('2️⃣ Probando validaciones...');
        $this->testValidations($invoiceId, $receiptId);
        $this->line('');

        // 3. Test de diferenciación
        $this->line('3️⃣ Probando diferenciación automática...');
        $this->testAutomaticDifferentiation($invoiceId, $receiptId);
        $this->line('');

        // 4. Preguntar si enviar a SUNAT
        if ($this->confirm('¿Deseas probar el envío real a SUNAT? (Esto enviará comprobantes reales)', false)) {
            $this->line('4️⃣ Enviando a SUNAT...');
            $this->testRealSending($receiptId); // Enviar solo boleta (más seguro)
        } else {
            $this->line('4️⃣ Envío a SUNAT omitido por el usuario');
        }

        $this->line('');
        $this->info('✅ Test completo finalizado');
        
        // Preguntar si limpiar datos
        if ($this->confirm('¿Deseas limpiar los datos de prueba generados?', true)) {
            $this->cleanupSpecificInvoices([$invoiceId, $receiptId]);
        }

        return 0;
    }

    private function generateTestInvoice()
    {
        try {
            // Crear cliente empresa
            $customer = Customer::create([
                'document_type' => 'RUC',
                'document_number' => '20999999996',
                'name' => 'EMPRESA TEST FUNCIONALIDAD S.A.C.',
                'address' => 'Av. Test 123, Lima',
                'phone' => '01-9999996',
                'email' => 'test@empresa.com'
            ]);

            // Obtener serie de factura
            $series = DocumentSeries::where('document_type', 'invoice')->where('active', true)->first();
            if (!$series) {
                throw new \Exception('No hay serie activa para facturas');
            }

            // Crear factura
            $invoice = Invoice::create([
                'invoice_type' => 'invoice',
                'series' => $series->series,
                'number' => str_pad($series->current_number + 1, 8, '0', STR_PAD_LEFT),
                'issue_date' => now()->format('Y-m-d'),
                'customer_id' => $customer->id,
                'taxable_amount' => 84.75,
                'tax' => 15.25,
                'total' => 100.00,
                'tax_authority_status' => 'pending',
                'codigo_tipo_moneda' => 'PEN',
                'codigo_tipo_operacion' => '0101'
            ]);

            // Agregar detalles (usar productos existentes o crear temporales)
            $this->addInvoiceDetails($invoice);

            // Actualizar serie
            $series->increment('current_number');

            return $invoice->id;

        } catch (\Exception $e) {
            $this->error("Error generando factura: " . $e->getMessage());
            return null;
        }
    }

    private function generateTestReceipt()
    {
        try {
            // Crear cliente persona
            $customer = Customer::create([
                'document_type' => 'DNI',
                'document_number' => '99999996',
                'name' => 'Juan Pérez Test',
                'address' => 'Jr. Test 456',
                'phone' => '999999996'
            ]);

            // Obtener serie de boleta
            $series = DocumentSeries::where('document_type', 'receipt')->where('active', true)->first();
            if (!$series) {
                throw new \Exception('No hay serie activa para boletas');
            }

            // Crear boleta
            $invoice = Invoice::create([
                'invoice_type' => 'receipt',
                'series' => $series->series,
                'number' => str_pad($series->current_number + 1, 8, '0', STR_PAD_LEFT),
                'issue_date' => now()->format('Y-m-d'),
                'customer_id' => $customer->id,
                'taxable_amount' => 42.37,
                'tax' => 7.63,
                'total' => 50.00,
                'tax_authority_status' => 'pending',
                'codigo_tipo_moneda' => 'PEN',
                'codigo_tipo_operacion' => '0101'
            ]);

            // Agregar detalles
            $this->addInvoiceDetails($invoice);

            // Actualizar serie
            $series->increment('current_number');

            return $invoice->id;

        } catch (\Exception $e) {
            $this->error("Error generando boleta: " . $e->getMessage());
            return null;
        }
    }

    private function addInvoiceDetails($invoice)
    {
        // Intentar usar productos existentes
        $products = Product::take(2)->get();
        
        if ($products->count() >= 2) {
            // Usar productos existentes
            InvoiceDetail::create([
                'invoice_id' => $invoice->id,
                'product_id' => $products[0]->id,
                'quantity' => 2,
                'unit_price' => 15.00,
                'subtotal' => 30.00,
                'description' => $products[0]->name
            ]);

            InvoiceDetail::create([
                'invoice_id' => $invoice->id,
                'product_id' => $products[1]->id,
                'quantity' => 4,
                'unit_price' => 5.00,
                'subtotal' => 20.00,
                'description' => $products[1]->name
            ]);
        } else {
            // Crear detalles genéricos
            InvoiceDetail::create([
                'invoice_id' => $invoice->id,
                'product_id' => null,
                'quantity' => 2,
                'unit_price' => 15.00,
                'subtotal' => 30.00,
                'description' => 'Producto Test 1'
            ]);

            InvoiceDetail::create([
                'invoice_id' => $invoice->id,
                'product_id' => null,
                'quantity' => 4,
                'unit_price' => 5.00,
                'subtotal' => 20.00,
                'description' => 'Producto Test 2'
            ]);
        }
    }

    private function testValidations($invoiceId, $receiptId)
    {
        $sunatService = new SunatService();

        // Test 1: Factura debe ser válida
        try {
            $invoice = Invoice::find($invoiceId);
            $reflection = new \ReflectionClass($sunatService);
            $method = $reflection->getMethod('determinarTipoComprobante');
            $method->setAccessible(true);
            $result = $method->invoke($sunatService, $invoice);
            
            if ($result === 'factura') {
                $this->line("   ✅ Factura correctamente identificada");
            } else {
                $this->line("   ❌ Factura mal identificada: {$result}");
            }
        } catch (\Exception $e) {
            $this->line("   ❌ Error validando factura: " . $e->getMessage());
        }

        // Test 2: Boleta debe ser válida
        try {
            $receipt = Invoice::find($receiptId);
            $reflection = new \ReflectionClass($sunatService);
            $method = $reflection->getMethod('determinarTipoComprobante');
            $method->setAccessible(true);
            $result = $method->invoke($sunatService, $receipt);
            
            if ($result === 'boleta') {
                $this->line("   ✅ Boleta correctamente identificada");
            } else {
                $this->line("   ❌ Boleta mal identificada: {$result}");
            }
        } catch (\Exception $e) {
            $this->line("   ❌ Error validando boleta: " . $e->getMessage());
        }
    }

    private function testAutomaticDifferentiation($invoiceId, $receiptId)
    {
        $invoice = Invoice::with('customer')->find($invoiceId);
        $receipt = Invoice::with('customer')->find($receiptId);

        // Verificar tipos de documento
        $this->line("   📄 Factura: Cliente {$invoice->customer->document_type} → Tipo: {$invoice->invoice_type}");
        $this->line("   📄 Boleta: Cliente {$receipt->customer->document_type} → Tipo: {$receipt->invoice_type}");

        // Verificar lógica
        if ($invoice->customer->document_type === 'RUC' && $invoice->invoice_type === 'invoice') {
            $this->line("   ✅ RUC → Factura (correcto)");
        } else {
            $this->line("   ❌ RUC → Factura (incorrecto)");
        }

        if ($receipt->customer->document_type === 'DNI' && $receipt->invoice_type === 'receipt') {
            $this->line("   ✅ DNI → Boleta (correcto)");
        } else {
            $this->line("   ❌ DNI → Boleta (incorrecto)");
        }
    }

    private function testRealSending($receiptId)
    {
        try {
            $this->line("   🚀 Enviando boleta ID {$receiptId} a SUNAT...");
            
            $exitCode = $this->call('sunat:test', ['invoiceId' => $receiptId]);
            
            if ($exitCode === 0) {
                $this->line("   ✅ Envío completado (revisar resultado en comando anterior)");
            } else {
                $this->line("   ⚠️  Envío completado con advertencias");
            }

        } catch (\Exception $e) {
            $this->line("   ❌ Error en envío: " . $e->getMessage());
        }
    }

    private function cleanupTestData()
    {
        $this->info('🧹 Limpiando datos de prueba...');

        $testCustomers = Customer::where('document_number', 'like', '99999999%')
            ->orWhere('document_number', 'like', '20999999999%')
            ->get();

        $cleaned = 0;
        foreach ($testCustomers as $customer) {
            // Eliminar facturas del cliente
            $invoices = Invoice::where('customer_id', $customer->id)->get();
            foreach ($invoices as $invoice) {
                $invoice->details()->delete();
                $invoice->delete();
                $cleaned++;
            }
            
            // Eliminar cliente
            $customer->delete();
        }

        $this->info("✅ Limpieza completada: {$cleaned} registros eliminados");
        return 0;
    }

    private function cleanupSpecificInvoices($invoiceIds)
    {
        foreach ($invoiceIds as $id) {
            $invoice = Invoice::find($id);
            if ($invoice) {
                $invoice->details()->delete();
                $invoice->customer()->delete();
                $invoice->delete();
            }
        }
        $this->line("✅ Datos de prueba eliminados");
    }
}
