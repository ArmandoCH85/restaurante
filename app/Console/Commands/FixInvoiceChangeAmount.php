<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class FixInvoiceChangeAmount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:fix-change-amount {invoice_id?} {--all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige el cálculo del vuelto en facturas existentes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $invoiceId = $this->argument('invoice_id');
        $fixAll = $this->option('all');

        if ($fixAll) {
            $this->fixAllInvoices();
        } elseif ($invoiceId) {
            $this->fixSingleInvoice($invoiceId);
        } else {
            // Buscar la factura específica NV002-00000716
            $invoice = Invoice::where('series', 'NV002')
                ->where('number', '00000716')
                ->first();
                
            if ($invoice) {
                $this->fixSingleInvoice($invoice->id);
            } else {
                $this->error('❌ No se encontró la factura NV002-00000716');
            }
        }
    }

    private function fixSingleInvoice($invoiceId)
    {
        $invoice = Invoice::with(['order.payments'])->find($invoiceId);

        if (!$invoice) {
            $this->error("❌ No se encontró la factura con ID: {$invoiceId}");
            return;
        }

        $this->info("🔍 Verificando factura {$invoice->series}-{$invoice->number}...");
        $this->info("📋 Datos actuales:");
        $this->line("   - Total: S/ " . number_format($invoice->total, 2));
        $this->line("   - Monto pagado: S/ " . number_format($invoice->payment_amount, 2));
        $this->line("   - Vuelto actual: S/ " . number_format($invoice->change_amount, 2));
        $this->line("   - Método de pago: {$invoice->payment_method}");

        if (!$invoice->order) {
            $this->error("❌ No se encontró la orden asociada a esta factura");
            return;
        }

        $totalPaid = $invoice->order->getTotalPaid();
        $payments = $invoice->order->payments;

        $this->info("💰 Pagos de la orden #{$invoice->order->id}:");
        foreach ($payments as $payment) {
            $this->line("   - {$payment->payment_method}: S/ " . number_format($payment->amount, 2));
        }
        $this->line("   Total pagado: S/ " . number_format($totalPaid, 2));

        // Calcular el vuelto correcto
        $correctChange = 0;
        $hasCashPayment = $payments->where('payment_method', 'cash')->isNotEmpty();

        if ($hasCashPayment && $totalPaid > $invoice->total) {
            $correctChange = $totalPaid - $invoice->total;
            $this->info("🧮 Cálculo del vuelto:");
            $this->line("   - Total pagado: S/ " . number_format($totalPaid, 2));
            $this->line("   - Total factura: S/ " . number_format($invoice->total, 2));
            $this->line("   - Vuelto correcto: S/ " . number_format($correctChange, 2));

            // Actualizar la factura si es necesario
            if (abs($correctChange - $invoice->change_amount) > 0.01 || abs($totalPaid - $invoice->payment_amount) > 0.01) {
                $this->info("🔧 Actualizando datos de la factura...");

                DB::transaction(function () use ($invoice, $totalPaid, $correctChange) {
                    $invoice->update([
                        'payment_amount' => $totalPaid,
                        'change_amount' => $correctChange
                    ]);
                });

                $this->info("✅ Factura actualizada correctamente!");
                $this->line("   - Nuevo monto pagado: S/ " . number_format($totalPaid, 2));
                $this->line("   - Nuevo vuelto: S/ " . number_format($correctChange, 2));
            } else {
                $this->info("ℹ️ Los datos ya están correctos, no se necesita actualización.");
            }
        } else {
            $this->info("ℹ️ No hay vuelto para esta factura (sin pago en efectivo o sin exceso)");
        }

        $this->info("🎯 Para verificar el resultado, visita:");
        $this->line("http://restaurante.test/print/invoice/{$invoice->id}");
    }

    private function fixAllInvoices()
    {
        $this->info("🔄 Corrigiendo todas las facturas con problemas de vuelto...");
        
        $invoices = Invoice::with(['order.payments'])
            ->whereHas('order.payments', function($q) {
                $q->where('payment_method', 'cash');
            })
            ->get();

        $fixed = 0;
        
        foreach ($invoices as $invoice) {
            if (!$invoice->order) continue;
            
            $totalPaid = $invoice->order->getTotalPaid();
            $hasCashPayment = $invoice->order->payments->where('payment_method', 'cash')->isNotEmpty();
            
            $correctChange = 0;
            if ($hasCashPayment && $totalPaid > $invoice->total) {
                $correctChange = $totalPaid - $invoice->total;
            }
            
            if (abs($correctChange - $invoice->change_amount) > 0.01 || abs($totalPaid - $invoice->payment_amount) > 0.01) {
                DB::transaction(function () use ($invoice, $totalPaid, $correctChange) {
                    $invoice->update([
                        'payment_amount' => $totalPaid,
                        'change_amount' => $correctChange
                    ]);
                });
                
                $this->line("✅ Corregida: {$invoice->series}-{$invoice->number} - Vuelto: S/ " . number_format($correctChange, 2));
                $fixed++;
            }
        }
        
        $this->info("🎉 Proceso completado. {$fixed} facturas corregidas.");
    }
}