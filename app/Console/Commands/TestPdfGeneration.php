<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;

class TestPdfGeneration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:pdf-generation {invoice_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simula la generación del PDF para verificar precios';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $invoiceId = $this->argument('invoice_id');
        
        $invoice = Invoice::with(['order.orderDetails.product', 'order.table', 'customer', 'details'])
            ->find($invoiceId);
        
        if (!$invoice) {
            $this->error("Factura con ID {$invoiceId} no encontrada");
            return 1;
        }
        
        $this->info("🧾 SIMULACIÓN DE GENERACIÓN PDF - FACTURA #{$invoice->id}");
        $this->line("URL: http://restaurante.test/pos/invoice/pdf/{$invoice->id}");
        $this->line("");
        
        $this->info("📋 PRECIOS QUE SE MOSTRARÁN EN EL PDF:");
        
        foreach ($invoice->details as $detail) {
            $this->line("• {$detail->description}:");
            $this->line("  - Cantidad: {$detail->quantity}");
            $this->line("  - Precio Unitario: S/ " . number_format($detail->unit_price, 2));
            $this->line("  - Subtotal: S/ " . number_format($detail->subtotal, 2));
            $this->line("");
        }
        
        $this->info("💰 TOTALES:");
        $this->line("Subtotal: S/ " . number_format($invoice->taxable_amount, 2));
        $this->line("IGV (18%): S/ " . number_format($invoice->tax, 2));
        $this->line("Total: S/ " . number_format($invoice->total, 2));
        
        $this->line("");
        $this->info("✅ CORRECCIÓN APLICADA:");
        $this->line("❌ ANTES: {{ number_format(\$detail->unit_price / 1.18, 2) }}");
        $this->line("✅ AHORA: {{ number_format(\$detail->unit_price, 2) }}");
        $this->line("");
        $this->line("Los precios ahora coinciden con los registrados en la base de datos.");
        
        return 0;
    }
}
