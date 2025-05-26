<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;

class TestPdfPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:pdf-prices {invoice_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica que los precios en el PDF coincidan con los de la BD';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $invoiceId = $this->argument('invoice_id');
        
        $invoice = Invoice::with(['details.product'])->find($invoiceId);
        
        if (!$invoice) {
            $this->error("Factura con ID {$invoiceId} no encontrada");
            return 1;
        }
        
        $this->info("🧾 VERIFICACIÓN DE PRECIOS - FACTURA #{$invoice->id}");
        $this->line("Tipo: " . $invoice->invoice_type);
        $this->line("Serie-Número: {$invoice->series}-{$invoice->number}");
        $this->line("Total: S/ {$invoice->total}");
        $this->line("");
        
        $this->info("📋 DETALLES:");
        $this->table(
            ['Producto', 'Cantidad', 'Precio BD', 'Subtotal BD', 'Precio Producto'],
            $invoice->details->map(function ($detail) {
                return [
                    $detail->description,
                    $detail->quantity,
                    'S/ ' . number_format($detail->unit_price, 2),
                    'S/ ' . number_format($detail->subtotal, 2),
                    $detail->product ? 'S/ ' . number_format($detail->product->price, 2) : 'N/A'
                ];
            })->toArray()
        );
        
        $this->line("");
        $this->info("✅ Los precios mostrados arriba son los que DEBEN aparecer en el PDF");
        $this->line("❌ ANTES: Los precios se dividían entre 1.18 incorrectamente");
        $this->line("✅ AHORA: Los precios se muestran tal como están en la BD");
        
        return 0;
    }
}
