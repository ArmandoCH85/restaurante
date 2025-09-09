<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use App\Models\Invoice;
use App\Models\CreditNote;

class CreditNoteLogger
{
    /**
     * Log de inicio de creación de nota de crédito
     */
    public static function logCreationStart(Invoice $invoice, string $motivo, string $descripcion): void
    {
        Log::channel('envionotacredito')->info('=== INICIANDO CREACIÓN DE NOTA DE CRÉDITO ===', [
            'invoice_id' => $invoice->id,
            'invoice_series' => $invoice->series,
            'invoice_number' => $invoice->number,
            'customer_id' => $invoice->customer_id,
            'customer_name' => $invoice->customer->business_name ?? 'N/A',
            'motivo_codigo' => $motivo,
            'motivo_descripcion' => $descripcion,
            'invoice_total' => $invoice->total,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Log de creación exitosa de nota de crédito
     */
    public static function logCreationSuccess(CreditNote $creditNote): void
    {
        Log::channel('envionotacredito')->info('=== NOTA DE CRÉDITO CREADA EXITOSAMENTE ===', [
            'credit_note_id' => $creditNote->id,
            'serie' => $creditNote->serie,
            'numero' => $creditNote->numero,
            'invoice_id' => $creditNote->invoice_id,
            'motivo_codigo' => $creditNote->motivo_codigo,
            'total' => $creditNote->total,
            'sunat_status' => $creditNote->sunat_status,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Log de error en creación de nota de crédito
     */
    public static function logCreationError(Invoice $invoice, \Exception $exception, array $context = []): void
    {
        Log::channel('envionotacredito')->error('=== ERROR AL CREAR NOTA DE CRÉDITO ===', [
            'invoice_id' => $invoice->id,
            'invoice_series' => $invoice->series,
            'invoice_number' => $invoice->number,
            'error_message' => $exception->getMessage(),
            'error_code' => $exception->getCode(),
            'error_file' => $exception->getFile(),
            'error_line' => $exception->getLine(),
            'stack_trace' => $exception->getTraceAsString(),
            'context' => $context,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Log de envío a SUNAT via QPSE
     */
    public static function logSunatSend(array $creditNoteData, string $xml): void
    {
        Log::channel('envionotacredito')->info('=== ENVIANDO NOTA DE CRÉDITO A SUNAT VIA QPSE ===', [
            'serie' => $creditNoteData['serie'],
            'correlativo' => $creditNoteData['correlativo'],
            'tipo_documento' => $creditNoteData['tipoDoc'],
            'documento_afectado' => $creditNoteData['numDocfectado'],
            'motivo_codigo' => $creditNoteData['codMotivo'],
            'motivo_descripcion' => $creditNoteData['desMotivo'],
            'total' => $creditNoteData['mtoImpVenta'],
            'subtotal' => $creditNoteData['mtoOperGravadas'],
            'igv' => $creditNoteData['mtoIGV'],
            'xml_size' => strlen($xml) . ' bytes',
            'company_ruc' => $creditNoteData['company']['ruc'] ?? 'N/A',
            'client_doc' => $creditNoteData['client']['numDoc'] ?? 'N/A',
            'client_name' => $creditNoteData['client']['rznSocial'] ?? 'N/A',
            'qpse_endpoint' => 'billService (QPSE)',
            'environment' => config('app.env'),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Log de respuesta de SUNAT via QPSE
     */
    public static function logSunatResponse(array $creditNoteData, array $response): void
    {
        if ($response['success']) {
            Log::channel('envionotacredito')->info('=== RESPUESTA EXITOSA DE SUNAT QPSE ===', [
                'serie' => $creditNoteData['serie'],
                'correlativo' => $creditNoteData['correlativo'],
                'sunat_code' => $response['sunat_code'] ?? 'N/A',
                'sunat_description' => $response['sunat_description'] ?? 'N/A',
                'cdr_received' => !empty($response['cdr']),
                'cdr_size' => !empty($response['cdr']) ? strlen($response['cdr']) . ' bytes' : '0 bytes',
                'xml_included' => !empty($response['xml']),
                'xml_size' => !empty($response['xml']) ? strlen($response['xml']) . ' bytes' : '0 bytes',
                'qpse_status' => 'ACCEPTED',
                'timestamp' => now()->toISOString(),
            ]);
        } else {
            Log::channel('envionotacredito')->error('=== ERROR EN RESPUESTA DE SUNAT QPSE ===', [
                'serie' => $creditNoteData['serie'],
                'correlativo' => $creditNoteData['correlativo'],
                'error' => $response['error'] ?? 'Error desconocido',
                'qpse_status' => 'REJECTED',
                'timestamp' => now()->toISOString(),
            ]);
        }
    }

    /**
     * Log de error en SUNAT QPSE
     */
    public static function logSunatError(array $creditNoteData, \Exception $exception): void
    {
        Log::channel('envionotacredito')->error('=== ERROR EN COMUNICACIÓN CON SUNAT QPSE ===', [
            'serie' => $creditNoteData['serie'] ?? 'N/A',
            'correlativo' => $creditNoteData['correlativo'] ?? 'N/A',
            'error_message' => $exception->getMessage(),
            'error_code' => $exception->getCode(),
            'error_file' => $exception->getFile(),
            'error_line' => $exception->getLine(),
            'stack_trace' => $exception->getTraceAsString(),
            'qpse_endpoint' => 'billService',
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Log de configuración QPSE antes del envío
     */
    public static function logQpseConfiguration(): void
    {
        $config = [
            'environment' => config('app.env'),
            'sunat_endpoint' => config('app.env') === 'production' 
                ? 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService'
                : 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService',
            'certificate_configured' => !empty(\App\Models\AppSetting::getSetting('FacturacionElectronica', 'certificate_path')),
            'sol_user_configured' => !empty(\App\Models\AppSetting::getSetting('FacturacionElectronica', 'sol_user')),
            'ruc_configured' => !empty(config('company.ruc')),
        ];

        Log::channel('envionotacredito')->info('=== CONFIGURACIÓN QPSE PARA NOTAS DE CRÉDITO ===', $config);
    }

    /**
     * Log de validación de XML antes del envío
     */
    public static function logXmlValidation(string $xml, bool $isValid, array $errors = []): void
    {
        Log::channel('envionotacredito')->info('=== VALIDACIÓN DE XML ===', [
            'xml_size' => strlen($xml) . ' bytes',
            'is_valid' => $isValid,
            'validation_errors' => $errors,
            'xml_preview' => substr($xml, 0, 500) . '...',
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Log de comunicación detallada con SUNAT
     */
    public static function logSunatCommunication(string $endpoint, array $headers, float $responseTime): void
    {
        Log::channel('envionotacredito')->info('=== COMUNICACIÓN DETALLADA CON SUNAT ===', [
            'endpoint' => $endpoint,
            'headers' => $headers,
            'response_time' => $responseTime . ' seconds',
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Log de debug para datos de estructura
     */
    public static function logDebugData(string $step, array $data): void
    {
        Log::channel('envionotacredito')->debug("STEP: {$step}", [
            'step' => $step,
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Log de guardado de archivos XML/CDR
     */
    public static function logFileSave(string $type, string $path, bool $success): void
    {
        $level = $success ? 'info' : 'error';
        $message = $success ? "Archivo {$type} guardado exitosamente" : "Error al guardar archivo {$type}";
        
        Log::channel('envionotacredito')->{$level}($message, [
            'file_type' => $type,
            'file_path' => $path,
            'success' => $success,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Log de actualización de estado
     */
    public static function logStatusUpdate(CreditNote $creditNote, string $oldStatus, string $newStatus): void
    {
        Log::channel('envionotacredito')->info('=== ESTADO DE NOTA DE CRÉDITO ACTUALIZADO ===', [
            'credit_note_id' => $creditNote->id,
            'serie' => $creditNote->serie,
            'numero' => $creditNote->numero,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'timestamp' => now()->toISOString(),
        ]);
    }
}