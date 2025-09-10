<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SunatService;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class SendDailySummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sunat:send-daily-summary 
                            {--date= : Fecha de referencia (YYYY-MM-DD). Por defecto: ayer}
                            {--force : Forzar envío aunque ya exista un resumen para la fecha}
                            {--dry-run : Simular el proceso sin enviar a SUNAT}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enviar resumen diario de boletas a SUNAT';

    protected $sunatService;

    /**
     * Create a new command instance.
     */
    public function __construct(SunatService $sunatService)
    {
        parent::__construct();
        $this->sunatService = $sunatService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Obtener fecha de referencia
            $fechaReferencia = $this->option('date') 
                ? Carbon::parse($this->option('date'))->format('Y-m-d')
                : Carbon::yesterday()->format('Y-m-d');
            
            $fechaGeneracion = Carbon::now()->format('Y-m-d');
            $isDryRun = $this->option('dry-run');
            $isForce = $this->option('force');
            
            $this->info("📋 Iniciando proceso de resumen diario de boletas");
            $this->info("Fecha de referencia: {$fechaReferencia}");
            $this->info("Fecha de generación: {$fechaGeneracion}");
            
            if ($isDryRun) {
                $this->warn("🧪 MODO SIMULACIÓN - No se enviará a SUNAT");
            }
            
            // Validar que la fecha de referencia no sea hoy
            if ($fechaReferencia >= Carbon::now()->format('Y-m-d')) {
                $this->error("❌ La fecha de referencia debe ser anterior a hoy");
                return Command::FAILURE;
            }
            
            // Validar horario de envío (hasta las 12:00 PM)
            $horaActual = Carbon::now()->format('H:i');
            if ($horaActual > '12:00' && !$isForce) {
                $this->error("❌ Los resúmenes solo se pueden enviar hasta las 12:00 PM");
                $this->info("💡 Usa --force para enviar fuera del horario permitido");
                return Command::FAILURE;
            }
            
            // Obtener boletas del día de referencia
            $this->info("🔍 Buscando boletas para la fecha {$fechaReferencia}...");
            
            $boletasQuery = Invoice::where('invoice_type', 'receipt')
                ->whereDate('issue_date', $fechaReferencia)
                ->whereIn('sunat_status', ['ACEPTADO', 'PENDIENTE'])
                ->with(['customer'])
                ->get();
            
            if ($boletasQuery->isEmpty()) {
                $this->warn("⚠️ No se encontraron boletas (aceptadas o pendientes) para la fecha {$fechaReferencia}");
                return Command::SUCCESS;
            }
            
            $this->info("✅ Encontradas {$boletasQuery->count()} boletas");
            
            // Mostrar resumen de boletas
            $totalMonto = $boletasQuery->sum('total');
            $this->table(
                ['Serie-Número', 'Cliente', 'Total'],
                $boletasQuery->take(10)->map(function ($boleta) {
                    return [
                        $boleta->series . '-' . $boleta->number,
                        $boleta->customer->name ?? 'Cliente genérico',
                        'S/ ' . number_format($boleta->total, 2)
                    ];
                })->toArray()
            );
            
            if ($boletasQuery->count() > 10) {
                $this->info("... y " . ($boletasQuery->count() - 10) . " boletas más");
            }
            
            $this->info("💰 Total general: S/ " . number_format($totalMonto, 2));
            
            // Confirmar envío
            if (!$isDryRun && !$this->confirm('¿Deseas continuar con el envío del resumen?')) {
                $this->info("❌ Proceso cancelado por el usuario");
                return Command::SUCCESS;
            }
            
            if ($isDryRun) {
                $this->info("🧪 SIMULACIÓN: El resumen se habría enviado con éxito");
                return Command::SUCCESS;
            }
            
            // Convertir a array con la estructura requerida
            $boletas = [];
            foreach ($boletasQuery as $boleta) {
                $boletas[] = [
                    'series' => $boleta->series,
                    'number' => $boleta->number,
                    'invoice_type' => $boleta->invoice_type,
                    'total' => $boleta->total,
                    'subtotal' => $boleta->subtotal,
                    'igv' => $boleta->igv,
                    'customer_document_type' => $boleta->customer->document_type ?? 'DNI',
                    'customer_document_number' => $boleta->customer->document_number ?? '00000000',
                    'estado' => '1' // 1 = Adicionar
                ];
            }
            
            // Enviar resumen a SUNAT
            $this->info("🚀 Enviando resumen a SUNAT...");
            
            $startTime = microtime(true);
            $resultado = $this->sunatService->enviarResumenBoletas($boletas, $fechaGeneracion, $fechaReferencia);
            $endTime = microtime(true);
            
            $processingTime = round(($endTime - $startTime) * 1000, 2);
            
            if ($resultado['success']) {
                $this->info("✅ Resumen enviado exitosamente");
                $this->info("🎫 Ticket: {$resultado['ticket']}");
                $this->info("📊 Correlativo: {$resultado['correlativo']}");
                $this->info("📁 XML guardado en: {$resultado['xml_path']}");
                $this->info("⏱️ Tiempo de procesamiento: {$processingTime} ms");
                
                // Registrar en logs
                Log::info('Resumen diario enviado via comando', [
                    'fecha_referencia' => $fechaReferencia,
                    'fecha_generacion' => $fechaGeneracion,
                    'ticket' => $resultado['ticket'],
                    'correlativo' => $resultado['correlativo'],
                    'cantidad_boletas' => count($boletas),
                    'total_monto' => $totalMonto,
                    'processing_time_ms' => $processingTime
                ]);
                
                // Preguntar si desea consultar el estado
                if ($this->confirm('¿Deseas consultar el estado del resumen ahora?')) {
                    $this->consultarEstado($resultado['ticket']);
                } else {
                    $this->info("💡 Puedes consultar el estado más tarde con:");
                    $this->info("   php artisan sunat:check-summary-status {$resultado['ticket']}");
                }
                
                return Command::SUCCESS;
                
            } else {
                $this->error("❌ Error al enviar resumen: {$resultado['message']}");
                
                if (isset($resultado['error_code'])) {
                    $this->error("🔢 Código de error: {$resultado['error_code']}");
                }
                
                // Registrar error en logs
                Log::error('Error al enviar resumen diario via comando', [
                    'fecha_referencia' => $fechaReferencia,
                    'message' => $resultado['message'],
                    'error_code' => $resultado['error_code'] ?? null
                ]);
                
                return Command::FAILURE;
            }
            
        } catch (Exception $e) {
            $this->error("❌ Error crítico: {$e->getMessage()}");
            $this->error("📁 Archivo: {$e->getFile()}");
            $this->error("📍 Línea: {$e->getLine()}");
            
            Log::error('Error crítico en comando de resumen diario', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return Command::FAILURE;
        }
    }
    
    /**
     * Consultar estado del resumen
     */
    private function consultarEstado($ticket)
    {
        try {
            $this->info("🔍 Consultando estado del resumen...");
            
            // Esperar un momento antes de consultar
            $this->info("⏳ Esperando 3 segundos...");
            sleep(3);
            
            $resultado = $this->sunatService->consultarEstadoResumen($ticket);
            
            if ($resultado['success']) {
                $codigo = $resultado['codigo'];
                $descripcion = $resultado['descripcion'];
                $estado = $resultado['estado'];
                
                switch ($codigo) {
                    case '0':
                        $this->info("✅ {$descripcion} (Código: {$codigo})");
                        break;
                    case '98':
                        $this->warn("⏳ {$descripcion} (Código: {$codigo})");
                        $this->info("💡 El resumen está siendo procesado. Consulta nuevamente en unos minutos.");
                        break;
                    case '99':
                        $this->error("❌ {$descripcion} (Código: {$codigo})");
                        break;
                    default:
                        $this->warn("⚠️ {$descripcion} (Código: {$codigo})");
                        break;
                }
                
                $this->info("📊 Estado interpretado: {$estado}");
                
                if (isset($resultado['cdr_content'])) {
                    $this->info("📄 CDR recibido y disponible");
                }
                
            } else {
                $this->error("❌ Error al consultar estado: {$resultado['message']}");
            }
            
        } catch (Exception $e) {
            $this->error("❌ Error al consultar estado: {$e->getMessage()}");
        }
    }
}