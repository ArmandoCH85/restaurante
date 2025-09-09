<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\CreditNoteLogger;
use Illuminate\Support\Facades\Log;

class CreditNoteErrorLogger
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $response = $next($request);
            
            // Si la respuesta contiene errores relacionados con notas de crédito
            if ($this->isCreditNoteRelated($request) && $response->getStatusCode() >= 400) {
                CreditNoteLogger::logError('Error HTTP en operación de nota de crédito', [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'status_code' => $response->getStatusCode(),
                    'user_id' => auth()->id(),
                    'request_data' => $this->sanitizeRequestData($request->all())
                ]);
            }
            
            return $response;
            
        } catch (\Exception $e) {
            // Capturar excepciones relacionadas con notas de crédito
            if ($this->isCreditNoteRelated($request) || $this->isCreditNoteException($e)) {
                CreditNoteLogger::logError('Excepción en operación de nota de crédito', [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'user_id' => auth()->id(),
                    'request_data' => $this->sanitizeRequestData($request->all()),
                    'trace' => $e->getTraceAsString()
                ]);
            }
            
            // Re-lanzar la excepción para que sea manejada normalmente
            throw $e;
        }
    }
    
    /**
     * Determinar si la request está relacionada con notas de crédito
     */
    private function isCreditNoteRelated(Request $request): bool
    {
        $url = $request->fullUrl();
        $routeName = $request->route()?->getName() ?? '';
        
        // Verificar URL
        $creditNotePatterns = [
            '/credit-note/',
            '/nota-credito/',
            '/emitir-nota-credito/',
            '/admin/credit-notes',
            '/filament/admin/credit-notes'
        ];
        
        foreach ($creditNotePatterns as $pattern) {
            if (strpos($url, $pattern) !== false) {
                return true;
            }
        }
        
        // Verificar nombre de ruta
        $routePatterns = [
            'credit-note',
            'nota-credito',
            'filament.admin.resources.credit-notes'
        ];
        
        foreach ($routePatterns as $pattern) {
            if (strpos($routeName, $pattern) !== false) {
                return true;
            }
        }
        
        // Verificar datos de la request
        $requestData = $request->all();
        if (isset($requestData['credit_note_id']) || 
            isset($requestData['nota_credito']) ||
            isset($requestData['emitir_nota_credito'])) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Determinar si la excepción está relacionada con notas de crédito
     */
    private function isCreditNoteException(\Exception $e): bool
    {
        $message = strtolower($e->getMessage());
        $file = $e->getFile();
        
        // Verificar mensaje de error
        $errorPatterns = [
            'credit note',
            'nota de crédito',
            'nota credito',
            'attempt to read property "serie" on array',
            'sunat credit note',
            'xml credit note'
        ];
        
        foreach ($errorPatterns as $pattern) {
            if (strpos($message, $pattern) !== false) {
                return true;
            }
        }
        
        // Verificar archivo donde ocurrió el error
        $filePatterns = [
            'CreditNote',
            'SunatService',
            'CreateCreditNote',
            'ListCreditNotes',
            'CreditNotesRelationManager'
        ];
        
        foreach ($filePatterns as $pattern) {
            if (strpos($file, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Sanitizar datos de la request para logging
     */
    private function sanitizeRequestData(array $data): array
    {
        // Remover datos sensibles
        $sensitiveKeys = [
            'password',
            'password_confirmation',
            'token',
            '_token',
            'api_key',
            'secret'
        ];
        
        foreach ($sensitiveKeys as $key) {
            if (isset($data[$key])) {
                $data[$key] = '[REDACTED]';
            }
        }
        
        // Limitar tamaño de arrays grandes
        foreach ($data as $key => $value) {
            if (is_array($value) && count($value) > 10) {
                $data[$key] = array_slice($value, 0, 10) + ['...' => 'truncated'];
            }
            
            if (is_string($value) && strlen($value) > 1000) {
                $data[$key] = substr($value, 0, 1000) . '... [truncated]';
            }
        }
        
        return $data;
    }
}