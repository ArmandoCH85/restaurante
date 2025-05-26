<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\InvoiceDetail;
use App\Models\Product;

class SunatInvoiceModelTest extends TestCase
{
    /** @test */
    public function scope_sunat_eligible_filtra_correctamente()
    {
        // Crear comprobantes de diferentes tipos
        $customer = Customer::create([
            'document_type' => 'RUC',
            'document_number' => '20999999998',
            'name' => 'EMPRESA TEST'
        ]);

        $factura = Invoice::create([
            'customer_id' => $customer->id,
            'invoice_type' => 'invoice',
            'series' => 'F001',
            'number' => '00000001',
            'issue_date' => now()->format('Y-m-d'),
            'taxable_amount' => 100.00,
            'tax' => 18.00,
            'total' => 118.00,
            'tax_authority_status' => 'pending'
        ]);

        $boleta = Invoice::create([
            'customer_id' => $customer->id,
            'invoice_type' => 'receipt',
            'series' => 'B001',
            'number' => '00000001',
            'issue_date' => now()->format('Y-m-d'),
            'taxable_amount' => 50.00,
            'tax' => 9.00,
            'total' => 59.00,
            'tax_authority_status' => 'pending'
        ]);

        $notaVenta = Invoice::create([
            'customer_id' => $customer->id,
            'invoice_type' => 'sales_note',
            'series' => 'NV01',
            'number' => '00000001',
            'issue_date' => now()->format('Y-m-d'),
            'taxable_amount' => 25.00,
            'tax' => 4.50,
            'total' => 29.50,
            'tax_authority_status' => 'pending'
        ]);

        // Test del scope
        $elegibles = Invoice::sunatEligible()->get();
        
        $this->assertCount(2, $elegibles, 'Solo facturas y boletas deben ser elegibles');
        $this->assertTrue($elegibles->contains($factura), 'Factura debe ser elegible');
        $this->assertTrue($elegibles->contains($boleta), 'Boleta debe ser elegible');
        $this->assertFalse($elegibles->contains($notaVenta), 'Nota de venta NO debe ser elegible');

        // Limpiar
        $factura->delete();
        $boleta->delete();
        $notaVenta->delete();
        $customer->delete();
    }

    /** @test */
    public function metodo_get_formatted_number_funciona()
    {
        $customer = Customer::create([
            'document_type' => 'DNI',
            'document_number' => '99999997',
            'name' => 'CLIENTE TEST'
        ]);

        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'invoice_type' => 'receipt',
            'series' => 'B001',
            'number' => '00000123',
            'issue_date' => now()->format('Y-m-d'),
            'taxable_amount' => 100.00,
            'tax' => 18.00,
            'total' => 118.00,
            'tax_authority_status' => 'pending'
        ]);

        $this->assertEquals('B001-00000123', $invoice->formatted_number);
        $this->assertEquals('B001-00000123', $invoice->getFormattedNumberAttribute());

        // Limpiar
        $invoice->delete();
        $customer->delete();
    }

    /** @test */
    public function metodo_get_sunat_document_type_funciona()
    {
        $customer = Customer::create([
            'document_type' => 'RUC',
            'document_number' => '20999999996',
            'name' => 'EMPRESA TEST'
        ]);

        // Test factura
        $factura = Invoice::create([
            'customer_id' => $customer->id,
            'invoice_type' => 'invoice',
            'series' => 'F001',
            'number' => '00000001',
            'issue_date' => now()->format('Y-m-d'),
            'taxable_amount' => 100.00,
            'tax' => 18.00,
            'total' => 118.00,
            'tax_authority_status' => 'pending'
        ]);

        // Test boleta
        $boleta = Invoice::create([
            'customer_id' => $customer->id,
            'invoice_type' => 'receipt',
            'series' => 'B001',
            'number' => '00000001',
            'issue_date' => now()->format('Y-m-d'),
            'taxable_amount' => 50.00,
            'tax' => 9.00,
            'total' => 59.00,
            'tax_authority_status' => 'pending'
        ]);

        $this->assertEquals('01', $factura->getSunatDocumentType(), 'Factura debe ser tipo 01');
        $this->assertEquals('03', $boleta->getSunatDocumentType(), 'Boleta debe ser tipo 03');

        // Limpiar
        $factura->delete();
        $boleta->delete();
        $customer->delete();
    }

    /** @test */
    public function metodo_generate_hash_funciona()
    {
        $customer = Customer::create([
            'document_type' => 'RUC',
            'document_number' => '20999999995',
            'name' => 'EMPRESA TEST HASH'
        ]);

        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'invoice_type' => 'invoice',
            'series' => 'F001',
            'number' => '00000001',
            'issue_date' => now()->format('Y-m-d'),
            'taxable_amount' => 100.00,
            'tax' => 18.00,
            'total' => 118.00,
            'tax_authority_status' => 'pending'
        ]);

        $hash = $invoice->generateHash();

        $this->assertNotEmpty($hash, 'Hash no debe estar vacío');
        $this->assertEquals(64, strlen($hash), 'Hash SHA256 debe tener 64 caracteres');
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $hash, 
            'Hash debe ser hexadecimal válido');

        // El mismo comprobante debe generar el mismo hash
        $hash2 = $invoice->generateHash();
        $this->assertEquals($hash, $hash2, 'El mismo comprobante debe generar el mismo hash');

        // Limpiar
        $invoice->delete();
        $customer->delete();
    }

    /** @test */
    public function relacion_con_customer_funciona()
    {
        $customer = Customer::create([
            'document_type' => 'DNI',
            'document_number' => '99999994',
            'name' => 'CLIENTE RELACION TEST'
        ]);

        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'invoice_type' => 'receipt',
            'series' => 'B001',
            'number' => '00000001',
            'issue_date' => now()->format('Y-m-d'),
            'taxable_amount' => 100.00,
            'tax' => 18.00,
            'total' => 118.00,
            'tax_authority_status' => 'pending'
        ]);

        $this->assertInstanceOf(Customer::class, $invoice->customer);
        $this->assertEquals($customer->id, $invoice->customer->id);
        $this->assertEquals('CLIENTE RELACION TEST', $invoice->customer->name);

        // Limpiar
        $invoice->delete();
        $customer->delete();
    }

    /** @test */
    public function relacion_con_details_funciona()
    {
        $customer = Customer::create([
            'document_type' => 'RUC',
            'document_number' => '20999999993',
            'name' => 'EMPRESA DETALLES TEST'
        ]);

        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'invoice_type' => 'invoice',
            'series' => 'F001',
            'number' => '00000001',
            'issue_date' => now()->format('Y-m-d'),
            'taxable_amount' => 100.00,
            'tax' => 18.00,
            'total' => 118.00,
            'tax_authority_status' => 'pending'
        ]);

        // Crear detalles
        InvoiceDetail::create([
            'invoice_id' => $invoice->id,
            'product_id' => null,
            'quantity' => 2,
            'unit_price' => 25.00,
            'subtotal' => 50.00,
            'description' => 'Producto Test 1'
        ]);

        InvoiceDetail::create([
            'invoice_id' => $invoice->id,
            'product_id' => null,
            'quantity' => 1,
            'unit_price' => 50.00,
            'subtotal' => 50.00,
            'description' => 'Producto Test 2'
        ]);

        $this->assertCount(2, $invoice->details);
        $this->assertEquals(100.00, $invoice->details->sum('subtotal'));

        // Limpiar
        $invoice->details()->delete();
        $invoice->delete();
        $customer->delete();
    }

    /** @test */
    public function campos_sunat_existen_en_modelo()
    {
        $customer = Customer::create([
            'document_type' => 'RUC',
            'document_number' => '20999999992',
            'name' => 'EMPRESA CAMPOS TEST'
        ]);

        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'invoice_type' => 'invoice',
            'series' => 'F001',
            'number' => '00000001',
            'issue_date' => now()->format('Y-m-d'),
            'taxable_amount' => 100.00,
            'tax' => 18.00,
            'total' => 118.00,
            'tax_authority_status' => 'pending',
            'sunat_status' => 'PENDIENTE',
            'sunat_code' => null,
            'sunat_description' => null,
            'xml_path' => null,
            'pdf_path' => null,
            'cdr_path' => null,
            'hash_sign' => null,
            'sent_at' => null
        ]);

        // Verificar que los campos SUNAT existen
        $this->assertArrayHasKey('sunat_status', $invoice->getAttributes());
        $this->assertArrayHasKey('sunat_code', $invoice->getAttributes());
        $this->assertArrayHasKey('sunat_description', $invoice->getAttributes());
        $this->assertArrayHasKey('xml_path', $invoice->getAttributes());
        $this->assertArrayHasKey('pdf_path', $invoice->getAttributes());
        $this->assertArrayHasKey('cdr_path', $invoice->getAttributes());
        $this->assertArrayHasKey('hash_sign', $invoice->getAttributes());
        $this->assertArrayHasKey('sent_at', $invoice->getAttributes());

        $this->assertEquals('PENDIENTE', $invoice->sunat_status);

        // Limpiar
        $invoice->delete();
        $customer->delete();
    }
}
