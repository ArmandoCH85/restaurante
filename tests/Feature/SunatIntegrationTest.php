<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Product;
use App\Models\InvoiceDetail;
use App\Models\DocumentSeries;
use App\Models\AppSetting;
use App\Services\SunatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;

class SunatIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setupTestEnvironment();
    }

    private function setupTestEnvironment()
    {
        // Configurar settings de SUNAT
        AppSetting::create([
            'tab' => 'FacturacionElectronica',
            'key' => 'environment',
            'value' => 'beta'
        ]);

        AppSetting::create([
            'tab' => 'FacturacionElectronica',
            'key' => 'ruc',
            'value' => '20123456789'
        ]);

        AppSetting::create([
            'tab' => 'FacturacionElectronica',
            'key' => 'razon_social',
            'value' => 'EMPRESA TEST S.A.C.'
        ]);

        // Crear series
        DocumentSeries::create([
            'document_type' => 'invoice',
            'series' => 'F001',
            'current_number' => 1,
            'active' => true,
            'description' => 'Facturas'
        ]);

        DocumentSeries::create([
            'document_type' => 'receipt',
            'series' => 'B001',
            'current_number' => 1,
            'active' => true,
            'description' => 'Boletas'
        ]);
    }

    /** @test */
    public function flujo_completo_generacion_y_validacion_factura()
    {
        // 1. Generar factura
        $this->artisan('invoice:generate-test invoice')
            ->assertExitCode(1);

        // 2. Verificar que se creó correctamente
        $invoice = Invoice::where('invoice_type', 'invoice')->latest()->first();
        $this->assertNotNull($invoice);
        $this->assertEquals('invoice', $invoice->invoice_type);
        $this->assertTrue($invoice->isPendingSunat());

        // 3. Verificar que tiene cliente con RUC
        $this->assertContains($invoice->customer->document_type, ['RUC', '6']);

        // 4. Verificar que tiene detalles
        $this->assertGreaterThan(0, $invoice->details()->count());

        // 5. Verificar cálculos
        $expectedTotal = $invoice->taxable_amount + $invoice->tax;
        $this->assertEquals(round($expectedTotal, 2), round($invoice->total, 2));
    }

    /** @test */
    public function flujo_completo_generacion_y_validacion_boleta()
    {
        // 1. Generar boleta
        $this->artisan('invoice:generate-test receipt')
            ->assertExitCode(1);

        // 2. Verificar que se creó correctamente
        $invoice = Invoice::where('invoice_type', 'receipt')->latest()->first();
        $this->assertNotNull($invoice);
        $this->assertEquals('receipt', $invoice->invoice_type);
        $this->assertTrue($invoice->isPendingSunat());

        // 3. Verificar que tiene cliente con DNI
        $this->assertContains($invoice->customer->document_type, ['DNI', '1']);

        // 4. Verificar que tiene detalles
        $this->assertGreaterThan(0, $invoice->details()->count());
    }

    /** @test */
    public function sistema_diferencia_tipos_comprobante_automaticamente()
    {
        // Crear cliente empresa
        $empresa = Customer::factory()->withRUC()->create();
        $facturaEmpresa = Invoice::factory()->withCustomer($empresa)->create([
            'invoice_type' => 'invoice'
        ]);

        // Crear cliente persona
        $persona = Customer::factory()->withDNI()->create();
        $boletaPersona = Invoice::factory()->withCustomer($persona)->create([
            'invoice_type' => 'receipt'
        ]);

        // Verificar diferenciación
        $this->assertTrue($facturaEmpresa->isInvoice());
        $this->assertTrue($boletaPersona->isReceipt());

        // Verificar que solo facturas y boletas son elegibles para SUNAT
        $elegibles = Invoice::sunatEligible()->get();
        $this->assertCount(2, $elegibles);
    }

    /** @test */
    public function sistema_bloquea_notas_de_venta_correctamente()
    {
        $notaVenta = Invoice::factory()->salesNote()->create();

        $this->artisan('sunat:test', ['invoiceId' => $notaVenta->id])
            ->expectsOutput('❌ Solo se pueden enviar Boletas y Facturas a SUNAT.')
            ->assertExitCode(1);
    }

    /** @test */
    public function sistema_valida_series_activas()
    {
        // Desactivar todas las series de factura
        DocumentSeries::where('document_type', 'invoice')->update(['active' => false]);

        $this->artisan('invoice:generate-test invoice')
            ->expectsOutput('🏭 Generando FACTURA de prueba...')
            ->assertExitCode(1);

        // Verificar que no se creó la factura por falta de serie activa
        $invoice = Invoice::where('invoice_type', 'invoice')->latest()->first();
        $this->assertNull($invoice);
    }

    /** @test */
    public function comando_lista_solo_comprobantes_elegibles()
    {
        // Crear diferentes tipos de comprobantes
        $factura = Invoice::factory()->invoice()->create(['sunat_status' => null]);
        $boleta = Invoice::factory()->receipt()->create(['sunat_status' => null]);
        $notaVenta = Invoice::factory()->salesNote()->create(['sunat_status' => null]);

        $this->artisan('sunat:test')
            ->expectsOutput('Comprobantes disponibles para SUNAT (solo Boletas y Facturas):')
            ->assertExitCode(0);

        // Verificar que solo muestra facturas y boletas (no notas de venta)
        $elegibles = Invoice::sunatEligible()
            ->where(function($query) {
                $query->where('sunat_status', 'PENDIENTE')
                      ->orWhereNull('sunat_status');
            })
            ->count();

        $this->assertEquals(2, $elegibles); // Solo factura y boleta
    }

    /** @test */
    public function sistema_actualiza_numeracion_series_correctamente()
    {
        $serieFactura = DocumentSeries::where('document_type', 'invoice')->first();
        $numeroInicial = $serieFactura->current_number;

        // Generar 3 facturas
        for ($i = 0; $i < 3; $i++) {
            $this->artisan('invoice:generate-test invoice');
        }

        $serieFactura->refresh();
        $this->assertEquals($numeroInicial + 3, $serieFactura->current_number);

        // Verificar que las facturas tienen números consecutivos
        $facturas = Invoice::where('invoice_type', 'invoice')
            ->orderBy('created_at')
            ->get();

        $this->assertCount(3, $facturas);
        
        for ($i = 0; $i < 3; $i++) {
            $expectedNumber = str_pad($numeroInicial + $i + 1, 8, '0', STR_PAD_LEFT);
            $this->assertEquals($expectedNumber, $facturas[$i]->number);
        }
    }
}
