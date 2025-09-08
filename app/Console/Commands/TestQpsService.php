<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\QpsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestQpsService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qps:test {invoice_id? : ID de la factura a enviar} {--yes : Confirmar envío sin preguntar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar el servicio QPS para envío a SUNAT';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Iniciando prueba del servicio QPS...');
        
        try {
            $qpsService = new QpsService();
            
            // Mostrar configuración
            $this->info('📋 Configuración QPS:');
            $config = $qpsService->getConfiguration();
            $this->table(
                ['Parámetro', 'Valor'],
                [
                    ['URL Base', $config['base_url']],
                    ['Usuario', $config['username']],
                    ['Servicio Disponible', $config['service_available'] ? '✅ Sí' : '❌ No'],
                    ['Token Actual', $config['has_token'] ? '✅ Válido' : '❌ No disponible'],
                    ['Token Expira', $config['token_expires_at'] ?? 'N/A']
                ]
            );
            
            // Probar obtención de token
            $this->info('\n🔑 Probando obtención de token...');
            $token = $qpsService->getAccessToken();
            $this->info('✅ Token obtenido: ' . substr($token, 0, 20) . '...');
            
            // Si se proporciona ID de factura o número de comprobante, probar envío
            $invoiceId = $this->argument('invoice_id');
            if ($invoiceId) {
                $this->info("\n📄 Probando envío de factura ID: {$invoiceId}");
                
                // Intentar buscar por ID numérico primero
                if (is_numeric($invoiceId)) {
                    $invoice = Invoice::find($invoiceId);
                } else {
                    // Si no es numérico, buscar por número de comprobante (formato: B02-00000004)
                    if (preg_match('/^([A-Z0-9]+)-(.+)$/', $invoiceId, $matches)) {
                        $series = $matches[1];
                        $number = $matches[2];
                        $invoice = Invoice::where('series', $series)->where('number', $number)->first();
                    } else {
                        $invoice = null;
                    }
                }
                
                if (!$invoice) {
                    $this->error("❌ Factura con ID/Número {$invoiceId} no encontrada");
                    return 1;
                }
                
                $this->info("📋 Factura: {$invoice->series}-{$invoice->number} ({$invoice->invoice_type})");
                $this->info("👤 Cliente: {$invoice->customer->name} - {$invoice->customer->document_number}");
                $this->info("💰 Total: S/ {$invoice->total}");
                
                if ($this->option('yes') || $this->confirm('¿Desea proceder con el envío a SUNAT vía QPS?')) {
                    $this->info('🚀 Enviando a SUNAT...');
                    
                    $result = $qpsService->sendInvoiceViaQps($invoice);
                    
                    if ($result['success']) {
                        $this->info('✅ Factura enviada exitosamente');
                        $this->info('📄 XML: ' . ($result['xml_url'] ?? 'N/A'));
                        $this->info('📋 CDR: ' . ($result['cdr_url'] ?? 'N/A'));
                    } else {
                        $this->error('❌ Error al enviar factura: ' . $result['message']);
                        return 1;
                    }
                } else {
                    $this->warn('⏹️ Envío cancelado por el usuario.');
                }
            } else {
                $this->info('\n💡 Para probar el envío de una factura específica, use:');
                $this->info('php artisan qps:test {invoice_id}');
                
                // Mostrar facturas disponibles (solo B y F, NO NV)
                $invoices = Invoice::where(function($query) {
                        $query->whereIn('sunat_status', ['PENDIENTE', 'RECHAZADO'])
                              ->orWhereNull('sunat_status');
                    })
                    ->whereIn('invoice_type', ['receipt', 'invoice']) // Solo Boletas y Facturas
                    ->where('series', 'NOT LIKE', 'NV%') // Excluir series que empiecen con NV
                    ->limit(5)
                    ->get(['id', 'series', 'number', 'invoice_type', 'total', 'sunat_status']);
                    
                if ($invoices->count() > 0) {
                    $this->info('\n📋 Facturas disponibles para envío:');
                    $this->table(
                        ['ID', 'Comprobante', 'Tipo', 'Total', 'Estado SUNAT'],
                        $invoices->map(function ($invoice) {
                            return [
                                $invoice->id,
                                $invoice->series . '-' . $invoice->number,
                                $invoice->invoice_type === 'invoice' ? 'Factura' : 'Boleta',
                                'S/ ' . number_format($invoice->total, 2),
                                $invoice->sunat_status ?? 'PENDIENTE'
                            ];
                        })->toArray()
                    );
                }
            }
            
            $this->info('\n✅ Prueba completada exitosamente');
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Error durante la prueba: ' . $e->getMessage());
            Log::channel('qps')->error('QPS Test Command Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}