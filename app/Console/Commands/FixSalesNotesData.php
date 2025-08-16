<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixSalesNotesData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:sales-notes-data {--dry-run : Solo mostrar qué cambios se harían sin aplicarlos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige la inconsistencia en los datos de Notas de Venta';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('=== MODO DRY-RUN: Solo mostrando cambios que se harían ===');
        } else {
            $this->info('=== APLICANDO CORRECCIONES A LOS DATOS ===');
        }

        // 1. Buscar facturas con invoice_type='sales_note'
        $salesNoteInvoices = Invoice::where('invoice_type', 'sales_note')->get();
        
        if ($salesNoteInvoices->count() > 0) {
            $this->info("\n1. Convirtiendo facturas sales_note a receipt + sunat_status=null:");
            
            $table = [];
            foreach ($salesNoteInvoices as $invoice) {
                $table[] = [
                    'ID' => $invoice->id,
                    'Serie-Número' => $invoice->series . '-' . $invoice->number,
                    'Total' => 'S/ ' . $invoice->total,
                    'Estado Actual' => $invoice->invoice_type . ' | ' . ($invoice->sunat_status ?? 'NULL'),
                    'Nuevo Estado' => 'receipt | NULL'
                ];
            }
            $this->table(['ID', 'Serie-Número', 'Total', 'Estado Actual', 'Nuevo Estado'], $table);
            
            if (!$dryRun) {
                $updated = DB::table('invoices')
                    ->where('invoice_type', 'sales_note')
                    ->update([
                        'invoice_type' => 'receipt',
                        'sunat_status' => null
                    ]);
                $this->info("✅ {$updated} facturas actualizadas.");
            }
        } else {
            $this->info("✅ No se encontraron facturas con invoice_type='sales_note'");
        }

        // 2. Buscar facturas receipt con series NV que tengan sunat_status diferente de null
        $nvInvoicesWithStatus = Invoice::where('invoice_type', 'receipt')
            ->where('series', 'like', 'NV%')
            ->whereNotNull('sunat_status')
            ->get();
            
        if ($nvInvoicesWithStatus->count() > 0) {
            $this->info("\n2. Corrigiendo facturas con series NV que tienen sunat_status incorrecto:");
            
            $table = [];
            foreach ($nvInvoicesWithStatus as $invoice) {
                $table[] = [
                    'ID' => $invoice->id,
                    'Serie-Número' => $invoice->series . '-' . $invoice->number,
                    'Total' => 'S/ ' . $invoice->total,
                    'SUNAT Actual' => $invoice->sunat_status,
                    'Nuevo SUNAT' => 'NULL'
                ];
            }
            $this->table(['ID', 'Serie-Número', 'Total', 'SUNAT Actual', 'Nuevo SUNAT'], $table);
            
            if (!$dryRun) {
                $updated = DB::table('invoices')
                    ->where('invoice_type', 'receipt')
                    ->where('series', 'like', 'NV%')
                    ->whereNotNull('sunat_status')
                    ->update(['sunat_status' => null]);
                $this->info("✅ {$updated} facturas NV actualizadas.");
            }
        } else {
            $this->info("✅ No se encontraron facturas NV con sunat_status incorrecto");
        }

        // 3. Asegurar que boletas no-NV tengan sunat_status válido
        $boletasSinStatus = Invoice::where('invoice_type', 'receipt')
            ->where('series', 'not like', 'NV%')
            ->whereNull('sunat_status')
            ->get();
            
        if ($boletasSinStatus->count() > 0) {
            $this->info("\n3. Asignando sunat_status='PENDIENTE' a boletas sin estado:");
            
            $table = [];
            foreach ($boletasSinStatus as $invoice) {
                $table[] = [
                    'ID' => $invoice->id,
                    'Serie-Número' => $invoice->series . '-' . $invoice->number,
                    'Total' => 'S/ ' . $invoice->total,
                    'SUNAT Actual' => 'NULL',
                    'Nuevo SUNAT' => 'PENDIENTE'
                ];
            }
            $this->table(['ID', 'Serie-Número', 'Total', 'SUNAT Actual', 'Nuevo SUNAT'], $table);
            
            if (!$dryRun) {
                $updated = DB::table('invoices')
                    ->where('invoice_type', 'receipt')
                    ->where('series', 'not like', 'NV%')
                    ->whereNull('sunat_status')
                    ->update(['sunat_status' => 'PENDIENTE']);
                $this->info("✅ {$updated} boletas actualizadas.");
            }
        } else {
            $this->info("✅ Todas las boletas tienen sunat_status correcto");
        }

        // 4. Asegurar que facturas tengan sunat_status válido
        $facturasSinStatus = Invoice::where('invoice_type', 'invoice')
            ->whereNull('sunat_status')
            ->get();
            
        if ($facturasSinStatus->count() > 0) {
            $this->info("\n4. Asignando sunat_status='PENDIENTE' a facturas sin estado:");
            
            $table = [];
            foreach ($facturasSinStatus as $invoice) {
                $table[] = [
                    'ID' => $invoice->id,
                    'Serie-Número' => $invoice->series . '-' . $invoice->number,
                    'Total' => 'S/ ' . $invoice->total,
                    'SUNAT Actual' => 'NULL',
                    'Nuevo SUNAT' => 'PENDIENTE'
                ];
            }
            $this->table(['ID', 'Serie-Número', 'Total', 'SUNAT Actual', 'Nuevo SUNAT'], $table);
            
            if (!$dryRun) {
                $updated = DB::table('invoices')
                    ->where('invoice_type', 'invoice')
                    ->whereNull('sunat_status')
                    ->update(['sunat_status' => 'PENDIENTE']);
                $this->info("✅ {$updated} facturas actualizadas.");
            }
        } else {
            $this->info("✅ Todas las facturas tienen sunat_status correcto");
        }

        if ($dryRun) {
            $this->info("\n=== FIN DEL DRY-RUN ===");
            $this->info("Para aplicar los cambios, ejecuta: php artisan fix:sales-notes-data");
        } else {
            $this->info("\n=== CORRECCIONES APLICADAS EXITOSAMENTE ===");
            $this->info("Puedes verificar los resultados con: php artisan debug:sales-notes-report");
        }
        
        return Command::SUCCESS;
    }
}