<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Models\DocumentSeries;
use Illuminate\Support\Facades\DB;

class FixDocumentSeries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:document-series {--dry-run : Solo mostrar qué cambios se harían sin ejecutarlos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige las series de documentos asignadas incorrectamente';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('🔍 MODO DRY-RUN: Solo mostrando cambios sin ejecutar');
        } else {
            $this->info('🔧 MODO EJECUCIÓN: Aplicando cambios a la base de datos');
        }

        // Obtener series activas configuradas
        $activeSeries = DocumentSeries::where('active', true)->get()->keyBy('document_type');
        
        $this->info("\n📋 Series activas configuradas:");
        foreach ($activeSeries as $type => $series) {
            $this->line("  - {$type}: {$series->series}");
        }

        // Obtener facturas con series incorrectas
        $incorrectInvoices = Invoice::where(function($query) use ($activeSeries) {
            foreach ($activeSeries as $type => $series) {
                $query->orWhere(function($q) use ($type, $series) {
                    $q->where('invoice_type', $type)
                      ->where('series', '!=', $series->series);
                });
            }
        })->orderBy('created_at')->get();

        if ($incorrectInvoices->isEmpty()) {
            $this->info("\n✅ No se encontraron facturas con series incorrectas.");
            return 0;
        }

        $this->info("\n🔍 Facturas encontradas con series incorrectas: " . $incorrectInvoices->count());

        // Agrupar por tipo de documento para mostrar resumen
        $grouped = $incorrectInvoices->groupBy('invoice_type');
        foreach ($grouped as $type => $invoices) {
            $correctSeries = $activeSeries[$type]->series ?? 'NO_CONFIGURADA';
            $this->line("  - {$type}: {$invoices->count()} facturas (serie correcta: {$correctSeries})");
        }

        if (!$isDryRun) {
            if (!$this->confirm("\n¿Deseas continuar con la corrección?")) {
                $this->info("Operación cancelada.");
                return 0;
            }
        }

        $correctedCount = 0;
        $errors = [];

        DB::beginTransaction();
        
        try {
            foreach ($incorrectInvoices as $invoice) {
                $correctSeries = $activeSeries[$invoice->invoice_type] ?? null;
                
                if (!$correctSeries) {
                    $errors[] = "No hay serie activa configurada para tipo: {$invoice->invoice_type}";
                    continue;
                }

                $oldSeries = $invoice->series;
                $newSeries = $correctSeries->series;

                if ($isDryRun) {
                    $this->line("  📝 ID {$invoice->id}: {$invoice->invoice_type} | {$oldSeries} → {$newSeries}");
                } else {
                    // Actualizar la serie de la factura
                    $invoice->update(['series' => $newSeries]);
                    
                    // Obtener el siguiente número para la serie correcta
                    $nextNumber = $correctSeries->getNextNumber();
                    
                    // Actualizar el número de la factura
                    $invoice->update(['number' => str_pad($nextNumber, 8, '0', STR_PAD_LEFT)]);
                    
                    $this->line("  ✅ ID {$invoice->id}: {$invoice->invoice_type} | {$oldSeries} → {$newSeries} | Número: {$invoice->number}");
                    $correctedCount++;
                }
            }

            if (!$isDryRun) {
                DB::commit();
                $this->info("\n🎉 Corrección completada exitosamente!");
                $this->info("📊 Facturas corregidas: {$correctedCount}");
            } else {
                DB::rollBack();
                $this->info("\n📋 Resumen del dry-run:");
                $this->info("📊 Facturas que serían corregidas: " . $incorrectInvoices->count());
            }

            if (!empty($errors)) {
                $this->warn("\n⚠️  Errores encontrados:");
                foreach ($errors as $error) {
                    $this->error("  - {$error}");
                }
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("\n❌ Error durante la corrección: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
