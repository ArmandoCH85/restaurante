<?php

namespace App\Console\Commands;

use App\Services\SunatService;
use App\Models\Invoice;
use Illuminate\Console\Command;

class TestSunatService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sunat:test {invoice_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar el servicio de facturación electrónica SUNAT';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $invoiceId = $this->argument('invoice_id');

        if (!$invoiceId) {
            // Mostrar SOLO Boletas y Facturas disponibles (NO Notas de Venta)
            $invoices = Invoice::whereIn('invoice_type', ['invoice', 'receipt']) // SOLO estos tipos
                ->where(function($query) {
                    $query->where('sunat_status', 'PENDIENTE')
                          ->orWhereNull('sunat_status');
                })
                ->with('customer')
                ->take(10)
                ->get();

            if ($invoices->isEmpty()) {
                $this->error('No hay Boletas o Facturas disponibles para enviar a SUNAT');
                return Command::FAILURE;
            }

            $this->info('Comprobantes disponibles para SUNAT (solo Boletas y Facturas):');
            $this->table(
                ['ID', 'Tipo', 'Serie-Número', 'Cliente', 'Total', 'Estado SUNAT'],
                $invoices->map(function ($invoice) {
                    $tipo = $invoice->invoice_type === 'invoice' ? 'Factura' : 'Boleta';
                    return [
                        $invoice->id,
                        $tipo,
                        $invoice->series . '-' . $invoice->number,
                        $invoice->customer->name ?? 'Sin cliente',
                        'S/ ' . number_format($invoice->total, 2),
                        $invoice->sunat_status ?? 'PENDIENTE'
                    ];
                })
            );

            $invoiceId = $this->ask('Ingrese el ID del comprobante a procesar');
        }

        $invoice = Invoice::find($invoiceId);
        if (!$invoice) {
            $this->error("Factura con ID {$invoiceId} no encontrada");
            return Command::FAILURE;
        }

        // Validar que sea Boleta o Factura (NO Nota de Venta)
        if (!in_array($invoice->invoice_type, ['invoice', 'receipt'])) {
            $tipoActual = match($invoice->invoice_type) {
                'sales_note' => 'Nota de Venta',
                default => $invoice->invoice_type
            };
            $this->error("❌ Solo se pueden enviar Boletas y Facturas a SUNAT.");
            $this->error("   Tipo actual: {$tipoActual}");
            $this->info("💡 Las Notas de Venta son documentos internos y no se envían a SUNAT.");
            return Command::FAILURE;
        }

        $this->info("Procesando factura: {$invoice->series}-{$invoice->number}");
        $this->info("Cliente: " . ($invoice->customer->name ?? 'Sin cliente'));
        $this->info("Total: S/ " . number_format($invoice->total, 2));

        if (!$this->confirm('¿Desea continuar con el envío a SUNAT?')) {
            $this->info('Operación cancelada');
            return Command::SUCCESS;
        }

        try {
            $this->info('Inicializando servicio SUNAT...');
            $sunatService = new SunatService();

            $this->info('Enviando factura a SUNAT...');
            $result = $sunatService->emitirFactura($invoiceId);

            if ($result['success']) {
                $this->info('✅ Factura enviada exitosamente');
                $this->info('XML: ' . $result['xml_path']);
                $this->info('PDF: ' . $result['pdf_path']);

                if (isset($result['sunat_response'])) {
                    $cdr = $result['sunat_response'];
                    $this->info('Código SUNAT: ' . $cdr->getCode());
                    $this->info('Descripción: ' . $cdr->getDescription());
                }
            } else {
                $this->error('❌ Error al enviar factura');
                $this->error('Mensaje: ' . $result['message']);
            }

        } catch (\Exception $e) {
            $this->error('❌ Error inesperado: ' . $e->getMessage());
            $this->error('Archivo: ' . $e->getFile() . ':' . $e->getLine());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
