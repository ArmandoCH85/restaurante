<?php

namespace App\Filament\Pages;

class ReportViewerPageLog
{
    private static $logFile = null;
    
    public static function getLogFile(): string
    {
        if (self::$logFile === null) {
            // Usar ruta absoluta para evitar dependencias de Laravel
            $baseDir = dirname(__DIR__, 3);
            self::$logFile = $baseDir . '/descargaexcel.log';
        }
        return self::$logFile;
    }
    
    public static function write(string $message): void
    {
        $logFile = self::getLogFile();
        $timestamp = date('Y-m-d H:i:s');
        $formattedMessage = "[{$timestamp}] {$message}\n";
        file_put_contents($logFile, $formattedMessage, FILE_APPEND);
    }
    
    public static function writeRaw(string $message): void
    {
        $logFile = self::getLogFile();
        file_put_contents($logFile, $message, FILE_APPEND);
    }
    
    public static function clear(): void
    {
        $logFile = self::getLogFile();
        file_put_contents($logFile, '');
    }
    
    public static function read(): string
    {
        $logFile = self::getLogFile();
        return file_exists($logFile) ? file_get_contents($logFile) : '';
    }
}