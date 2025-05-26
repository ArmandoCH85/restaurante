<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Product;
use App\Models\DocumentSeries;
use App\Models\AppSetting;
use Illuminate\Foundation\Testing\WithFaker;

class SunatCommandsTest extends TestCase
{
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTestData();
    }

    private function setupTestData()
    {
        // Verificar que existan configuraciones básicas (sin crear duplicados)
        $this->ensureSetting('FacturacionElectronica', 'environment', 'beta');
        $this->ensureSetting('FacturacionElectronica', 'ruc', '20123456789');
        $this->ensureSetting('FacturacionElectronica', 'razon_social', 'Q RICO SAC POLLO TEST');

        // Verificar que existan series básicas (sin crear duplicados)
        $this->ensureSeries('invoice', 'F001', 'Facturas Test');
        $this->ensureSeries('receipt', 'B001', 'Boletas Test');

        // Verificar que existan productos (crear solo si no hay)
        if (Product::count() < 3) {
            Product::factory()->count(3)->create();
        }
    }

    private function ensureSetting($tab, $key, $defaultValue)
    {
        if (!AppSetting::where('tab', $tab)->where('key', $key)->exists()) {
            AppSetting::create([
                'tab' => $tab,
                'key' => $key,
                'value' => $defaultValue
            ]);
        }
    }

    private function ensureSeries($type, $series, $description)
    {
        if (!DocumentSeries::where('document_type', $type)->where('series', $series)->exists()) {
            DocumentSeries::create([
                'document_type' => $type,
                'series' => $series,
                'current_number' => 1,
                'active' => true,
                'description' => $description
            ]);
        }
    }

    /** @test */
    public function comando_genera_factura_correctamente()
    {
        $this->artisan('invoice:generate-test invoice')
            ->expectsOutput('🏭 Generando FACTURA de prueba...')
            ->assertExitCode(1); // Exit code 1 porque retorna el ID

        // Verificar que se creó la factura
        $invoice = Invoice::where('invoice_type', 'invoice')->latest()->first();
        $this->assertNotNull($invoice);
        $this->assertEquals('invoice', $invoice->invoice_type);
        $this->assertEquals('F001', $invoice->series);
        $this->assertGreaterThan(0, $invoice->total);

        // Verificar que tiene detalles
        $this->assertGreaterThan(0, $invoice->details()->count());

        // Verificar que el cliente tiene RUC
        $this->assertContains($invoice->customer->document_type, ['RUC', '6']);
    }

    /** @test */
    public function comando_genera_boleta_correctamente()
    {
        $this->artisan('invoice:generate-test receipt')
            ->expectsOutput('👤 Generando BOLETA de prueba...')
            ->assertExitCode(1); // Exit code 1 porque retorna el ID

        // Verificar que se creó la boleta
        $invoice = Invoice::where('invoice_type', 'receipt')->latest()->first();
        $this->assertNotNull($invoice);
        $this->assertEquals('receipt', $invoice->invoice_type);
        $this->assertEquals('B001', $invoice->series);
        $this->assertGreaterThan(0, $invoice->total);

        // Verificar que tiene detalles
        $this->assertGreaterThan(0, $invoice->details()->count());

        // Verificar que el cliente tiene DNI
        $this->assertContains($invoice->customer->document_type, ['DNI', '1']);
    }

    /** @test */
    public function comando_test_sunat_muestra_facturas_disponibles()
    {
        // Crear facturas de prueba
        $customer = Customer::factory()->withRUC()->create();
        Invoice::factory()->invoice()->withCustomer($customer)->create([
            'sunat_status' => null
        ]);

        $this->artisan('sunat:test')
            ->expectsOutput('Comprobantes disponibles para SUNAT (solo Boletas y Facturas):')
            ->assertExitCode(0);
    }

    /** @test */
    public function comando_test_sunat_rechaza_notas_de_venta()
    {
        $customer = Customer::factory()->create();
        $salesNote = Invoice::factory()->salesNote()->withCustomer($customer)->create();

        $this->artisan('sunat:test', ['invoiceId' => $salesNote->id])
            ->expectsOutput('❌ Solo se pueden enviar Boletas y Facturas a SUNAT.')
            ->expectsOutput('💡 Las Notas de Venta son documentos internos y no se envían a SUNAT.')
            ->assertExitCode(1);
    }

    /** @test */
    public function comando_test_sunat_valida_factura_existe()
    {
        $this->artisan('sunat:test', ['invoiceId' => 99999])
            ->expectsOutput('Factura con ID 99999 no encontrada')
            ->assertExitCode(1);
    }

    /** @test */
    public function comando_actualiza_serie_al_generar()
    {
        $series = DocumentSeries::where('document_type', 'invoice')->first();
        $initialNumber = $series->current_number;

        $this->artisan('invoice:generate-test invoice');

        $series->refresh();
        $this->assertEquals($initialNumber + 1, $series->current_number);
    }

    /** @test */
    public function comando_calcula_totales_correctamente()
    {
        $this->artisan('invoice:generate-test invoice');

        $invoice = Invoice::where('invoice_type', 'invoice')->latest()->first();

        // Verificar que los totales están calculados
        $this->assertGreaterThan(0, $invoice->taxable_amount);
        $this->assertGreaterThan(0, $invoice->tax);
        $this->assertEquals(
            round($invoice->taxable_amount + $invoice->tax, 2),
            round($invoice->total, 2)
        );

        // Verificar que el IGV es aproximadamente 18%
        $expectedTax = round($invoice->taxable_amount * 0.18, 2);
        $this->assertEquals($expectedTax, $invoice->tax);
    }
}
