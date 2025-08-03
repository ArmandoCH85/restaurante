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
                    // Encontrar el siguiente número disponible para la serie correcta
                    $nextNumber = $this->findNextAvailableNumber($newSeries);
                    $formattedNumber = str_pad($nextNumber, 8, '0', STR_PAD_LEFT);
                    
                    // Actualizar la serie y número de la factura
                    $invoice->update([
                        'series' => $newSeries,
                        'number' => $formattedNumber
                    ]);
                    
                    $this->line("  ✅ ID {$invoice->id}: {$invoice->invoice_type} | {$oldSeries} → {$newSeries} | Número: {$formattedNumber}");
                    $correctedCount++;
                }
            }

            if (!$isDryRun) {
                // Actualizar los contadores de las series después de todas las correcciones
                $this->updateSeriesCounters($activeSeries);
                
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

    /**
     * Encuentra el siguiente número disponible para una serie
     */
    private function findNextAvailableNumber($series)
    {
        // Obtener el número más alto existente para esta serie
        $lastNumber = Invoice::where('series', $series)
            ->orderBy('number', 'desc')
            ->value('number');

        if (!$lastNumber) {
            return 1; // Si no hay facturas, empezar desde 1
        }

        // Convertir a entero y sumar 1
        $nextNumber = intval($lastNumber) + 1;

        // Verificar que no exista ya una factura con este número
        while (Invoice::where('series', $series)
                     ->where('number', str_pad($nextNumber, 8, '0', STR_PAD_LEFT))
                     ->exists()) {
            $nextNumber++;
        }

        return $nextNumber;
    }

    /**
     * Actualiza los contadores de las series después de las correcciones
     */
    private function updateSeriesCounters($activeSeries)
    {
        $this->info("\n🔄 Actualizando contadores de series...");

        foreach ($activeSeries as $type => $series) {
            // Obtener el número más alto para esta serie
            $lastNumber = Invoice::where('series', $series->series)
                ->orderBy('number', 'desc')
                ->value('number');

            if ($lastNumber) {
                $nextNumber = intval($lastNumber) + 1;
                $series->update(['next_number' => $nextNumber]);
                $this->line("  ✅ Serie {$series->series}: próximo número = {$nextNumber}");
            }
        }
    }
}
