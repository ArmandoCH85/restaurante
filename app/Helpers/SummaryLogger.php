<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Helper para logging específico de resúmenes de boletas
 * Proporciona métodos especializados para el seguimiento detallado
 * de todos los procesos relacionados con resúmenes SUNAT
 */
class SummaryLogger
{
    /**
     * Canal de log específico para resúmenes
     */
    private static string $channel = 'daily';
    
    /**
     * Prefijo para todos los logs de resúmenes
     */
    private static string $prefix = '[RESUMEN-BOLETAS]';
    
    /**
     * Log de inicio de proceso de resumen
     */
    public static function logProcessStart(string $fechaReferencia, int $cantidadBoletas, array $context = []): void
    {
        $data = array_merge([
            'fecha_referencia' => $fechaReferencia,
            'cantidad_boletas' => $cantidadBoletas,
            'timestamp' => now()->toISOString(),
            'proceso' => 'INICIO_RESUMEN'
        ], $context);
        
        Log::channel(self::$channel)->info(self::$prefix . ' 🚀 INICIANDO PROCESO DE RESUMEN', $data);
    }
    
    /**
     * Log de generación de correlativo
     */
    public static function logCorrelativoGeneration(string $correlativo, string $fecha, array $context = []): void
    {
        $data = array_merge([
            'correlativo_generado' => $correlativo,
            'fecha_base' => $fecha,
            'timestamp' => now()->toISOString()
        ], $context);
        
        Log::channel(self::$channel)->info(self::$prefix . ' 🔢 CORRELATIVO GENERADO', $data);
    }
    
    /**
     * Log de creación de XML
     */
    public static function logXmlGeneration(string $correlativo, int $xmlSize, array $context = []): void
    {
        $data = array_merge([
            'correlativo' => $correlativo,
            'xml_size_bytes' => $xmlSize,
            'xml_size_kb' => round($xmlSize / 1024, 2),
            'timestamp' => now()->toISOString()
        ], $context);
        
        Log::channel(self::$channel)->info(self::$prefix . ' 📄 XML GENERADO', $data);
    }
    
    /**
     * Log de proceso de firma
     */
    public static function logSigningProcess(string $correlativo, string $filename, array $signResult = []): void
    {
        $data = [
            'correlativo' => $correlativo,
            'filename' => $filename,
            'signing_success' => $signResult['success'] ?? false,
            'signing_message' => $signResult['message'] ?? 'N/A',
            'hash_code' => $signResult['hash_code'] ?? 'N/A',
            'timestamp' => now()->toISOString()
        ];
        
        if ($signResult['success'] ?? false) {
            Log::channel(self::$channel)->info(self::$prefix . ' ✍️ XML FIRMADO EXITOSAMENTE', $data);
        } else {
            Log::channel(self::$channel)->error(self::$prefix . ' ❌ ERROR AL FIRMAR XML', $data);
        }
    }
    
    /**
     * Log de comunicación con SUNAT/QPS
     */
    public static function logSunatCommunication(string $method, string $correlativo, array $request, array $response, float $responseTime): void
    {
        $data = [
            'correlativo' => $correlativo,
            'metodo_envio' => $method,
            'request_size' => strlen(json_encode($request)),
            'response_time_ms' => round($responseTime * 1000, 2),
            'response_success' => $response['success'] ?? false,
            'response_code' => $response['code'] ?? 'N/A',
            'response_message' => $response['message'] ?? 'N/A',
            'ticket' => $response['ticket'] ?? 'N/A',
            'timestamp' => now()->toISOString()
        ];
        
        if ($response['success'] ?? false) {
            Log::channel(self::$channel)->info(self::$prefix . ' 📤 ENVIADO A SUNAT EXITOSAMENTE', $data);
        } else {
            Log::channel(self::$channel)->error(self::$prefix . ' ❌ ERROR AL ENVIAR A SUNAT', $data);
        }
    }
    
    /**
     * Log de consulta de estado
     */
    public static function logStatusCheck(string $ticket, array $statusResult, float $responseTime = 0): void
    {
        $data = [
            'ticket' => $ticket,
            'status_code' => $statusResult['status_code'] ?? 'N/A',
            'status_message' => $statusResult['status_message'] ?? 'N/A',
            'response_time_ms' => round($responseTime * 1000, 2),
            'timestamp' => now()->toISOString()
        ];
        
        $statusCode = $statusResult['status_code'] ?? null;
        
        if ($statusCode === '0') {
            Log::channel(self::$channel)->info(self::$prefix . ' ✅ RESUMEN ACEPTADO POR SUNAT', $data);
        } elseif ($statusCode === '98') {
            Log::channel(self::$channel)->info(self::$prefix . ' ⏳ RESUMEN EN PROCESO', $data);
        } elseif ($statusCode === '99') {
            Log::channel(self::$channel)->warning(self::$prefix . ' ⚠️ RESUMEN PROCESADO CON ERRORES', $data);
        } else {
            Log::channel(self::$channel)->error(self::$prefix . ' ❌ ERROR EN CONSULTA DE ESTADO', $data);
        }
    }
    
    /**
     * Log de datos de debug detallados
     */
    public static function logDebugData(string $operation, array $data): void
    {
        $logData = array_merge([
            'operacion' => $operation,
            'timestamp' => now()->toISOString()
        ], $data);
        
        Log::channel(self::$channel)->debug(self::$prefix . ' 🔍 DEBUG: ' . $operation, $logData);
    }
    
    /**
     * Log de errores críticos
     */
    public static function logCriticalError(string $operation, \Throwable $exception, array $context = []): void
    {
        $data = array_merge([
            'operacion' => $operation,
            'error_message' => $exception->getMessage(),
            'error_code' => $exception->getCode(),
            'error_file' => $exception->getFile(),
            'error_line' => $exception->getLine(),
            'stack_trace' => $exception->getTraceAsString(),
            'timestamp' => now()->toISOString()
        ], $context);
        
        Log::channel(self::$channel)->critical(self::$prefix . ' 💥 ERROR CRÍTICO: ' . $operation, $data);
    }
    
    /**
     * Log de validaciones
     */
    public static function logValidation(string $validationType, bool $passed, array $details = []): void
    {
        $data = array_merge([
            'tipo_validacion' => $validationType,
            'validacion_exitosa' => $passed,
            'timestamp' => now()->toISOString()
        ], $details);
        
        if ($passed) {
            Log::channel(self::$channel)->info(self::$prefix . ' ✅ VALIDACIÓN EXITOSA: ' . $validationType, $data);
        } else {
            Log::channel(self::$channel)->warning(self::$prefix . ' ⚠️ VALIDACIÓN FALLIDA: ' . $validationType, $data);
        }
    }
    
    /**
     * Log de archivos guardados
     */
    public static function logFileSaved(string $fileType, string $filename, string $path, int $size): void
    {
        $data = [
            'tipo_archivo' => $fileType,
            'nombre_archivo' => $filename,
            'ruta_completa' => $path,
            'tamaño_bytes' => $size,
            'tamaño_kb' => round($size / 1024, 2),
            'timestamp' => now()->toISOString()
        ];
        
        Log::channel(self::$channel)->info(self::$prefix . ' 💾 ARCHIVO GUARDADO: ' . $fileType, $data);
    }
    
    /**
     * Log de resumen del proceso completo
     */
    public static function logProcessSummary(array $summary): void
    {
        $data = array_merge([
            'timestamp' => now()->toISOString(),
            'proceso_completo' => true
        ], $summary);
        
        if ($summary['success'] ?? false) {
            Log::channel(self::$channel)->info(self::$prefix . ' 🎉 PROCESO COMPLETADO EXITOSAMENTE', $data);
        } else {
            Log::channel(self::$channel)->error(self::$prefix . ' ❌ PROCESO COMPLETADO CON ERRORES', $data);
        }
    }
}