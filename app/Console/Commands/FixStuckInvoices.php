<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Services\QpsService;
use App\Helpers\SunatServiceHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FixStuckInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sunat:fix-stuck-invoices 
                            {--status=ENVIANDO : Estado a corregir (ENVIANDO, ERROR)}
                            {--hours=2 : Horas desde la última actualización}
                            {--method=qps : Método de reenvío (qps, sunat)}
                            {--dry-run : Solo mostrar qué se haría sin ejecutar}
                            {--force : Forzar reenvío sin confirmación}
                            {--invoice-id= : ID específico de factura a corregir}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige facturas/boletas que quedaron en estado ENVIANDO por timeout u otros errores';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 CORRECTOR DE FACTURAS ATASCADAS');
        $this->info('=====================================');
        
        $status = $this->option('status');
        $hours = (int) $this->option('hours');
        $method = $this->option('method');
        $isDryRun = $this->option('dry-run');
        $isForced = $this->option('force');
        $invoiceId = $this->option('invoice-id');
        
        // Validar parámetros
        if (!in_array($status, ['ENVIANDO', 'ERROR'])) {
            $this->error('❌ Estado inválido. Use: ENVIANDO o ERROR');
            return 1;
        }
        
        if (!in_array($method, ['qps', 'sunat'])) {
            $this->error('❌ Método inválido. Use: qps o sunat');
            return 1;
        }
        
        try {
            // Buscar facturas atascadas
            $query = Invoice::whereIn('invoice_type', ['invoice', 'receipt'])
                ->where('sunat_status', $status);
            
            if ($invoiceId) {
                $query->where('id', $invoiceId);
                $this->info("🎯 Procesando factura específica ID: {$invoiceId}");
            } else {
                $cutoffTime = Carbon::now()->subHours($hours);
                $query->where('updated_at', '<=', $cutoffTime);
                $this->info("🕐 Buscando facturas en estado '{$status}' desde hace más de {$hours} horas");
            }
            
            $stuckInvoices = $query->with(['customer', 'order'])->get();
            
            if ($stuckInvoices->isEmpty()) {
                $this->info('✅ No se encontraron facturas atascadas');
                return 0;
            }
            
            $this->info("📋 Encontradas {$stuckInvoices->count()} facturas atascadas:");
            
            // Mostrar tabla de facturas encontradas
            $this->table(
                ['ID', 'Comprobante', 'Tipo', 'Total', 'Estado', 'Última Actualización'],
                $stuckInvoices->map(function ($invoice) {
                    return [
                        $invoice->id,
                        $invoice->series . '-' . $invoice->number,
                        $invoice->invoice_type === 'receipt' ? 'Boleta' : 'Factura',
                        'S/ ' . number_format($invoice->total, 2),
                        $invoice->sunat_status,
                        $invoice->updated_at->format('d/m/Y H:i:s')
                    ];
                })
            );
            
            if ($isDryRun) {
                $this->warn('🔍 MODO DRY-RUN: No se realizarán cambios');
                $this->info('💡 Para ejecutar realmente, quite la opción --dry-run');
                return 0;
            }
            
            // Confirmar acción
            if (!$isForced && !$this->confirm("¿Desea proceder a corregir estas {$stuckInvoices->count()} facturas usando método '{$method}'?")) {
                $this->info('❌ Operación cancelada por el usuario');
                return 0;
            }
            
            // Procesar cada factura
            $this->info("\n🚀 Iniciando corrección usando método: {$method}");
            $this->info('=' . str_repeat('=', 50));
            
            $successful = 0;
            $failed = 0;
            
            foreach ($stuckInvoices as $invoice) {
                $this->info("\n📄 Procesando: {$invoice->series}-{$invoice->number} (ID: {$invoice->id})");
                
                try {
                    DB::beginTransaction();
                    
                    // Resetear estado antes del reenvío
                    $invoice->update([
                        'sunat_status' => 'PENDIENTE',
                        'sunat_code' => null,
                        'sunat_description' => null,
                        'sunat_response' => null
                    ]);
                    
                    $this->line("   🔄 Estado reseteado a PENDIENTE");
                    
                    // Reenviar según el método seleccionado
                    if ($method === 'qps') {
                        $qpsService = new QpsService();
                        $result = $qpsService->sendInvoiceViaQps($invoice);
                    } else {
                        $sunatService = SunatServiceHelper::createIfNotTesting();
                        if ($sunatService === null) {
                            $this->line("   ⚠️  Saltando envío a SUNAT en modo testing");
                            $result = ['success' => true, 'message' => 'Modo testing - SUNAT deshabilitado'];
                        } else {
                            $result = $sunatService->emitirFactura($invoice->id);
                        }
                    }
                    
                    if ($result['success'] ?? false) {
                        $this->line("   ✅ Reenviado exitosamente vía {$method}");
                        $successful++;
                        
                        // Recargar para mostrar nuevo estado
                        $invoice->refresh();
                        $this->line("   📊 Nuevo estado: {$invoice->sunat_status}");
                        
                        if ($invoice->sunat_code) {
                            $this->line("   📋 Código SUNAT: {$invoice->sunat_code}");
                        }
                        
                    } else {
                        $this->line("   ❌ Error en reenvío: " . ($result['message'] ?? 'Error desconocido'));
                        $failed++;
                    }
                    
                    DB::commit();
                    
                } catch (\Exception $e) {
                    DB::rollBack();
                    $this->line("   🚨 Excepción: {$e->getMessage()}");
                    $failed++;
                    
                    // Log del error
                    Log::error('Error al corregir factura atascada', [
                        'invoice_id' => $invoice->id,
                        'series_number' => $invoice->series . '-' . $invoice->number,
                        'method' => $method,
                        'error' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                }
                
                // Pausa pequeña entre envíos
                usleep(500000); // 0.5 segundos
            }
            
            // Resumen final
            $this->info("\n" . str_repeat('=', 50));
            $this->info('📊 RESUMEN DE CORRECCIÓN:');
            $this->info("✅ Exitosas: {$successful}");
            $this->info("❌ Fallidas: {$failed}");
            $this->info("📋 Total procesadas: " . ($successful + $failed));
            
            if ($successful > 0) {
                $this->info("\n💡 Recomendación: Verifique los estados en /admin/invoices");
            }
            
            if ($failed > 0) {
                $this->warn("\n⚠️  Algunas facturas fallaron. Revise los logs para más detalles.");
                return 1;
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("🚨 Error crítico: {$e->getMessage()}");
            Log::error('Error crítico en comando fix-stuck-invoices', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}