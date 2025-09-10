<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\AppSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class QpsService
{
    private $baseUrl;
    private $tokenUrl;
    private $apiUrl;
    private $username;
    private $password;
    private $token;
    private $tokenExpiry;
    private $ruc;
    private $usuarioSol;
    private $claveSol;

    public function __construct()
    {
        // Verificar si usar configuración dinámica desde base de datos
        $useDynamicConfig = config('services.qps.use_dynamic_config', true);
        
        if ($useDynamicConfig) {
            // Configuración dinámica desde base de datos
            $this->loadDynamicConfiguration();
        } else {
            // Configuración estática desde config/services.php
            $this->loadStaticConfiguration();
        }
        
        $this->token = null;
        $this->tokenExpiry = null;
        
        // Configuración de la empresa para SUNAT
        $this->ruc = \App\Models\AppSetting::getSetting('Empresa', 'ruc');
        $this->usuarioSol = \App\Models\AppSetting::getSetting('Empresa', 'usuario_sol');
        $this->claveSol = \App\Models\AppSetting::getSetting('Empresa', 'clave_sol');
    }

    /**
     * Cargar configuración dinámica desde la base de datos
     */
    private function loadDynamicConfiguration(): void
    {
        // Determinar el entorno actual (beta o production)
        $isProduction = \App\Models\AppSetting::getSetting('FacturacionElectronica', 'sunat_production') === '1';
        $environment = $isProduction ? 'production' : 'beta';
        
        // Obtener endpoint dinámico
        $endpoint = \App\Models\AppSetting::getQpseEndpointByEnvironmentFromFacturacion($environment);
        
        if ($endpoint) {
            $this->baseUrl = $endpoint;
            $this->tokenUrl = $endpoint . '/api/auth/cpe/token';
            $this->apiUrl = $endpoint . '/api/cpe';
        } else {
            // Fallback a configuración por defecto
            $this->loadStaticConfiguration();
            return;
        }
        
        // Obtener credenciales dinámicas
        $credentials = \App\Models\AppSetting::getQpseCredentialsFromFacturacion();
        
        if ($credentials['username'] && $credentials['password']) {
            $this->username = $credentials['username'];
            $this->password = $credentials['password'];
        } else {
            // Fallback a credenciales de configuración estática
            $this->username = config('services.qps.username');
            $this->password = config('services.qps.password');
        }
        
        Log::channel('qps')->info('QPS: Configuración dinámica cargada', [
            'environment' => $environment,
            'endpoint' => $endpoint,
            'has_credentials' => !empty($credentials['username']) && !empty($credentials['password'])
        ]);
    }

    /**
     * Cargar configuración estática desde config/services.php
     */
    private function loadStaticConfiguration(): void
    {
        $this->baseUrl = config('services.qps.base_url', 'https://demo-cpe.qpse.pe');
        $this->tokenUrl = config('services.qps.token_url', 'https://demo-cpe.qpse.pe/api/auth/cpe/token');
        $this->apiUrl = config('services.qps.api_url', 'https://demo-cpe.qpse.pe/api/cpe');
        $this->username = config('services.qps.username');
        $this->password = config('services.qps.password');
        
        Log::channel('qps')->info('QPS: Configuración estática cargada desde config/services.php');
    }

    /**
     * Obtener token de acceso de QPS
     * 
     * @return string Token de acceso
     * @throws Exception Si no se puede obtener el token
     */
    public function getAccessToken(): string
    {
        // Verificar si el token actual sigue siendo válido
        if ($this->token && $this->tokenExpiry && now()->timestamp < $this->tokenExpiry) {
            Log::channel('qps')->info('QPS: Usando token existente válido');
            return $this->token;
        }

        // Validar configuración antes de solicitar token
        $this->validateConfiguration();

        Log::channel('qps')->info('QPS: Solicitando nuevo token de acceso', [
            'url' => $this->tokenUrl,
            'username' => $this->username ? substr($this->username, 0, 3) . '***' : 'NO_CONFIGURADO'
        ]);

        try {
            $response = Http::timeout(90)
                ->retry(3, 1000) // 3 reintentos con 1 segundo de espera
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json'
                ])
                ->post($this->tokenUrl, [
                    'usuario' => $this->username,
                    'contraseña' => $this->password
                ]);

            // Manejo específico de códigos de error HTTP
            if (!$response->successful()) {
                $statusCode = $response->status();
                $errorBody = $response->body();
                
                $errorMessage = match ($statusCode) {
                    401 => 'Credenciales inválidas. Verifique usuario y contraseña QPSE.',
                    403 => 'Acceso denegado. Verifique permisos de la cuenta QPSE.',
                    404 => 'Endpoint no encontrado. Verifique la URL del servicio QPSE.',
                    429 => 'Demasiadas solicitudes. Intente nuevamente en unos minutos.',
                    500, 502, 503, 504 => 'Error del servidor QPSE. Intente nuevamente más tarde.',
                    default => "Error HTTP {$statusCode} del servidor QPSE."
                };
                
                Log::channel('qps')->error('QPS: Error HTTP al obtener token', [
                    'status_code' => $statusCode,
                    'error_message' => $errorMessage,
                    'response_body' => $errorBody,
                    'url' => $this->tokenUrl
                ]);
                
                throw new Exception($errorMessage . " Código: {$statusCode}");
            }

            $data = $response->json();

            // Validar estructura de respuesta
            if (!is_array($data)) {
                throw new Exception('Respuesta inválida del servidor QPSE: no es un JSON válido.');
            }

            if (!isset($data['token_acceso'])) {
                Log::channel('qps')->error('QPS: Respuesta sin token_acceso', ['response' => $data]);
                throw new Exception('El servidor QPSE no devolvió un token de acceso válido.');
            }

            if (!isset($data['expira_en']) || !is_numeric($data['expira_en'])) {
                Log::channel('qps')->warning('QPS: Respuesta sin tiempo de expiración válido', ['response' => $data]);
                // Usar tiempo por defecto de 1 hora si no se especifica
                $data['expira_en'] = 3600;
            }

            $this->token = $data['token_acceso'];
            $this->tokenExpiry = now()->timestamp + (int)$data['expira_en'] - 30; // 30 segundos de margen

            Log::channel('qps')->info('QPS: Token obtenido exitosamente', [
                'token_prefix' => substr($this->token, 0, 10) . '...',
                'expira_en_segundos' => $data['expira_en'],
                'expira_timestamp' => $this->tokenExpiry,
                'expira_datetime' => date('Y-m-d H:i:s', $this->tokenExpiry)
            ]);

            return $this->token;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $errorMsg = 'No se pudo conectar con el servidor QPSE. Verifique su conexión a internet y la URL del servicio.';
            Log::channel('qps')->error('QPS: Error de conexión', [
                'error' => $e->getMessage(),
                'url' => $this->tokenUrl
            ]);
            throw new Exception($errorMsg);
        } catch (\Illuminate\Http\Client\RequestException $e) {
            $errorMsg = 'Error en la solicitud HTTP al servidor QPSE.';
            Log::channel('qps')->error('QPS: Error de solicitud HTTP', [
                'error' => $e->getMessage(),
                'url' => $this->tokenUrl
            ]);
            throw new Exception($errorMsg . ' ' . $e->getMessage());
        } catch (Exception $e) {
            // Re-lanzar excepciones ya manejadas
            if (str_contains($e->getMessage(), 'QPSE')) {
                throw $e;
            }
            
            Log::channel('qps')->error('QPS: Error inesperado al obtener token', [
                'error' => $e->getMessage(),
                'url' => $this->tokenUrl,
                'trace' => $e->getTraceAsString()
            ]);
            throw new Exception('Error inesperado al obtener token QPSE: ' . $e->getMessage());
        }
    }

    /**
     * Validar que la configuración esté completa
     * 
     * @throws Exception Si falta configuración
     */
    private function validateConfiguration(): void
    {
        $errors = [];
        
        if (empty($this->tokenUrl)) {
            $errors[] = 'URL del token QPSE no configurada';
        }
        
        if (empty($this->username)) {
            $errors[] = 'Usuario QPSE no configurado';
        }
        
        if (empty($this->password)) {
            $errors[] = 'Contraseña QPSE no configurada';
        }
        
        if (!empty($errors)) {
            $errorMessage = 'Configuración QPSE incompleta: ' . implode(', ', $errors);
            Log::channel('qps')->error('QPS: Configuración incompleta', ['errors' => $errors]);
            throw new Exception($errorMessage);
        }
    }

    /**
     * Enviar XML sin firmar para procesamiento integrado (firma + envío)
     * 
     * @param string $xmlContent XML sin firmar
     * @param string $filename Nombre del archivo XML
     * @return array Respuesta del procesamiento
     * @throws Exception Si hay error en el procesamiento
     */
    public function sendUnsignedXmlIntegrated(string $xmlContent, string $filename): array
    {
        // Validar nombre antes de cualquier llamada a QPS
        $this->validateFilename($filename);
        $token = $this->getAccessToken();
     
         // Convertir XML a base64
         $xmlBase64 = base64_encode($xmlContent);
     
         $data = [
             'tipo_integracion' => 1, // 1 para procesamiento completo (firma + envío)
             'nombre_archivo' => $filename,
             'contenido_archivo' => $xmlBase64
         ];
     
         Log::channel('qps')->info('QPS: INTEGRADO - Enviando XML para procesamiento completo', [
             'filename' => $filename,
             'xml_size' => strlen($xmlContent),
             'xml_base64_length' => strlen($xmlBase64),
             'token_prefix' => substr($token, 0, 10) . '...',
             'metodo' => 'Integrado (firma + envío)',
             'endpoint' => $this->apiUrl . '/generar',
             'tipo_integracion' => 1
         ]);

        try {
            $response = Http::timeout(60)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token
                ])
                ->post($this->apiUrl . '/generar', $data);

            if (!$response->successful()) {
                throw new Exception('Error en procesamiento integrado QPS: ' . $response->status() . ' - ' . $response->body());
            }

            $responseData = $response->json();

            Log::channel('qps')->info('QPS: Respuesta del procesamiento integrado', [
                'filename' => $filename,
                'response_keys' => array_keys($responseData),
                'success' => $response->successful()
            ]);

            return [
                'success' => true,
                'data' => $responseData,
                'cdr_content' => $responseData['cdr_base64'] ?? null,
                'message' => 'XML procesado exitosamente a través de QPS (integrado)'
            ];

        } catch (Exception $e) {
            Log::channel('qps')->error('QPS: Error en procesamiento integrado', [
                'filename' => $filename,
                'error' => $e->getMessage(),
                'url' => $this->apiUrl . '/enviar'
            ]);
            
            throw new Exception('Error en procesamiento integrado QPS: ' . $e->getMessage());
        }
    }

    /**
     * Enviar XML firmado a SUNAT a través de QPS
     * 
     * @param string $xmlContent XML firmado en base64
     * @param string $filename Nombre del archivo XML
     * @return array Respuesta del envío
     * @throws Exception Si hay error en el envío
     */
    public function sendSignedXml(string $xmlContent, string $filename): array
    {
        $this->validateFilename($filename);
        $token = $this->getAccessToken();

        $data = [
            'nombre_xml_firmado' => $filename,
            'contenido_xml_firmado' => base64_encode($xmlContent)
        ];

        Log::channel('qps')->info('QPS: Enviando XML firmado a SUNAT', [
            'filename' => $filename,
            'xml_size' => strlen($xmlContent),
            'token_prefix' => substr($token, 0, 10) . '...'
        ]);
        
        // Log crítico para debugging error 0161
        Log::channel('qps')->info('QPS: ENVÍO - Datos enviados', [
            'filename' => $filename,
            'nombre_xml_firmado' => $filename,
            'xml_base64_length' => strlen($xmlContent),
            'endpoint' => $this->apiUrl . '/enviar'
        ]);

        // Log adicional para debugging
        Log::channel('qps')->info('QPS: Datos de envío', [
            'filename' => $filename,
            'xml_base64_length' => strlen($xmlContent),
            'xml_base64_start' => substr($xmlContent, 0, 50),
            'json_payload_size' => strlen(json_encode($data))
        ]);

        try {
            $response = Http::timeout(60)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token
                ])
                ->post($this->apiUrl . '/enviar', $data);

            if (!$response->successful()) {
                throw new Exception('Error en envío QPS: ' . $response->status() . ' - ' . $response->body());
            }

            $data = $response->json();

            Log::channel('qps')->info('QPS: Respuesta recibida de SUNAT', [
                'filename' => $filename,
                'response_keys' => array_keys($data),
                'success' => $response->successful()
            ]);

            return [
                'success' => true,
                'data' => $data,
                'cdr_content' => $data['cdr_base64'] ?? null,
                'message' => 'XML enviado exitosamente a través de QPS'
            ];

        } catch (Exception $e) {
            Log::channel('qps')->error('QPS: Error al enviar XML', [
                'filename' => $filename,
                'error' => $e->getMessage(),
                'url' => $this->apiUrl . '/enviar',
                'response_headers' => isset($response) ? $response->headers() : null
            ]);
            
            throw new Exception('Error al enviar XML a través de QPS: ' . $e->getMessage());
        }
    }

    /**
     * Enviar factura completa usando QPS (integración con SunatService)
     * 
     * @param Invoice $invoice Factura a enviar
     * @return array Resultado del envío
     */
    public function sendInvoiceViaQps(Invoice $invoice): array
    {
        try {
            // Validar que solo se procesen Boletas (B) y Facturas (F), NO Notas de Venta (NV)
            if (!in_array($invoice->invoice_type, ['receipt', 'invoice']) || str_starts_with($invoice->series, 'NV')) {
                throw new Exception("Tipo de documento no válido para SUNAT: {$invoice->invoice_type} con serie {$invoice->series}. Solo se permiten Boletas (B) y Facturas (F), NO Notas de Venta (NV).");
            }
            
            Log::channel('qps')->info('QPS: Iniciando envío de factura', [
                'invoice_id' => $invoice->id,
                'series_number' => $invoice->series . '-' . $invoice->number,
                'invoice_type' => $invoice->invoice_type
            ]);

            // Actualizar estado a enviando
            $invoice->update(['sunat_status' => 'ENVIANDO']);

            // PASO 1: Generar XML SIN firmar usando Greenter
            $sunatService = new SunatService();
            $greenterInvoice = $sunatService->createGreenterInvoice($invoice);
            
            // Obtener XML sin firmar
            $unsignedXml = $sunatService->getUnsignedXml($greenterInvoice);
            
            if (!$unsignedXml) {
                throw new Exception('No se pudo generar el XML sin firmar');
            }

            Log::channel('qps')->info('QPS: PASO 1 - XML sin firmar generado', [
                'invoice_id' => $invoice->id,
                'xml_length' => strlen($unsignedXml),
                'xml_starts_with' => substr($unsignedXml, 0, 100)
            ]);

            // Generar nombre del archivo
            $filename = $this->generateXmlFilename($invoice);
            
            // Validar formato del nombre de archivo
            $this->validateFilename($filename);
            
            Log::channel('qps')->info('QPS: Validación nombre archivo', [
                'invoice_id' => $invoice->id,
                'filename' => $filename,
                'length' => strlen($filename),
                'format_valid' => preg_match('/^\d{11}-\d{2}-[A-Z0-9]+-\d+$/', $filename),
                'ruc' => AppSetting::getSetting('Empresa', 'ruc'),
                'tipo_doc' => $invoice->invoice_type === 'invoice' ? '01' : '03',
                'serie' => $invoice->series,
                'correlativo' => $invoice->number
            ]);
            
            // PASO 2: Intentar flujo de 2 pasos, si falla usar integrado
            Log::channel('qps')->info('QPS: PASO 2 - Intentando flujo de 2 pasos', [
                'invoice_id' => $invoice->id,
                'filename' => $filename,
                'xml_size' => strlen($unsignedXml),
                'metodo' => 'Flujo 2 pasos con fallback'
            ]);
            
            $signResult = $this->signXml($unsignedXml, $filename);
            
            if (!$signResult['success']) {
                Log::channel('qps')->warning('QPS: Firma falló, usando flujo integrado como fallback', [
                    'invoice_id' => $invoice->id,
                    'error' => $signResult['message']
                ]);
                
                // Fallback: usar flujo integrado
                $qpsResult = $this->sendUnsignedXmlIntegrated($unsignedXml, $filename);
                $signResult = ['signed_xml' => $unsignedXml]; // Para guardar el XML original
            } else {
                // PASO 3: Enviar XML firmado a SUNAT
                Log::channel('qps')->info('QPS: PASO 3 - Enviando XML firmado a SUNAT', [
                    'invoice_id' => $invoice->id,
                    'filename' => $filename,
                    'signed_xml_size' => strlen($signResult['signed_xml']),
                    'hash_code' => $signResult['hash_code']
                ]);
                
                try {
                    $qpsResult = $this->sendSignedXml($signResult['signed_xml'], $filename);
                } catch (Exception $e) {
                    // Si hay error 0161, usar flujo integrado como fallback
                    if (strpos($e->getMessage(), '0161') !== false) {
                        Log::channel('qps')->warning('QPS: Error 0161 detectado, usando flujo integrado como fallback', [
                            'invoice_id' => $invoice->id,
                            'error' => $e->getMessage()
                        ]);
                        
                        $qpsResult = $this->sendUnsignedXmlIntegrated($unsignedXml, $filename);
                        $signResult = ['signed_xml' => $unsignedXml]; // Para guardar el XML original
                    } else {
                        throw $e;
                    }
                }
            }
            
            if ($qpsResult['success']) {
                // Guardar archivos
                $documentName = $invoice->series . '-' . $invoice->number;
                Storage::put("sunat/xml/{$documentName}.xml", $signResult['signed_xml']);
                
                // Nuevo: soportar múltiples formatos de CDR devueltos por QPS
                $cdrBinary = null;
                $cdrBase64 = $qpsResult['cdr_content'] ?? null; // preferido desde métodos internos
                $qpsData = $qpsResult['data'] ?? [];
                
                if (!$cdrBase64 && is_array($qpsData)) {
                    // Intentar cdr_base64 primero
                    if (!empty($qpsData['cdr_base64'])) {
                        $cdrBase64 = $qpsData['cdr_base64'];
                    } elseif (!empty($qpsData['cdr'])) {
                        // El campo 'cdr' puede ser URL o base64 según el proveedor
                        $possible = $qpsData['cdr'];
                        if (is_string($possible) && preg_match('/^https?:\\/\\//i', $possible)) {
                            try {
                                $resp = Http::timeout(90)->get($possible);
                                if ($resp->successful()) {
                                    $cdrBinary = $resp->body();
                                    Log::channel('qps')->info('QPS: CDR descargado desde URL', [
                                        'document_name' => $documentName,
                                        'url' => substr($possible, 0, 120) . (strlen($possible) > 120 ? '...' : '')
                                    ]);
                                } else {
                                    Log::channel('qps')->warning('QPS: No se pudo descargar CDR desde URL', [
                                        'status' => $resp->status(),
                                        'url' => $possible
                                    ]);
                                }
                            } catch (Exception $e) {
                                Log::channel('qps')->error('QPS: Error descargando CDR desde URL', [
                                    'error' => $e->getMessage(),
                                    'url' => $possible
                                ]);
                            }
                        } else {
                            // Intentar decodificar como base64 estricto
                            $decoded = base64_decode((string)$possible, true);
                            if ($decoded !== false) {
                                $cdrBinary = $decoded;
                                Log::channel('qps')->info('QPS: CDR obtenido desde campo "cdr" en base64');
                            }
                        }
                    } elseif (!empty($qpsData['cdr_url'])) {
                        // Algunos retornan cdr_url
                        $url = $qpsData['cdr_url'];
                        try {
                            $resp = Http::timeout(90)->get($url);
                            if ($resp->successful()) {
                                $cdrBinary = $resp->body();
                                Log::channel('qps')->info('QPS: CDR descargado desde cdr_url', [
                                    'document_name' => $documentName,
                                    'url' => substr($url, 0, 120) . (strlen($url) > 120 ? '...' : '')
                                ]);
                            } else {
                                Log::channel('qps')->warning('QPS: No se pudo descargar CDR desde cdr_url', [
                                    'status' => $resp->status(),
                                    'url' => $url
                                ]);
                            }
                        } catch (Exception $e) {
                            Log::channel('qps')->error('QPS: Error descargando CDR desde cdr_url', [
                                'error' => $e->getMessage(),
                                'url' => $url
                            ]);
                        }
                    }
                }
                
                if (!$cdrBinary && $cdrBase64) {
                    $decoded = base64_decode($cdrBase64, true);
                    if ($decoded !== false) {
                        $cdrBinary = $decoded;
                        Log::channel('qps')->info('QPS: CDR obtenido desde base64 (cdr_base64/cdr_content)');
                    } else {
                        Log::channel('qps')->warning('QPS: Falló decodificación base64 del CDR');
                    }
                }

                if ($cdrBinary) {
                    // Asegurar directorio
                    if (!Storage::exists('sunat/cdr')) {
                        Storage::makeDirectory('sunat/cdr');
                    }

                    // Detectar si el contenido es un ZIP válido (firma PK)
                    $isZip = (strlen($cdrBinary) >= 2) && (substr($cdrBinary, 0, 2) === 'PK');

                    if ($isZip) {
                        Storage::put("sunat/cdr/{$documentName}.zip", $cdrBinary);
                        Log::channel('qps')->info('QPS: CDR guardado como ZIP (firma PK detectada)', [
                            'document_name' => $documentName,
                            'size' => strlen($cdrBinary)
                        ]);
                    } else {
                        // El proveedor devolvió XML plano: crear un ZIP estándar con nombre R-<filename>.xml
                        try {
                            $tmpPath = tempnam(sys_get_temp_dir(), 'cdrzip_');
                            $zip = new \ZipArchive();
                            if ($zip->open($tmpPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                                throw new \Exception('No se pudo crear ZIP temporal para CDR');
                            }
                            $entryName = 'R-' . ($filename ?? ($invoice->series . '-' . $invoice->number)) . '.xml';
                            $zip->addFromString($entryName, $cdrBinary);
                            $zip->close();

                            $zipBytes = file_get_contents($tmpPath);
                            @unlink($tmpPath);

                            if ($zipBytes === false) {
                                throw new \Exception('No se pudo leer ZIP temporal del CDR');
                            }

                            Storage::put("sunat/cdr/{$documentName}.zip", $zipBytes);
                            Log::channel('qps')->info('QPS: CDR recibido como XML y empaquetado a ZIP', [
                                'document_name' => $documentName,
                                'entry' => $entryName,
                                'zip_size' => strlen($zipBytes)
                            ]);
                        } catch (\Exception $e) {
                            Log::channel('qps')->error('QPS: Error al empaquetar CDR XML en ZIP', [
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                } else {
                    Log::channel('qps')->warning('QPS: No se pudo obtener contenido de CDR desde la respuesta');
                }
                
                // Actualizar estado de la factura
                $invoice->update([
                    'sunat_status' => 'ACEPTADO',
                    'sunat_code' => '0000',
                    'sunat_description' => 'Comprobante enviado exitosamente vía QPS',
                    'xml_path' => "sunat/xml/{$documentName}.xml",
                    'cdr_path' => "sunat/cdr/{$documentName}.zip"
                ]);
                
                $metodoUsado = isset($signResult['hash_code']) ? 'Flujo 2 pasos (firmar + enviar)' : 'Flujo integrado (fallback)';
                
                Log::channel('qps')->info('QPS: FLUJO COMPLETO - Factura procesada exitosamente', [
                    'invoice_id' => $invoice->id,
                    'metodo' => $metodoUsado,
                    'hash_code' => $signResult['hash_code'] ?? 'N/A (flujo integrado)',
                    'document_name' => $documentName,
                    'proceso' => '3 pasos: Generar XML → Firmar con QPS → Enviar a SUNAT'
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Comprobante enviado exitosamente a SUNAT vía QPS',
                    'xml_url' => Storage::url("sunat/xml/{$documentName}.xml"),
                    'cdr_url' => Storage::url("sunat/cdr/{$documentName}.zip")
                ];
            } else {
                throw new Exception('Error en el envío QPS: ' . ($qpsResult['message'] ?? 'Error desconocido'));
            }
            
        } catch (Exception $e) {
            // Detectar si es error 0161 específicamente
            $isError0161 = strpos($e->getMessage(), '0161') !== false || 
                          strpos($e->getMessage(), 'nombre del archivo XML no coincide') !== false;
            
            if ($isError0161) {
                Log::channel('qps')->error('QPS: ERROR 0161 DETECTADO', [
                    'invoice_id' => $invoice->id,
                    'error_message' => $e->getMessage(),
                    'filename_usado' => $filename ?? 'N/A',
                    'recomendacion' => 'Verificar configuración QPS y formato de nombres',
                    'solucion_sugerida' => 'Contactar soporte QPS si persiste después de validaciones'
                ]);
            }
            
            // Actualizar estado de error
            $invoice->update([
                'sunat_status' => 'RECHAZADO',
                'sunat_code' => $isError0161 ? '0161' : ($e->getCode() ?: '9999'),
                'sunat_description' => $e->getMessage()
            ]);
            
            Log::channel('qps')->error('QPS: Error al enviar factura', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'error_code' => $isError0161 ? '0161' : ($e->getCode() ?: '9999'),
                'is_error_0161' => $isError0161
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $isError0161 ? 'Error 0161: Problema con nombres de archivos en QPS' : 'Error al enviar comprobante vía QPS',
                'error_code' => $isError0161 ? '0161' : ($e->getCode() ?: '9999')
            ];
        }
    }

    /**
     * Generar nombre del archivo XML
     * 
     * @param Invoice $invoice
     * @return string
     * @throws Exception
     */
    public function generateXmlFilename(Invoice $invoice): string
    {
        $ruc = AppSetting::getSetting('Empresa', 'ruc') ?: '20000000000';
        $tipoDoc = $invoice->invoice_type === 'invoice' ? '01' : '03';
        $serie = $invoice->series;
        // Normalizar serie a 4 caracteres si viene como B02/F02 -> B002/F002
        if (preg_match('/^[BF][0-9]{2}$/', $serie)) {
            $serie = $serie[0] . '0' . substr($serie, 1);
        }
        // ✅ CORREGIDO FINAL: Sin padding y SIN extensión .xml según colección Postman
        $correlativo = ltrim($invoice->number, '0') ?: '0'; // Eliminar ceros del padding
        
        // IMPORTANTE: Formato correcto para QPS - SIN extensión .xml
        $filename = "{$ruc}-{$tipoDoc}-{$serie}-{$correlativo}";
        
        // Validar que el nombre solo contiene caracteres permitidos
        if (!preg_match('/^[0-9A-Z\-]+$/i', $filename)) {
            throw new Exception("Nombre de archivo contiene caracteres no permitidos: {$filename}");
        }
        
        return $filename;
    }
    
    /**
     * Validar formato del nombre de archivo
     * 
     * @param string $filename
     * @throws Exception
     */
    private function validateFilename(string $filename): void
    {
        // Validar formato según tipo de documento
        $isInvoiceFormat = preg_match('/^\d{11}-\d{2}-[A-Z0-9]+-\d+$/', $filename); // RUC-TipoDoc-Serie-Correlativo
        $isSummaryFormat = preg_match('/^\d{11}-RC-\d{8}-\d{3}$/', $filename); // RUC-RC-YYYYMMDD-XXX
        
        if (!$isInvoiceFormat && !$isSummaryFormat) {
            throw new Exception("Formato de nombre de archivo inválido: {$filename}. Esperado: RUC-TipoDoc-Serie-Correlativo o RUC-RC-YYYYMMDD-XXX");
        }
        
        // Verificar longitud
        if (strlen($filename) > 100) {
            throw new Exception("Nombre de archivo muy largo: {$filename}");
        }
    }

    /**
     * Firmar XML usando QPS
     * 
     * @param string $unsignedXml XML sin firmar
     * @param string $filename Nombre del archivo
     * @return array
     */
    public function signXml(string $unsignedXml, string $filename): array
    {
        try {
            $this->validateFilename($filename);
            Log::channel('qps')->info('QPS: Iniciando firma de XML', [
                'filename' => $filename,
                'xml_size' => strlen($unsignedXml)
            ]);

            $token = $this->getAccessToken();
            if (!$token) {
                throw new Exception('No se pudo obtener token de QPS');
            }

            $xmlBase64 = base64_encode($unsignedXml);
            
            $data = [
                'tipo_integracion' => 0,
                'nombre_archivo' => $filename,
                'contenido_archivo' => $xmlBase64
            ];
            
            // Log crítico para debugging error 0161
            Log::channel('qps')->info('QPS: FIRMA - Datos enviados', [
                'filename' => $filename,
                'nombre_archivo' => $filename,
                'xml_base64_length' => strlen($xmlBase64),
                'endpoint' => $this->apiUrl . '/generar'
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->timeout(90)->post($this->apiUrl . '/generar', $data);

            Log::channel('qps')->info('QPS: Respuesta de firma', [
                'status' => $response->status(),
                'response_size' => strlen($response->body())
            ]);

            if ($response->successful()) {
                $result = $response->json();
                
                if (isset($result['xml'])) {
                    $signedXml = base64_decode($result['xml']);
                    
                    Log::channel('qps')->info('QPS: XML firmado exitosamente', [
                        'filename' => $filename,
                        'signed_xml_size' => strlen($signedXml),
                        'hash_code' => $result['codigo_hash'] ?? 'N/A'
                    ]);
                    
                    return [
                        'success' => true,
                        'signed_xml' => $signedXml,
                        'hash_code' => $result['codigo_hash'] ?? null,
                        'message' => $result['mensaje'] ?? 'XML firmado correctamente'
                    ];
                } else {
                    throw new Exception('Respuesta de QPS no contiene XML firmado');
                }
            } else {
                $errorBody = $response->json();
                $errorMessage = $errorBody['message'] ?? 'Error desconocido';
                
                // Log detallado del error para debugging
                Log::channel('qps')->error('QPS: Error detallado en firma', [
                    'status' => $response->status(),
                    'headers' => $response->headers(),
                    'body' => $response->body(),
                    'error_body' => $errorBody,
                    'token_used' => substr($token, 0, 20) . '...',
                    'endpoint' => $this->apiUrl . '/generar'
                ]);
                
                // Manejar errores específicos de QPS
                if (strpos($errorMessage, 'Data too long for column') !== false) {
                    throw new Exception('Error de configuración en QPS: El servidor tiene limitaciones en el tamaño de datos. Contacte al soporte de QPS.');
                } elseif (strpos($errorMessage, 'SQLSTATE') !== false) {
                    throw new Exception('Error de base de datos en QPS: ' . $errorMessage . '. Contacte al soporte de QPS.');
                } else {
                    throw new Exception('Error en QPS: ' . $errorMessage);
                }
            }
            
        } catch (Exception $e) {
            Log::channel('qps')->error('QPS: Error al firmar XML', [
                'filename' => $filename,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error' => 'Error al firmar XML con QPS'
            ];
        }
    }

    /**
     * Verificar estado del servicio QPS
     * 
     * @return bool
     */
    public function isServiceAvailable(): bool
    {
        try {
            $response = Http::timeout(10)->get($this->baseUrl);
            return $response->successful();
        } catch (Exception $e) {
            Log::warning('QPS: Servicio no disponible', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Obtener configuración actual
     * 
     * @return array
     */
    public function getConfiguration(): array
    {
        return [
            'base_url' => config('services.qps.base_url', $this->baseUrl),
            'username' => config('services.qps.username', $this->username),
            'has_token' => !is_null($this->token),
            'token_expires_at' => $this->tokenExpiry ? date('Y-m-d H:i:s', $this->tokenExpiry) : null,
            'service_available' => $this->isServiceAvailable()
        ];
    }
}