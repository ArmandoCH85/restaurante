<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Customer;
use App\Models\Product;
use App\Models\DocumentSeries;
use Illuminate\Console\Command;

class GenerateTestInvoice extends Command
{
    protected $signature = 'invoice:generate-test {type=invoice}';
    protected $description = 'Generar una factura de prueba para testing';

    public function handle()
    {
        $type = $this->argument('type');
        
        if ($type === 'invoice') {
            return $this->generateInvoice();
        } else {
            return $this->generateReceipt();
        }
    }

    private function generateInvoice()
    {
        $this->info('🏭 Generando FACTURA de prueba...');

        // 1. Cliente con RUC
        $customer = Customer::where('document_type', 'RUC')->first();
        if (!$customer) {
            $customer = Customer::create([
                'document_type' => 'RUC',
                'document_number' => '20123456789',
                'name' => 'EMPRESA DEMO S.A.C.',
                'address' => 'Av. Ejemplo 123, Lima',
                'phone' => '01-1234567',
                'email' => 'demo@empresa.com'
            ]);
        }

        // 2. Serie de factura
        $series = DocumentSeries::where('document_type', 'invoice')->where('active', true)->first();
        
        // 3. Productos
        $products = Product::take(2)->get();
        
        // 4. Crear factura
        $invoice = Invoice::create([
            'invoice_type' => 'invoice',
            'series' => $series->series,
            'number' => str_pad($series->current_number + 1, 8, '0', STR_PAD_LEFT),
            'issue_date' => now()->format('Y-m-d'),
            'customer_id' => $customer->id,
            'taxable_amount' => 42.37, // Sin IGV
            'tax' => 7.63, // IGV 18%
            'total' => 50.00,
            'tax_authority_status' => 'pending',
            'codigo_tipo_moneda' => 'PEN',
            'codigo_tipo_operacion' => '0101'
        ]);

        // 5. Detalles
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

        // 6. Actualizar serie
        $series->increment('current_number');

        $this->info("✅ FACTURA generada:");
        $this->line("   ID: {$invoice->id}");
        $this->line("   Número: {$invoice->series}-{$invoice->number}");
        $this->line("   Cliente: {$customer->name} (RUC: {$customer->document_number})");
        $this->line("   Total: S/ {$invoice->total}");
        
        return $invoice->id;
    }

    private function generateReceipt()
    {
        $this->info('👤 Generando BOLETA de prueba...');

        // 1. Cliente con DNI
        $customer = Customer::where('document_type', 'DNI')->first();
        if (!$customer) {
            $customer = Customer::create([
                'document_type' => 'DNI',
                'document_number' => '12345678',
                'name' => 'Juan Pérez García',
                'address' => 'Jr. Lima 456',
                'phone' => '987654321'
            ]);
        }

        // 2. Serie de boleta
        $series = DocumentSeries::where('document_type', 'receipt')->where('active', true)->first();
        
        // 3. Productos
        $products = Product::take(2)->get();
        
        // 4. Crear boleta
        $invoice = Invoice::create([
            'invoice_type' => 'receipt',
            'series' => $series->series,
            'number' => str_pad($series->current_number + 1, 8, '0', STR_PAD_LEFT),
            'issue_date' => now()->format('Y-m-d'),
            'customer_id' => $customer->id,
            'taxable_amount' => 25.42, // Sin IGV
            'tax' => 4.58, // IGV 18%
            'total' => 30.00,
            'tax_authority_status' => 'pending',
            'codigo_tipo_moneda' => 'PEN',
            'codigo_tipo_operacion' => '0101'
        ]);

        // 5. Detalles
        InvoiceDetail::create([
            'invoice_id' => $invoice->id,
            'product_id' => $products[0]->id,
            'quantity' => 1,
            'unit_price' => 15.00,
            'subtotal' => 15.00,
            'description' => $products[0]->name
        ]);

        InvoiceDetail::create([
            'invoice_id' => $invoice->id,
            'product_id' => $products[1]->id,
            'quantity' => 3,
            'unit_price' => 5.00,
            'subtotal' => 15.00,
            'description' => $products[1]->name
        ]);

        // 6. Actualizar serie
        $series->increment('current_number');

        $this->info("✅ BOLETA generada:");
        $this->line("   ID: {$invoice->id}");
        $this->line("   Número: {$invoice->series}-{$invoice->number}");
        $this->line("   Cliente: {$customer->name} (DNI: {$customer->document_number})");
        $this->line("   Total: S/ {$invoice->total}");
        
        return $invoice->id;
    }
}
