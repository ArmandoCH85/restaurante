<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\InvoiceDetail;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvoiceModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function invoice_pertenece_a_customer()
    {
        $customer = Customer::factory()->create();
        $invoice = Invoice::factory()->withCustomer($customer)->create();

        $this->assertInstanceOf(Customer::class, $invoice->customer);
        $this->assertEquals($customer->id, $invoice->customer->id);
    }

    /** @test */
    public function invoice_tiene_muchos_detalles()
    {
        $invoice = Invoice::factory()->create();
        $product = Product::factory()->create();
        
        InvoiceDetail::create([
            'invoice_id' => $invoice->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 10.00,
            'subtotal' => 20.00,
            'description' => $product->name
        ]);

        $this->assertCount(1, $invoice->details);
        $this->assertInstanceOf(InvoiceDetail::class, $invoice->details->first());
    }

    /** @test */
    public function invoice_calcula_totales_correctamente()
    {
        $invoice = Invoice::factory()->create([
            'taxable_amount' => 100.00,
            'tax' => 18.00,
            'total' => 118.00
        ]);

        $this->assertEquals(100.00, $invoice->taxable_amount);
        $this->assertEquals(18.00, $invoice->tax);
        $this->assertEquals(118.00, $invoice->total);
    }

    /** @test */
    public function invoice_puede_ser_factura()
    {
        $customer = Customer::factory()->withRUC()->create();
        $invoice = Invoice::factory()->invoice()->withCustomer($customer)->create();

        $this->assertEquals('invoice', $invoice->invoice_type);
        $this->assertTrue($invoice->isInvoice());
    }

    /** @test */
    public function invoice_puede_ser_boleta()
    {
        $customer = Customer::factory()->withDNI()->create();
        $invoice = Invoice::factory()->receipt()->withCustomer($customer)->create();

        $this->assertEquals('receipt', $invoice->invoice_type);
        $this->assertTrue($invoice->isReceipt());
    }

    /** @test */
    public function invoice_puede_ser_nota_de_venta()
    {
        $invoice = Invoice::factory()->salesNote()->create();

        $this->assertEquals('sales_note', $invoice->invoice_type);
        $this->assertTrue($invoice->isSalesNote());
    }

    /** @test */
    public function invoice_puede_estar_pendiente_sunat()
    {
        $invoice = Invoice::factory()->create(['sunat_status' => null]);

        $this->assertTrue($invoice->isPendingSunat());
    }

    /** @test */
    public function invoice_puede_estar_aceptado_sunat()
    {
        $invoice = Invoice::factory()->create(['sunat_status' => 'ACEPTADO']);

        $this->assertTrue($invoice->isAcceptedBySunat());
    }

    /** @test */
    public function invoice_puede_estar_rechazado_sunat()
    {
        $invoice = Invoice::factory()->create(['sunat_status' => 'RECHAZADO']);

        $this->assertTrue($invoice->isRejectedBySunat());
    }

    /** @test */
    public function invoice_scope_solo_facturas_y_boletas()
    {
        Invoice::factory()->invoice()->create();
        Invoice::factory()->receipt()->create();
        Invoice::factory()->salesNote()->create();

        $sunatEligible = Invoice::sunatEligible()->get();

        $this->assertCount(2, $sunatEligible);
        $this->assertTrue($sunatEligible->every(function ($invoice) {
            return in_array($invoice->invoice_type, ['invoice', 'receipt']);
        }));
    }
}
