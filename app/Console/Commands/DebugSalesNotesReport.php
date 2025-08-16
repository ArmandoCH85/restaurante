<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Console\Command;

class DebugSalesNotesReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:sales-notes-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Analiza y debug del reporte de Notas de Venta';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== ANÁLISIS DE FACTURAS PARA NOTAS DE VENTA ===');

        // Estadísticas generales
        $totalInvoices = Invoice::count();
        $this->info("Total de facturas en la base de datos: {$totalInvoices}");

        // Análisis por tipo de factura y estado SUNAT
        $this->info("\n=== DESGLOSE POR TIPO Y ESTADO SUNAT ===");
        $breakdown = Invoice::selectRaw('invoice_type, sunat_status, count(*) as count, sum(total) as total')
            ->groupBy('invoice_type', 'sunat_status')
            ->orderBy('invoice_type')
            ->orderBy('sunat_status')
            ->get();

        $table = [];
        foreach ($breakdown as $item) {
            $sunatStatus = $item->sunat_status ?? 'NULL';
            $table[] = [
                'Tipo' => $item->invoice_type,
                'SUNAT Status' => $sunatStatus,
                'Cantidad' => $item->count,
                'Total' => 'S/ ' . number_format($item->total, 2)
            ];
        }
        $this->table(['Tipo', 'SUNAT Status', 'Cantidad', 'Total'], $table);

        $this->info("\n=== IDENTIFICACIÓN DE NOTAS DE VENTA ===");
        
        // Notas de Venta - Forma actual (receipt + sunat_status NULL)
        $currentFormCount = Invoice::where('invoice_type', 'receipt')
            ->whereNull('sunat_status')
            ->count();
        $currentFormTotal = Invoice::where('invoice_type', 'receipt')
            ->whereNull('sunat_status')
            ->sum('total') ?? 0;
        
        $this->info("Notas de Venta (forma actual - receipt + sunat_status NULL): {$currentFormCount} | Total: S/ " . number_format($currentFormTotal, 2));

        // Notas de Venta - Forma legacy (sales_note)
        $legacyFormCount = Invoice::where('invoice_type', 'sales_note')->count();
        $legacyFormTotal = Invoice::where('invoice_type', 'sales_note')->sum('total') ?? 0;
        
        $this->info("Notas de Venta (forma legacy - sales_note): {$legacyFormCount} | Total: S/ " . number_format($legacyFormTotal, 2));

        // Total combinado
        $totalNotasVenta = $currentFormTotal + $legacyFormTotal;
        $this->info("TOTAL NOTAS DE VENTA (combinado): S/ " . number_format($totalNotasVenta, 2));

        $this->info("\n=== BOLETAS ELECTRÓNICAS ===");
        $boletasCount = Invoice::where('invoice_type', 'receipt')
            ->whereNotNull('sunat_status')
            ->where('sunat_status', '!=', 'NO_APLICA')
            ->count();
        $boletasTotal = Invoice::where('invoice_type', 'receipt')
            ->whereNotNull('sunat_status')
            ->where('sunat_status', '!=', 'NO_APLICA')
            ->sum('total') ?? 0;
        
        $this->info("Boletas Electrónicas (receipt + sunat_status válido): {$boletasCount} | Total: S/ " . number_format($boletasTotal, 2));

        $this->info("\n=== FACTURAS ELECTRÓNICAS ===");
        $facturasCount = Invoice::where('invoice_type', 'invoice')->count();
        $facturasTotal = Invoice::where('invoice_type', 'invoice')->sum('total') ?? 0;
        
        $this->info("Facturas Electrónicas (invoice): {$facturasCount} | Total: S/ " . number_format($facturasTotal, 2));

        // Test de la lógica del reporte
        $this->info("\n=== TEST DE LÓGICA DEL REPORTE ===");
        
        // Simular la lógica del ReportViewerPage
        $orders = Order::where('billed', true)
            ->with(['invoices'])
            ->get();

        $salesNotesTotal = $orders->filter(function ($order) {
            // Forma actual: invoice_type='receipt' + sunat_status=null
            $currentForm = $order->invoices->where('invoice_type', 'receipt')
                ->whereNull('sunat_status')->isNotEmpty();
            
            // Forma legacy: invoice_type='sales_note' (cualquier sunat_status)
            $legacyForm = $order->invoices->where('invoice_type', 'sales_note')->isNotEmpty();
            
            return $currentForm || $legacyForm;
        })->sum('total');

        $this->info("Total Notas de Venta según lógica del reporte: S/ " . number_format($salesNotesTotal, 2));

        // Mostrar algunos ejemplos
        $this->info("\n=== EJEMPLOS DE REGISTROS ===");
        $examples = Invoice::select('id', 'invoice_type', 'series', 'number', 'total', 'sunat_status')
            ->limit(10)
            ->get();
        
        $exampleTable = [];
        foreach ($examples as $invoice) {
            $sunatStatus = $invoice->sunat_status ?? 'NULL';
            $exampleTable[] = [
                'ID' => $invoice->id,
                'Tipo' => $invoice->invoice_type,
                'Número' => $invoice->series . '-' . $invoice->number,
                'Total' => 'S/ ' . $invoice->total,
                'SUNAT' => $sunatStatus
            ];
        }
        $this->table(['ID', 'Tipo', 'Número', 'Total', 'SUNAT'], $exampleTable);

        $this->info("\n=== FIN DEL ANÁLISIS ===");
        
        return Command::SUCCESS;
    }
}