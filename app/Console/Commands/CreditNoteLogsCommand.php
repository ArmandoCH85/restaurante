<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class CreditNoteLogsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'credit-notes:logs 
                            {--type=all : Tipo de log (all, credit_notes, sunat, errors)}
                            {--lines=50 : Número de líneas a mostrar}
                            {--date= : Fecha específica (Y-m-d)}
                            {--follow : Seguir el log en tiempo real}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Visualizar logs específicos de notas de crédito';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');
        $lines = $this->option('lines');
        $date = $this->option('date');
        $follow = $this->option('follow');

        $this->info('=== LOGS DE NOTAS DE CRÉDITO ===');
        $this->newLine();

        if ($follow) {
            $this->followLogs($type);
            return;
        }

        switch ($type) {
            case 'credit_notes':
                $this->showCreditNotesLogs($lines, $date);
                break;
            case 'sunat':
                $this->showSunatLogs($lines, $date);
                break;
            case 'errors':
                $this->showErrorLogs($lines, $date);
                break;
            case 'all':
            default:
                $this->showAllLogs($lines, $date);
                break;
        }
    }

    /**
     * Mostrar logs de notas de crédito
     */
    private function showCreditNotesLogs(int $lines, ?string $date): void
    {
        $this->info('📋 LOGS DE NOTAS DE CRÉDITO:');
        $this->line('─────────────────────────────');
        
        $logFile = $this->getLogFile('credit-notes', $date);
        if ($logFile && File::exists($logFile)) {
            $this->displayLogContent($logFile, $lines);
        } else {
            $this->warn('No se encontraron logs de notas de crédito para la fecha especificada.');
        }
    }

    /**
     * Mostrar logs de SUNAT
     */
    private function showSunatLogs(int $lines, ?string $date): void
    {
        $this->info('🏛️  LOGS DE SUNAT:');
        $this->line('─────────────────');
        
        $logFile = $this->getLogFile('sunat-credit-notes', $date);
        if ($logFile && File::exists($logFile)) {
            $this->displayLogContent($logFile, $lines);
        } else {
            $this->warn('No se encontraron logs de SUNAT para la fecha especificada.');
        }
    }

    /**
     * Mostrar solo errores
     */
    private function showErrorLogs(int $lines, ?string $date): void
    {
        $this->error('❌ LOGS DE ERRORES:');
        $this->line('─────────────────');
        
        $creditNotesLog = $this->getLogFile('credit-notes', $date);
        $sunatLog = $this->getLogFile('sunat-credit-notes', $date);
        
        if ($creditNotesLog && File::exists($creditNotesLog)) {
            $this->info('Errores en notas de crédito:');
            $this->displayLogContent($creditNotesLog, $lines, 'ERROR');
        }
        
        if ($sunatLog && File::exists($sunatLog)) {
            $this->info('Errores en SUNAT:');
            $this->displayLogContent($sunatLog, $lines, 'ERROR');
        }
    }

    /**
     * Mostrar todos los logs
     */
    private function showAllLogs(int $lines, ?string $date): void
    {
        $this->showCreditNotesLogs($lines, $date);
        $this->newLine();
        $this->showSunatLogs($lines, $date);
    }

    /**
     * Seguir logs en tiempo real
     */
    private function followLogs(string $type): void
    {
        $this->info('Siguiendo logs en tiempo real... (Ctrl+C para salir)');
        $this->newLine();
        
        $logFiles = [];
        
        switch ($type) {
            case 'credit_notes':
                $logFiles[] = $this->getLogFile('credit-notes');
                break;
            case 'sunat':
                $logFiles[] = $this->getLogFile('sunat-credit-notes');
                break;
            case 'all':
            default:
                $logFiles[] = $this->getLogFile('credit-notes');
                $logFiles[] = $this->getLogFile('sunat-credit-notes');
                break;
        }
        
        // Implementación básica de seguimiento
        $lastSizes = [];
        foreach ($logFiles as $file) {
            if ($file && File::exists($file)) {
                $lastSizes[$file] = File::size($file);
            }
        }
        
        while (true) {
            foreach ($logFiles as $file) {
                if ($file && File::exists($file)) {
                    $currentSize = File::size($file);
                    if ($currentSize > ($lastSizes[$file] ?? 0)) {
                        $newContent = File::get($file);
                        $lines = explode("\n", $newContent);
                        $newLines = array_slice($lines, -10); // Últimas 10 líneas
                        
                        foreach ($newLines as $line) {
                            if (!empty(trim($line))) {
                                $this->line($line);
                            }
                        }
                        
                        $lastSizes[$file] = $currentSize;
                    }
                }
            }
            
            sleep(1);
        }
    }

    /**
     * Obtener ruta del archivo de log
     */
    private function getLogFile(string $type, ?string $date = null): ?string
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();
        $dateStr = $date->format('Y-m-d');
        
        return storage_path("logs/{$type}-{$dateStr}.log");
    }

    /**
     * Mostrar contenido del log
     */
    private function displayLogContent(string $file, int $lines, ?string $filter = null): void
    {
        if (!File::exists($file)) {
            $this->warn("Archivo no encontrado: {$file}");
            return;
        }
        
        $content = File::get($file);
        $logLines = explode("\n", $content);
        
        if ($filter) {
            $logLines = array_filter($logLines, function($line) use ($filter) {
                return strpos($line, $filter) !== false;
            });
        }
        
        $logLines = array_slice($logLines, -$lines);
        
        foreach ($logLines as $line) {
            if (!empty(trim($line))) {
                // Colorear según el nivel de log
                if (strpos($line, 'ERROR') !== false) {
                    $this->error($line);
                } elseif (strpos($line, 'WARNING') !== false) {
                    $this->warn($line);
                } elseif (strpos($line, 'INFO') !== false) {
                    $this->info($line);
                } else {
                    $this->line($line);
                }
            }
        }
    }
}