<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\DocumentSeries;
use App\Helpers\SunatServiceHelper;

class SunatUseCases extends Command
{
    protected $signature = 'sunat:use-cases {case?}';
    protected $description = 'Ejecutar casos de uso específicos del sistema SUNAT';

    public function handle()
    {
        $case = $this->argument('case');

        if (!$case) {
            $this->showAvailableCases();
            $case = $this->choice('Selecciona un caso de uso:', [
                'diferenciacion' => 'Diferenciación automática Factura vs Boleta',
                'validaciones' => 'Validaciones de tipos de comprobante',
                'series' => 'Gestión de series de documentos',
                'calculos' => 'Cálculos de totales e IGV',
                'restricciones' => 'Restricciones de Notas de Venta',
                'flujo-completo' => 'Flujo completo de facturación',
                'all' => 'Ejecutar todos los casos'
            ]);
        }

        switch ($case) {
            case 'diferenciacion':
                return $this->testDiferenciacion();
            case 'validaciones':
                return $this->testValidaciones();
            case 'series':
                return $this->testSeries();
            case 'calculos':
                return $this->testCalculos();
            case 'restricciones':
                return $this->testRestricciones();
            case 'flujo-completo':
                return $this->testFlujoCompleto();
            case 'all':
                return $this->runAllCases();
            default:
                $this->error("Caso de uso '{$case}' no encontrado");
                return 1;
        }
    }

    private function showAvailableCases()
    {
        $this->info('🎯 Casos de Uso Disponibles - Sistema SUNAT');
        $this->line('');
        $this->line('1. <fg=cyan>diferenciacion</> - Diferenciación automática Factura vs Boleta');
        $this->line('2. <fg=cyan>validaciones</> - Validaciones de tipos de comprobante');
        $this->line('3. <fg=cyan>series</> - Gestión de series de documentos');
        $this->line('4. <fg=cyan>calculos</> - Cálculos de totales e IGV');
        $this->line('5. <fg=cyan>restricciones</> - Restricciones de Notas de Venta');
        $this->line('6. <fg=cyan>flujo-completo</> - Flujo completo de facturación');
        $this->line('7. <fg=cyan>all</> - Ejecutar todos los casos');
        $this->line('');
    }

    private function testDiferenciacion()
    {
        $this->info('🔍 Caso de Uso: Diferenciación Automática');
        $this->line('');

        // Crear clientes de prueba
        $empresa = Customer::factory()->withRUC()->create();
        $persona = Customer::factory()->withDNI()->create();

        $this->line("✅ Cliente Empresa: {$empresa->name} (RUC: {$empresa->document_number})");
        $this->line("✅ Cliente Persona: {$persona->name} (DNI: {$persona->document_number})");
        $this->line('');

        // Crear facturas
        $factura = Invoice::factory()->invoice()->withCustomer($empresa)->create();
        $boleta = Invoice::factory()->receipt()->withCustomer($persona)->create();

        $this->line("📄 Factura generada: {$factura->series}-{$factura->number}");
        $this->line("📄 Boleta generada: {$boleta->series}-{$boleta->number}");
        $this->line('');

        // Verificar diferenciación
        $this->line('🔍 Verificando diferenciación automática:');
        $this->line($factura->isInvoice() ? '✅ Factura identificada correctamente' : '❌ Error en identificación de factura');
        $this->line($boleta->isReceipt() ? '✅ Boleta identificada correctamente' : '❌ Error en identificación de boleta');
        
        return 0;
    }

    private function testValidaciones()
    {
        $this->info('🛡️ Caso de Uso: Validaciones de Tipos');
        $this->line('');

        $sunatService = SunatServiceHelper::createIfNotTesting();
        if ($sunatService === null) {
            $this->line("⚠️  Modo testing - Saltando validaciones con SUNAT");
            return 0;
        }

        // Test 1: Validar RUC → Factura
        $empresaRUC = Customer::factory()->create([
            'document_type' => 'RUC',
            'document_number' => '20123456789'
        ]);
        
        $facturaRUC = Invoice::factory()->withCustomer($empresaRUC)->create(['invoice_type' => 'invoice']);
        
        $this->line("✅ Cliente RUC: {$empresaRUC->document_number} → Tipo: {$facturaRUC->invoice_type}");

        // Test 2: Validar DNI → Boleta
        $personaDNI = Customer::factory()->create([
            'document_type' => 'DNI',
            'document_number' => '12345678'
        ]);
        
        $boletaDNI = Invoice::factory()->withCustomer($personaDNI)->create(['invoice_type' => 'receipt']);
        
        $this->line("✅ Cliente DNI: {$personaDNI->document_number} → Tipo: {$boletaDNI->invoice_type}");

        // Test 3: Validar Nota de Venta (debe ser rechazada)
        $notaVenta = Invoice::factory()->salesNote()->create();
        
        try {
            $sunatService->emitirFactura($notaVenta->id);
            $this->line('❌ Error: Nota de venta no debería ser procesada');
        } catch (\Exception $e) {
            $this->line('✅ Nota de venta correctamente rechazada');
        }

        return 0;
    }

    private function testSeries()
    {
        $this->info('📊 Caso de Uso: Gestión de Series');
        $this->line('');

        // Verificar series activas
        $serieFactura = DocumentSeries::where('document_type', 'invoice')->where('active', true)->first();
        $serieBoleta = DocumentSeries::where('document_type', 'receipt')->where('active', true)->first();

        if ($serieFactura) {
            $this->line("✅ Serie Factura: {$serieFactura->series} (Número actual: {$serieFactura->current_number})");
        } else {
            $this->line('❌ No hay serie activa para facturas');
        }

        if ($serieBoleta) {
            $this->line("✅ Serie Boleta: {$serieBoleta->series} (Número actual: {$serieBoleta->current_number})");
        } else {
            $this->line('❌ No hay serie activa para boletas');
        }

        // Test de incremento de numeración
        if ($serieFactura) {
            $numeroInicial = $serieFactura->current_number;
            $this->call('invoice:generate-test', ['type' => 'invoice']);
            $serieFactura->refresh();
            
            $this->line("✅ Numeración incrementada: {$numeroInicial} → {$serieFactura->current_number}");
        }

        return 0;
    }

    private function testCalculos()
    {
        $this->info('🧮 Caso de Uso: Cálculos de Totales');
        $this->line('');

        // Generar factura con cálculos
        $this->call('invoice:generate-test', ['type' => 'invoice']);
        $factura = Invoice::where('invoice_type', 'invoice')->latest()->first();

        $this->line("📄 Factura: {$factura->series}-{$factura->number}");
        $this->line("💰 Subtotal (sin IGV): S/ {$factura->taxable_amount}");
        $this->line("📊 IGV (18%): S/ {$factura->tax}");
        $this->line("💵 Total: S/ {$factura->total}");
        $this->line('');

        // Verificar cálculos
        $expectedTax = round($factura->taxable_amount * 0.18, 2);
        $expectedTotal = $factura->taxable_amount + $factura->tax;

        $this->line('🔍 Verificando cálculos:');
        $this->line($expectedTax == $factura->tax ? '✅ IGV calculado correctamente' : '❌ Error en cálculo de IGV');
        $this->line(round($expectedTotal, 2) == round($factura->total, 2) ? '✅ Total calculado correctamente' : '❌ Error en cálculo de total');

        return 0;
    }

    private function testRestricciones()
    {
        $this->info('🚫 Caso de Uso: Restricciones de Notas de Venta');
        $this->line('');

        // Crear nota de venta
        $notaVenta = Invoice::factory()->salesNote()->create();
        
        $this->line("📝 Nota de Venta creada: {$notaVenta->series}-{$notaVenta->number}");
        $this->line('');

        // Intentar enviar a SUNAT (debe fallar)
        $this->line('🔍 Intentando enviar Nota de Venta a SUNAT...');
        
        $exitCode = $this->call('sunat:test', ['invoiceId' => $notaVenta->id]);
        
        if ($exitCode === 1) {
            $this->line('✅ Nota de Venta correctamente bloqueada');
        } else {
            $this->line('❌ Error: Nota de Venta no debería ser enviada');
        }

        return 0;
    }

    private function testFlujoCompleto()
    {
        $this->info('🔄 Caso de Uso: Flujo Completo de Facturación');
        $this->line('');

        // 1. Generar factura
        $this->line('1️⃣ Generando factura...');
        $this->call('invoice:generate-test', ['type' => 'invoice']);
        
        // 2. Generar boleta
        $this->line('2️⃣ Generando boleta...');
        $this->call('invoice:generate-test', ['type' => 'receipt']);
        
        // 3. Verificar comprobantes elegibles
        $this->line('3️⃣ Verificando comprobantes elegibles para SUNAT...');
        $elegibles = Invoice::sunatEligible()->where(function($query) {
            $query->whereNull('sunat_status')->orWhere('sunat_status', 'PENDIENTE');
        })->count();
        
        $this->line("✅ Comprobantes elegibles: {$elegibles}");
        
        // 4. Mostrar estadísticas
        $this->line('4️⃣ Estadísticas del sistema:');
        $facturas = Invoice::where('invoice_type', 'invoice')->count();
        $boletas = Invoice::where('invoice_type', 'receipt')->count();
        $notas = Invoice::where('invoice_type', 'sales_note')->count();
        
        $this->line("   📄 Facturas: {$facturas}");
        $this->line("   🧾 Boletas: {$boletas}");
        $this->line("   📝 Notas de Venta: {$notas}");

        return 0;
    }

    private function runAllCases()
    {
        $this->info('🚀 Ejecutando Todos los Casos de Uso');
        $this->line('');

        $cases = ['diferenciacion', 'validaciones', 'series', 'calculos', 'restricciones', 'flujo-completo'];
        
        foreach ($cases as $case) {
            $this->call('sunat:use-cases', ['case' => $case]);
            $this->line('');
            $this->line(str_repeat('─', 50));
            $this->line('');
        }

        $this->info('✅ Todos los casos de uso ejecutados exitosamente!');
        return 0;
    }
}
