<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;

class CheckInvoiceData extends Command
{
    protected $signature = 'invoice:check {invoice_id}';
    protected $description = 'Verificar datos de una factura';

    public function handle()
    {
        $invoiceId = $this->argument('invoice_id');
        
        $invoice = Invoice::with(['details.product', 'customer'])->find($invoiceId);
        
        if (!$invoice) {
            $this->error("Factura {$invoiceId} no encontrada");
            return 1;
        }
        
        $this->info("📋 DATOS DE LA FACTURA {$invoiceId}");
        $this->line("Serie-Número: {$invoice->series}-{$invoice->number}");
        $this->line("Cliente: {$invoice->customer->name}");
        $this->line("Total: S/ {$invoice->total}");
        $this->line("Subtotal: S/ {$invoice->subtotal}");
        $this->line("IGV: S/ {$invoice->tax}");
        $this->line("");
        
        $this->info("📦 DETALLES DE PRODUCTOS:");
        foreach ($invoice->details as $detail) {
            $this->line("- {$detail->product->name}");
            $this->line("  Cantidad: {$detail->quantity}");
            $this->line("  Precio Unitario: S/ {$detail->unit_price}");
            $this->line("  Subtotal: S/ {$detail->subtotal}");
            $this->line("");
        }
        
        // Verificar cálculos
        $totalCalculado = $invoice->details->sum('subtotal');
        $igvCalculado = round($totalCalculado * 0.18, 2);
        $totalConIgv = $totalCalculado + $igvCalculado;
        
        $this->info("🧮 VERIFICACIÓN DE CÁLCULOS:");
        $this->line("Suma de subtotales: S/ {$totalCalculado}");
        $this->line("IGV calculado (18%): S/ {$igvCalculado}");
        $this->line("Total con IGV: S/ {$totalConIgv}");
        $this->line("Total en BD: S/ {$invoice->total}");
        $this->line("Diferencia: S/ " . abs($totalConIgv - $invoice->total));
        
        return 0;
    }
}
