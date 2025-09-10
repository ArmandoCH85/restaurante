<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SunatService;
use Illuminate\Support\Facades\Log;
use Exception;

class CheckSummaryStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sunat:check-summary-status 
                            {ticket : Ticket de SUNAT para consultar el estado}
                            {--wait=0 : Segundos a esperar antes de consultar}
                            {--retry=1 : Número de reintentos si está en proceso}
                            {--interval=30 : Intervalo en segundos entre reintentos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Consultar el estado de un resumen de boletas enviado a SUNAT';

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
            $ticket = $this->argument('ticket');
            $waitTime = (int) $this->option('wait');
            $maxRetries = (int) $this->option('retry');
            $retryInterval = (int) $this->option('interval');
            
            $this->info("🔍 Consultando estado del resumen de boletas");
            $this->info("🎫 Ticket: {$ticket}");
            
            // Validar formato del ticket
            if (!$this->isValidTicket($ticket)) {
                $this->error("❌ Formato de ticket inválido");
                $this->info("💡 El ticket debe tener el formato: YYYYMMDDHHMMSSNNNNNN");
                return Command::FAILURE;
            }
            
            // Esperar si se especificó
            if ($waitTime > 0) {
                $this->info("⏳ Esperando {$waitTime} segundos antes de consultar...");
                sleep($waitTime);
            }
            
            $attempt = 1;
            $maxAttempts = $maxRetries + 1;
            
            while ($attempt <= $maxAttempts) {
                if ($attempt > 1) {
                    $this->info("🔄 Intento {$attempt} de {$maxAttempts}");
                }
                
                $resultado = $this->sunatService->consultarEstadoResumen($ticket);
                
                if ($resultado['success']) {
                    $codigo = $resultado['codigo'];
                    $descripcion = $resultado['descripcion'];
                    $estado = $resultado['estado'];
                    
                    $this->displayStatus($codigo, $descripcion, $estado, $ticket);
                    
                    // Si está procesado (éxito o error), terminar
                    if (in_array($codigo, ['0', '99'])) {
                        if (isset($resultado['cdr_content'])) {
                            $this->info("📄 CDR recibido y guardado automáticamente");
                        }
                        
                        // Registrar en logs
                        Log::info('Estado de resumen consultado via comando', [
                            'ticket' => $ticket,
                            'codigo' => $codigo,
                            'estado' => $estado,
                            'descripcion' => $descripcion
                        ]);
                        
                        return Command::SUCCESS;
                    }
                    
                    // Si está en proceso y hay más intentos
                    if ($codigo === '98' && $attempt < $maxAttempts) {
                        $this->info("⏳ Esperando {$retryInterval} segundos antes del siguiente intento...");
                        sleep($retryInterval);
                        $attempt++;
                        continue;
                    }
                    
                    // Si está en proceso pero no hay más intentos
                    if ($codigo === '98') {
                        $this->warn("⚠️ El resumen sigue en proceso después de {$maxAttempts} intentos");
                        $this->info("💡 Puedes consultar nuevamente más tarde con el mismo comando");
                        return Command::SUCCESS;
                    }
                    
                } else {
                    $this->error("❌ Error al consultar estado: {$resultado['message']}");
                    
                    // Si hay más intentos, continuar
                    if ($attempt < $maxAttempts) {
                        $this->info("🔄 Reintentando en {$retryInterval} segundos...");
                        sleep($retryInterval);
                        $attempt++;
                        continue;
                    }
                    
                    Log::error('Error al consultar estado de resumen via comando', [
                        'ticket' => $ticket,
                        'message' => $resultado['message']
                    ]);
                    
                    return Command::FAILURE;
                }
                
                $attempt++;
            }
            
            return Command::SUCCESS;
            
        } catch (Exception $e) {
            $this->error("❌ Error crítico: {$e->getMessage()}");
            $this->error("📁 Archivo: {$e->getFile()}");
            $this->error("📍 Línea: {$e->getLine()}");
            
            Log::error('Error crítico en comando de consulta de estado', [
                'ticket' => $this->argument('ticket'),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return Command::FAILURE;
        }
    }
    
    /**
     * Mostrar el estado del resumen
     */
    private function displayStatus($codigo, $descripcion, $estado, $ticket)
    {
        $this->newLine();
        $this->info("📊 RESULTADO DE LA CONSULTA");
        $this->info("═══════════════════════════");
        
        switch ($codigo) {
            case '0':
                $this->info("✅ ESTADO: ACEPTADO");
                $this->info("🎉 {$descripcion}");
                break;
                
            case '98':
                $this->warn("⏳ ESTADO: EN PROCESO");
                $this->warn("🔄 {$descripcion}");
                $this->info("💡 SUNAT está procesando el resumen. Consulta nuevamente en unos minutos.");
                break;
                
            case '99':
                $this->error("❌ ESTADO: PROCESADO CON ERRORES");
                $this->error("🚫 {$descripcion}");
                $this->info("💡 Revisa los datos del resumen y vuelve a enviarlo si es necesario.");
                break;
                
            default:
                $this->warn("⚠️ ESTADO: {$estado}");
                $this->warn("📝 {$descripcion}");
                $this->info("🔢 Código: {$codigo}");
                break;
        }
        
        $this->info("🎫 Ticket: {$ticket}");
        $this->info("🕐 Consultado: " . now()->format('Y-m-d H:i:s'));
        $this->newLine();
        
        // Mostrar información adicional según el estado
        if ($codigo === '0') {
            $this->info("🎯 PRÓXIMOS PASOS:");
            $this->info("   • El resumen ha sido aceptado por SUNAT");
            $this->info("   • Las boletas están oficialmente reportadas");
            $this->info("   • El CDR ha sido guardado automáticamente");
        } elseif ($codigo === '98') {
            $this->info("🎯 PRÓXIMOS PASOS:");
            $this->info("   • Espera unos minutos y consulta nuevamente");
            $this->info("   • Comando: php artisan sunat:check-summary-status {$ticket}");
            $this->info("   • O usa --retry para consultas automáticas");
        } elseif ($codigo === '99') {
            $this->info("🎯 PRÓXIMOS PASOS:");
            $this->info("   • Revisa los logs del sistema para más detalles");
            $this->info("   • Verifica los datos de las boletas");
            $this->info("   • Considera reenviar el resumen si es necesario");
        }
    }
    
    /**
     * Validar formato del ticket
     */
    private function isValidTicket($ticket)
    {
        // El ticket debe tener 20 dígitos: YYYYMMDDHHMMSSNNNNNN
        return preg_match('/^\d{20}$/', $ticket);
    }
}