<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;

class RecalculateInvoiceTotals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:recalculate-totals 
                            {--dry-run : Solo mostrar qué se haría sin ejecutar cambios}
                            {--invoice-id= : Recalcular solo una factura específica}
                            {--type= : Recalcular solo facturas de tipo específico (invoice, receipt, sales_note)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalcula los totales de las facturas para corregir el cálculo de IGV incluido';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $invoiceId = $this->option('invoice-id');
        $type = $this->option('type');

        $this->info('🧮 Iniciando recálculo de totales de facturas con IGV incluido...');
        
        if ($dryRun) {
            $this->warn('⚠️  MODO DRY-RUN: No se realizarán cambios reales');
        }

        // Construir query
        $query = Invoice::query();

        if ($invoiceId) {
            $query->where('id', $invoiceId);
        }

        if ($type) {
            $query->where('invoice_type', $type);
        }

        $invoices = $query->get();

        if ($invoices->isEmpty()) {
            $this->error('❌ No se encontraron facturas para procesar');
            return 1;
        }

        $this->info("📊 Se procesarán {$invoices->count()} facturas");

        $processed = 0;
        $errors = 0;

        $progressBar = $this->output->createProgressBar($invoices->count());
        $progressBar->start();

        foreach ($invoices as $invoice) {
            try {
                // Calcular valores antes del cambio
                $oldTaxableAmount = $invoice->taxable_amount;
                $oldTax = $invoice->tax;
                $oldTotal = $invoice->total;

                // Calcular nuevos valores usando IGV incluido
                $newSubtotal = $invoice->correct_subtotal;
                $newTax = $invoice->correct_igv;
                $newTotal = $invoice->total; // El total no cambia

                if (!$dryRun) {
                    // Actualizar los valores en la base de datos
                    $invoice->taxable_amount = $newSubtotal;
                    $invoice->tax = $newTax;
                    $invoice->save();
                }

                // Mostrar cambios si hay diferencias significativas
                if (abs($oldTaxableAmount - $newSubtotal) > 0.01 || 
                    abs($oldTax - $newTax) > 0.01) {
                    
                    $this->newLine();
                    $this->info("📝 Factura #{$invoice->id} ({$invoice->invoice_type}):");
                    $this->line("   Subtotal: S/ {$oldTaxableAmount} → S/ {$newSubtotal}");
                    $this->line("   IGV:      S/ {$oldTax} → S/ {$newTax}");
                    $this->line("   Total:    S/ {$oldTotal} (sin cambios)");
                }

                $processed++;
                
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("❌ Error procesando factura #{$invoice->id}: " . $e->getMessage());
                $errors++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Resumen final
        $this->info("✅ Procesamiento completado:");
        $this->line("   📊 Facturas procesadas: {$processed}");
        
        if ($errors > 0) {
            $this->line("   ❌ Errores: {$errors}");
        }

        if ($dryRun) {
            $this->warn("⚠️  Para aplicar los cambios, ejecuta el comando sin --dry-run");
        } else {
            $this->info("💾 Cambios guardados en la base de datos");
        }

        return 0;
    }
}
