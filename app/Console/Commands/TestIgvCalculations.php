<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Console\Command;

class TestIgvCalculations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:igv-calculations 
                            {--invoice-id= : ID de factura específica para probar}
                            {--order-id= : ID de orden específica para probar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prueba los cálculos de IGV incluido en órdenes y facturas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧮 PRUEBA DE CÁLCULOS DE IGV INCLUIDO');
        $this->line('==========================================');

        $invoiceId = $this->option('invoice-id');
        $orderId = $this->option('order-id');

        if ($invoiceId) {
            $this->testInvoice($invoiceId);
        } elseif ($orderId) {
            $this->testOrder($orderId);
        } else {
            // Probar con ejemplos aleatorios
            $this->testRandomSamples();
        }

        return 0;
    }

    private function testInvoice($invoiceId)
    {
        $invoice = Invoice::find($invoiceId);
        
        if (!$invoice) {
            $this->error("❌ Factura #{$invoiceId} no encontrada");
            return;
        }

        $this->info("📄 FACTURA #{$invoice->id} ({$invoice->invoice_type})");
        $this->line("Número: {$invoice->series}-{$invoice->number}");
        $this->line("Fecha: {$invoice->issue_date}");
        $this->newLine();

        // Valores actuales en BD
        $this->info("💾 VALORES EN BASE DE DATOS:");
        $this->line("Subtotal (BD): S/ " . number_format($invoice->taxable_amount, 2));
        $this->line("IGV (BD):      S/ " . number_format($invoice->tax, 2));
        $this->line("Total (BD):    S/ " . number_format($invoice->total, 2));
        $this->newLine();

        // Valores calculados correctamente
        $this->info("✅ VALORES CALCULADOS CORRECTAMENTE:");
        $this->line("Subtotal:      S/ " . number_format($invoice->correct_subtotal, 2));
        $this->line("IGV:           S/ " . number_format($invoice->correct_igv, 2));
        $this->line("Total:         S/ " . number_format($invoice->total, 2));
        $this->newLine();

        // Diferencias
        $diffSubtotal = $invoice->correct_subtotal - $invoice->taxable_amount;
        $diffIgv = $invoice->correct_igv - $invoice->tax;

        $this->info("📊 DIFERENCIAS:");
        $this->line("Subtotal: S/ " . number_format($diffSubtotal, 2));
        $this->line("IGV:      S/ " . number_format($diffIgv, 2));
        
        if (abs($diffSubtotal) > 0.01 || abs($diffIgv) > 0.01) {
            $this->warn("⚠️  Se detectaron diferencias significativas");
        } else {
            $this->info("✅ Los cálculos están correctos");
        }

        // Validación matemática
        $calculatedTotal = $invoice->correct_subtotal + $invoice->correct_igv;
        $totalDiff = abs($calculatedTotal - $invoice->total);
        
        $this->newLine();
        $this->info("🔍 VALIDACIÓN MATEMÁTICA:");
        $this->line("Subtotal + IGV = S/ " . number_format($calculatedTotal, 2));
        $this->line("Total real =     S/ " . number_format($invoice->total, 2));
        $this->line("Diferencia =     S/ " . number_format($totalDiff, 2));
        
        if ($totalDiff <= 0.01) {
            $this->info("✅ Validación matemática CORRECTA");
        } else {
            $this->error("❌ Validación matemática INCORRECTA");
        }
    }

    private function testOrder($orderId)
    {
        $order = Order::find($orderId);
        
        if (!$order) {
            $this->error("❌ Orden #{$orderId} no encontrada");
            return;
        }

        $this->info("📋 ORDEN #{$order->id}");
        $this->line("Mesa: " . ($order->table ? "Mesa #{$order->table->number}" : "Venta rápida"));
        $this->line("Estado: {$order->status}");
        $this->line("Fecha: {$order->order_datetime}");
        $this->newLine();

        $this->info("💾 VALORES ACTUALES:");
        $this->line("Subtotal: S/ " . number_format($order->subtotal, 2));
        $this->line("IGV:      S/ " . number_format($order->tax, 2));
        $this->line("Total:    S/ " . number_format($order->total, 2));
        $this->newLine();

        // Validación matemática
        $calculatedTotal = $order->subtotal + $order->tax;
        $totalDiff = abs($calculatedTotal - $order->total);
        
        $this->info("🔍 VALIDACIÓN MATEMÁTICA:");
        $this->line("Subtotal + IGV = S/ " . number_format($calculatedTotal, 2));
        $this->line("Total real =     S/ " . number_format($order->total, 2));
        $this->line("Diferencia =     S/ " . number_format($totalDiff, 2));
        
        if ($totalDiff <= 0.01) {
            $this->info("✅ Validación matemática CORRECTA");
        } else {
            $this->error("❌ Validación matemática INCORRECTA");
        }

        // Mostrar detalles
        $this->newLine();
        $this->info("📝 DETALLES DE LA ORDEN:");
        foreach ($order->orderDetails as $detail) {
            $this->line("- {$detail->quantity}x {$detail->product->name}: S/ " . number_format($detail->subtotal, 2));
        }
    }

    private function testRandomSamples()
    {
        $this->info("🎲 PROBANDO CON MUESTRAS ALEATORIAS");
        $this->newLine();

        // Probar con una factura reciente
        $invoice = Invoice::latest()->first();
        if ($invoice) {
            $this->testInvoice($invoice->id);
            $this->newLine();
        }

        // Probar con una orden reciente
        $order = Order::latest()->first();
        if ($order) {
            $this->testOrder($order->id);
        }
    }
}
