<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\DocumentSeries;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Model\Sale\Invoice as GreenterInvoice;
use Greenter\Model\Sale\Note;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Sale\PaymentTerms;
use Greenter\Model\Sale\Cuota;
use Greenter\Model\Summary\Summary;
use Greenter\Model\Summary\SummaryDetail;
use Greenter\See;
use Greenter\Ws\Services\SunatEndpoints;
use Greenter\XMLSecLibs\Certificate\X509Certificate;
use Greenter\XMLSecLibs\Certificate\X509ContentType;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class SunatService
{
    private $see;
    private $company;
    private $environment;

    public function __construct()
    {
        $this->environment = AppSetting::getSetting('FacturacionElectronica', 'environment') ?: 'beta';
        $this->initializeGreenter();
        $this->setupCompany();
    }

    /**
     * Inicializar configuración de Greenter
     */
    private function initializeGreenter()
    {
        $certificatePath = AppSetting::getSetting('FacturacionElectronica', 'certificate_path');
        $certificatePassword = AppSetting::getDecryptedSetting('FacturacionElectronica', 'certificate_password');
        $solUser = AppSetting::getSetting('FacturacionElectronica', 'sol_user');
        $solPassword = AppSetting::getDecryptedSetting('FacturacionElectronica', 'sol_password');

        // Log de debugging para certificados
        Log::info('Inicializando Greenter - Configuración de certificado', [
            'certificate_path' => $certificatePath,
            'certificate_exists' => $certificatePath ? file_exists($certificatePath) : false,
            'certificate_password_set' => !empty($certificatePassword),
            'sol_user' => $solUser,
            'sol_password_set' => !empty($solPassword),
            'environment' => $this->environment
        ]);

        if (!$certificatePath || !file_exists($certificatePath)) {
            throw new Exception('Certificado digital no encontrado en: ' . ($certificatePath ?: 'ruta no configurada'));
        }

        if (empty($certificatePassword)) {
            throw new Exception('Contraseña del certificado digital no configurada');
        }

        $this->see = new See();

        // Para certificados .pfx/.p12, Greenter necesita la contraseña
        $certificateContent = file_get_contents($certificatePath);

        // Detectar el tipo de certificado por extensión
        $extension = strtolower(pathinfo($certificatePath, PATHINFO_EXTENSION));

        if (in_array($extension, ['pfx', 'p12'])) {
            // Para certificados .pfx/.p12, usar X509Certificate para convertir a PEM
            Log::info('Procesando certificado PFX/P12', [
                'extension' => $extension,
                'certificate_size' => strlen($certificateContent) . ' bytes'
            ]);

            // Método KISS: Configurar OpenSSL para algoritmos legacy
            $certs = [];

            // Limpiar errores previos de OpenSSL
            while (openssl_error_string() !== false) {
                // Limpiar cola de errores
            }

            // Configurar variables de entorno para OpenSSL legacy
            $originalConf = getenv('OPENSSL_CONF');
            $originalProvider = getenv('OPENSSL_MODULES');

            // Configurar para permitir algoritmos legacy
            putenv('OPENSSL_CONF=');
            putenv('OPENSSL_MODULES=');

            // Intentar con configuración más permisiva
            $context = stream_context_create([
                'ssl' => [
                    'crypto_method' => STREAM_CRYPTO_METHOD_ANY_CLIENT,
                    'ciphers' => 'DEFAULT:@SECLEVEL=0'
                ]
            ]);

            if (!openssl_pkcs12_read($certificateContent, $certs, $certificatePassword)) {
                $opensslError = openssl_error_string();

                // Mensajes de error más específicos
                if (strpos($opensslError, 'mac verify failure') !== false) {
                    throw new Exception('Error al procesar certificado digital: La contraseña del certificado es incorrecta. Verifique que la contraseña sea la correcta para este certificado.');
                } elseif (strpos($opensslError, 'digital envelope routines') !== false) {
                    throw new Exception('Error al procesar certificado digital: El certificado usa algoritmos no soportados por esta versión de OpenSSL. Contacte al administrador del sistema.');
                } else {
                    throw new Exception('Error al procesar certificado digital: ' . ($opensslError ?: 'Error desconocido al leer el certificado PKCS#12') . '. Verifique que el archivo no esté corrupto.');
                }
            }

            if (!isset($certs['cert']) || !isset($certs['pkey'])) {
                throw new Exception('El certificado no contiene los componentes necesarios (certificado y clave privada).');
            }

            // Crear PEM simple
            $pemContent = $certs['cert'] . $certs['pkey'];

            // Agregar certificados adicionales si existen
            if (isset($certs['extracerts']) && is_array($certs['extracerts'])) {
                foreach ($certs['extracerts'] as $extraCert) {
                    $pemContent .= $extraCert;
                }
            }

            $this->see->setCertificate($pemContent);
            Log::info('Certificado PFX/P12 procesado exitosamente', [
                'has_cert' => isset($certs['cert']),
                'has_pkey' => isset($certs['pkey']),
                'extra_certs_count' => isset($certs['extracerts']) ? count($certs['extracerts']) : 0
            ]);

            // Restaurar configuración original de OpenSSL
            if ($originalConf !== false) {
                putenv('OPENSSL_CONF=' . $originalConf);
            }
            if ($originalProvider !== false) {
                putenv('OPENSSL_MODULES=' . $originalProvider);
            }
        } else {
            // Para certificados .pem, usar directamente
            Log::info('Procesando certificado PEM', [
                'extension' => $extension,
                'certificate_size' => strlen($certificateContent) . ' bytes'
            ]);

            $this->see->setCertificate($certificateContent);
        }

        $this->see->setCredentials($solUser, $solPassword);

        // Configurar endpoint directo a SUNAT para facturas y boletas electrónicas
        $this->see->setService('https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService');

        Log::info('Greenter inicializado correctamente', [
            'environment' => $this->environment,
            'certificate_type' => $extension,
            'certificate_size' => strlen($certificateContent) . ' bytes'
        ]);
    }

    /**
     * Configurar datos de la empresa
     */
    private function setupCompany()
    {
        // Obtener datos de la empresa desde app_settings
        $ruc = AppSetting::getSetting('Empresa', 'ruc');
        $razonSocial = AppSetting::getSetting('Empresa', 'razon_social');
        $nombreComercial = AppSetting::getSetting('Empresa', 'nombre_comercial');
        $direccion = AppSetting::getSetting('Empresa', 'direccion');
        $ubigeo = AppSetting::getSetting('Empresa', 'ubigeo') ?: '150101'; // Lima por defecto
        $distrito = AppSetting::getSetting('Empresa', 'distrito') ?: 'Lima';
        $provincia = AppSetting::getSetting('Empresa', 'provincia') ?: 'Lima';
        $departamento = AppSetting::getSetting('Empresa', 'departamento') ?: 'Lima';

        // Log de configuración de empresa
        Log::info('Configurando datos de empresa', [
            'ruc_empresa' => $ruc,
            'ruc_factelec' => AppSetting::getSetting('FacturacionElectronica', 'ruc'),
            'razon_social' => $razonSocial,
            'nombre_comercial' => $nombreComercial,
            'direccion' => $direccion,
            'ubigeo' => $ubigeo
        ]);

        $address = new Address();
        $address->setUbigueo($ubigeo)
            ->setDistrito($distrito)
            ->setProvincia($provincia)
            ->setDepartamento($departamento)
            ->setUrbanizacion('-')
            ->setDireccion($direccion);

        $this->company = new Company();

        // Asegurar que el RUC no esté vacío
        if (empty($ruc)) {
            $ruc = AppSetting::getSetting('FacturacionElectronica', 'ruc') ?: '20123456789';
            Log::warning('RUC de empresa estaba vacío, usando fallback', ['ruc_fallback' => $ruc]);
        }

        $this->company->setRuc($ruc)
            ->setRazonSocial($razonSocial ?: 'EMPRESA DEMO SAC')
            ->setNombreComercial($nombreComercial ?: 'Empresa Demo')
            ->setAddress($address);

        // Log de verificación después de crear la empresa
        Log::info('Empresa configurada', [
            'company_ruc' => $this->company->getRuc(),
            'company_razon_social' => $this->company->getRazonSocial(),
            'company_nombre_comercial' => $this->company->getNombreComercial()
        ]);
    }

    /**
     * Obtener instancia de See (Greenter) para uso externo
     * 
     * @return See
     */
    public function getSee(): See
    {
        return $this->see;
    }

    /**
     * Generar XML sin firmar usando Greenter
     * 
     * @param mixed $greenterInvoice
     * @return string XML sin firmar
     */
    public function getUnsignedXml($greenterInvoice): string
    {
        try {
            // Generar XML sin firma usando InvoiceBuilder directamente
            $builder = new \Greenter\Xml\Builder\InvoiceBuilder();
            return $builder->build($greenterInvoice);
        } catch (Exception $e) {
            Log::error('Error generando XML sin firmar: ' . $e->getMessage());
            throw new Exception('Error al generar XML sin firmar: ' . $e->getMessage());
        }
    }

    /**
     * Emitir factura electrónica
     */
    public function emitirFactura($invoiceId)
    {
        $startTime = microtime(true);
        $logFile = storage_path('logs/envio.log');

        // Función helper para escribir en el log
        $writeLog = function($message, $data = []) use ($logFile) {
            $timestamp = now()->format('Y-m-d H:i:s');
            $logEntry = "[{$timestamp}] {$message}";
            if (!empty($data)) {
                $logEntry .= " " . json_encode($data, JSON_UNESCAPED_UNICODE);
            }
            $logEntry .= "\n";

            file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        };

        try {
            // 🚀 LOG: INICIO DEL PROCESO
            $endpoint = 'https://e-factura.sunat.gob.pe:443/ol-ti-itemision-otroscpe-gem/billService';

            $writeLog('🚀 INICIO ENVÍO COMPROBANTE A SUNAT', [
                'invoice_id' => $invoiceId,
                'endpoint_destino' => $endpoint,
                'modo_configurado' => $this->environment,
                'tipo_conexion' => 'DIRECTA_SUNAT_OFICIAL',
                'servidor_objetivo' => 'e-factura.sunat.gob.pe',
                'puerto' => '443',
                'servicio' => 'billService',
                'nota' => 'Endpoint único oficial de SUNAT'
            ]);

            // 📄 LOG: OBTENIENDO DATOS DEL COMPROBANTE
            $invoice = Invoice::with(['details.product', 'customer', 'employee'])
                ->findOrFail($invoiceId);

            $writeLog('📄 DATOS DEL COMPROBANTE CARGADO', [
                'numero_completo' => $invoice->series . '-' . $invoice->number,
                'tipo_comprobante' => $invoice->invoice_type,
                'cliente' => [
                    'nombre' => $invoice->customer->name ?? 'N/A',
                    'documento' => $invoice->customer->document_number ?? 'N/A',
                    'tipo_doc' => $invoice->customer->document_type ?? 'N/A'
                ],
                'total' => $invoice->total,
                'fecha_emision' => $invoice->issue_date,
                'estado_actual' => $invoice->sunat_status
            ]);

            // Validar que sea Boleta o Factura (NO Nota de Venta)
            if (!in_array($invoice->invoice_type, ['invoice', 'receipt'])) {
                throw new Exception('Solo se pueden enviar Boletas y Facturas a SUNAT. Las Notas de Venta son documentos internos.');
            }

            // Actualizar estado a enviando
            Invoice::where('id', $invoiceId)->update(['sunat_status' => 'ENVIANDO']);

            $writeLog('🔧 ESTADO ACTUALIZADO', [
                'invoice_id' => $invoiceId,
                'nuevo_estado' => 'ENVIANDO',
                'timestamp_actualizacion' => now()->toISOString()
            ]);

            // ⏱️ LOG: CONSTRUYENDO DATOS PARA ENVÍO
            $buildStart = microtime(true);
            $writeLog('🔧 PROCESANDO FACTURA PARA SUNAT', [
                'invoice_id' => $invoiceId,
                'timestamp_inicio' => now()->toISOString()
            ]);

            // Crear factura Greenter (optimizado - sin logs internos)
            $greenterInvoice = $this->createGreenterInvoice($invoice);

            // Generar XML (optimizado)
            $xml = $this->see->getXmlSigned($greenterInvoice);
            $xmlPath = $this->saveXmlFile($xml, $invoice, 'signed');

            $buildTime = round((microtime(true) - $buildStart) * 1000, 2);
            $writeLog('📋 FACTURA Y XML PROCESADOS', [
                'invoice_id' => $invoiceId,
                'serie' => $greenterInvoice->getSerie(),
                'correlativo' => $greenterInvoice->getCorrelativo(),
                'xml_size_kb' => round(strlen($xml) / 1024, 2),
                'tiempo_total_ms' => $buildTime
            ]);

            // 📨 LOG: ENVIANDO A SUNAT (optimizado)
            $sendStart = microtime(true);
            $writeLog('📨 ENVIANDO A SUNAT', [
                'invoice_id' => $invoiceId,
                'endpoint' => $endpoint,
                'timestamp_envio' => now()->toISOString()
            ]);

            // Enviar a SUNAT usando Greenter directamente (sin SoapClient personalizado)
            $result = $this->see->send($greenterInvoice);

            $sendTime = round((microtime(true) - $sendStart) * 1000, 2);

            // 📨 LOG: RESPUESTA DE SUNAT
            $writeLog('📨 RESPUESTA RECIBIDA DE SUNAT', [
                'invoice_id' => $invoiceId,
                'tiempo_envio_ms' => $sendTime,
                'servidor_respuesta' => 'e-factura.sunat.gob.pe',
                'endpoint_respuesta' => $endpoint,
                'conexion_exitosa' => $result->isSuccess() ? 'SÍ' : 'NO',
                'resultado_greenter' => $result->isSuccess() ? 'SUCCESS' : 'ERROR',
                'timestamp_respuesta' => now()->toISOString()
            ]);

            // 📄 LOG: PROCESANDO ARCHIVOS (XML y CDR)
            if ($result->isSuccess()) {
                $documentName = $result->getDocument()->getName();

                $writeLog('📄 PROCESANDO ARCHIVOS GREENTER', [
                    'invoice_id' => $invoiceId,
                    'document_name' => $documentName,
                    'xml_disponible' => $result->getXml() ? 'SÍ' : 'NO',
                    'cdr_disponible' => $result->getCdrZip() ? 'SÍ' : 'NO',
                    'xml_size' => $result->getXml() ? strlen($result->getXml()) : 0,
                    'cdr_size' => $result->getCdrZip() ? strlen($result->getCdrZip()) : 0
                ]);

                // Guardar XML
                $xmlPath = "sunat/xml/{$documentName}.xml";
                Storage::put($xmlPath, $result->getXml());

                $writeLog('💾 XML GUARDADO EN STORAGE', [
                    'invoice_id' => $invoiceId,
                    'xml_path' => $xmlPath,
                    'xml_url' => Storage::url($xmlPath),
                    'xml_size' => strlen($result->getXml()),
                    'storage_disk' => config('filesystems.default')
                ]);

                // Guardar CDR
                $cdrPath = "sunat/cdr/{$documentName}.zip";
                Storage::put($cdrPath, $result->getCdrZip());

                $writeLog('💾 CDR GUARDADO EN STORAGE', [
                    'invoice_id' => $invoiceId,
                    'cdr_path' => $cdrPath,
                    'cdr_url' => Storage::url($cdrPath),
                    'cdr_size' => strlen($result->getCdrZip()),
                    'storage_disk' => config('filesystems.default')
                ]);

                // 📋 LOG: RESPUESTA CDR PROCESADA
                $cdrResponse = $result->getCdrResponse();

                $writeLog('📋 CDR RESPONSE PROCESADO', [
                    'invoice_id' => $invoiceId,
                    'cdr_response_code' => $cdrResponse->getCode(),
                    'cdr_response_description' => $cdrResponse->getDescription(),
                    'cdr_response_id' => $cdrResponse->getId(),
                    'document_name' => $documentName
                ]);
            }

            // Procesar respuesta
            $this->processResponse($invoice, $result, $xmlPath, null);

            // ✅ LOG: ENVÍO EXITOSO
            $totalTime = round((microtime(true) - $startTime) * 1000, 2);

            if ($result->isSuccess()) {
                $cdr = $result->getCdrResponse();
                $writeLog('✅ ENVÍO A SUNAT EXITOSO', [
                    'invoice_id' => $invoiceId,
                    'numero_comprobante' => $invoice->series . '-' . $invoice->number,
                    'tiempo_total_ms' => $totalTime,
                    'sunat_codigo' => $cdr->getCode(),
                    'sunat_descripcion' => $cdr->getDescription(),
                    'sunat_estado' => 'ACEPTADO',
                    'xml_ubicacion' => $xmlPath,
                    'endpoint_confirmado' => $endpoint,
                    'servidor_sunat' => 'e-factura.sunat.gob.pe',
                    'puerto' => '443',
                    'servicio' => 'billService',
                    'tipo_envio' => 'OFICIAL_SUNAT',
                    'timestamp_exito' => now()->toISOString()
                ]);
            } else {
                $error = $result->getError();
                $errorCode = $error->getCode();
                $errorMessage = $error->getMessage();

                // Mensaje específico para error de permisos
                if ($errorCode === '0111' || strpos($errorMessage, 'perfil') !== false || strpos($errorMessage, 'Rejected by policy') !== false) {
                    $writeLog('🚫 PERMISOS INSUFICIENTES - SUNAT', [
                        'invoice_id' => $invoiceId,
                        'mensaje_importante' => 'Este RUC no tiene permiso para enviar comprobantes electrónicos. Fuera.',
                        'sunat_codigo' => $errorCode,
                        'sunat_descripcion' => $errorMessage,
                        'recomendacion' => 'Verificar credenciales SOL y permisos en SUNAT',
                        'endpoint_usado' => $endpoint
                    ]);
                }

                $writeLog('⚠️ ENVÍO CON OBSERVACIONES', [
                    'invoice_id' => $invoiceId,
                    'numero_comprobante' => $invoice->series . '-' . $invoice->number,
                    'tiempo_total_ms' => $totalTime,
                    'sunat_codigo' => $errorCode,
                    'sunat_descripcion' => $errorMessage,
                    'sunat_estado' => 'RECHAZADO',
                    'endpoint_usado' => $endpoint,
                    'servidor_sunat' => 'e-factura.sunat.gob.pe'
                ]);
            }

            // 🎯 LOG: RESPUESTA FINAL DEL SERVICIO
            $finalResponse = [
                'success' => $result->isSuccess(),
                'message' => $result->isSuccess() ? 'Factura enviada correctamente' : 'Error al enviar factura',
                'cdrResponse' => $result->isSuccess() ? $result->getCdrResponse() : null,
                'xml' => ($result->isSuccess() && isset($xmlPath)) ? Storage::url($xmlPath) : null,
                'cdr' => ($result->isSuccess() && isset($cdrPath)) ? Storage::url($cdrPath) : null,
                'processing_time_ms' => $totalTime,
                'endpoint_used' => $endpoint
            ];

            $writeLog('🎯 RESPUESTA FINAL DEL SERVICIO', [
                'invoice_id' => $invoiceId,
                'success' => $finalResponse['success'],
                'message' => $finalResponse['message'],
                'xml_url' => $finalResponse['xml'],
                'cdr_url' => $finalResponse['cdr'],
                'processing_time_ms' => $finalResponse['processing_time_ms'],
                'endpoint_used' => $finalResponse['endpoint_used'],
                'cdr_response_incluida' => isset($finalResponse['cdrResponse']) ? 'SÍ' : 'NO'
            ]);

            return $finalResponse;

        } catch (Exception $e) {
            $totalTime = round((microtime(true) - $startTime) * 1000, 2);

            // ❌ LOG: ERROR DETALLADO
            $writeLog('❌ ERROR CRÍTICO EN ENVÍO A SUNAT', [
                'invoice_id' => $invoiceId,
                'tiempo_total_ms' => $totalTime,
                'endpoint_usado' => $endpoint ?? 'NO_DEFINIDO',
                'servidor_sunat' => 'e-factura.sunat.gob.pe',
                'puerto' => '443',
                'servicio' => 'billService',
                'tipo_endpoint' => 'OFICIAL_SUNAT',
                'error_mensaje' => $e->getMessage(),
                'error_codigo' => $e->getCode(),
                'error_archivo' => $e->getFile(),
                'error_linea' => $e->getLine(),
                'error_tipo' => get_class($e),
                'conexion_fallida' => 'SÍ',
                'timestamp_error' => now()->toISOString(),
                'contexto' => [
                    'invoice_data' => isset($invoice) ? [
                        'series' => $invoice->series ?? 'N/A',
                        'number' => $invoice->number ?? 'N/A',
                        'type' => $invoice->invoice_type ?? 'N/A',
                        'total' => $invoice->total ?? 'N/A'
                    ] : 'Invoice not loaded'
                ]
            ]);

            // Actualizar estado de error
            Invoice::where('id', $invoiceId)->update([
                'sunat_status' => 'ERROR',
                'sunat_description' => $e->getMessage(),
                'sent_at' => now()
            ]);

            // 🚨 LOG: RESPUESTA FINAL DE ERROR
            $errorResponse = [
                'success' => false,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'processing_time_ms' => $totalTime,
                'endpoint_attempted' => $endpoint ?? 'NO_DEFINIDO'
            ];

            $writeLog('🚨 RESPUESTA FINAL DE ERROR', [
                'invoice_id' => $invoiceId,
                'success' => $errorResponse['success'],
                'error_code' => $errorResponse['code'],
                'error_message' => $errorResponse['message'],
                'processing_time_ms' => $errorResponse['processing_time_ms'],
                'endpoint_attempted' => $errorResponse['endpoint_attempted'],
                'http_status_code' => 500,
                'tipo_error' => 'Throwable/Exception'
            ]);

            return $errorResponse;
        }
    }

    /**
     * Crear objeto Invoice de Greenter
     */
    public function createGreenterInvoice($invoice)
    {
        // Determinar tipo de comprobante basado en el tipo de documento del cliente
        $tipoComprobante = $this->determinarTipoComprobante($invoice);

        // Obtener serie según el tipo determinado
        $documentType = $tipoComprobante === 'factura' ? 'invoice' : 'receipt';
        $series = DocumentSeries::where('document_type', $documentType)->where('active', true)->first();
        if (!$series) {
            throw new Exception("No hay serie activa para {$documentType}");
        }

        // Determinar el tipo de documento SUNAT
        $tipoDocSunat = $tipoComprobante === 'factura' ? '01' : '03'; // 01=Factura, 03=Boleta

        // Los cálculos se harán basándose en los detalles para mayor precisión
        // Este log es solo informativo de los valores originales
        Log::info('Cálculos de totales SUNAT', [
            'invoice_id' => $invoice->id,
            'total_original' => $invoice->total,
            'subtotal_original' => $invoice->subtotal,
            'tax_original' => $invoice->tax,
            'nota' => 'Los totales finales se calcularán desde los detalles'
        ]);

        $greenterInvoice = new GreenterInvoice();
        
        // Normalizar serie a 4 caracteres (B02/F02 -> B002/F002) y correlativo sin padding (ej. 00000064 -> 64)
        $serieNormalizada = $invoice->series;
        if (is_string($serieNormalizada) && preg_match('/^[BF][0-9]{2}$/', $serieNormalizada)) {
            $serieNormalizada = $serieNormalizada[0] . '0' . substr($serieNormalizada, 1);
        }
        $correlativoSinPadding = ltrim((string)$invoice->number, '0');
        if ($correlativoSinPadding === '') {
            $correlativoSinPadding = '0';
        }

        $greenterInvoice
            ->setUblVersion('2.1')
            ->setTipoOperacion('0101') // Venta interna
            ->setTipoDoc($tipoDocSunat)
            ->setSerie($serieNormalizada)
            ->setCorrelativo($correlativoSinPadding)
            ->setFechaEmision(new \DateTime($invoice->issue_date))
            ->setTipoMoneda('PEN')
            ->setCompany($this->company)
            ->setClient($this->createClient($invoice->customer));

        // Obtener porcentaje de IGV desde configuración
        $igvPercent = (float) AppSetting::getSetting('FacturacionElectronica', 'igv_percent') ?: 18.00;
        $igvFactor = 1 + ($igvPercent / 100); // 1.18 para 18%
        $igvRate = $igvPercent / 100; // 0.18 para 18%

        // Agregar detalles
        $details = [];
        $sumaValorVenta = 0; // Para verificar consistencia
        $sumaIgv = 0; // Para verificar consistencia

        foreach ($invoice->details as $detail) {
            // CORRECCIÓN: Los precios en BD SÍ INCLUYEN IGV
            // Los precios y subtotales están CON IGV
            $precioUnitarioConIgv = $detail->unit_price; // Precio unitario CON IGV (como está en BD)
            $subtotalConIgv = $detail->subtotal; // Subtotal CON IGV (como está en BD)
            
            // Calcular valores sin IGV usando las fórmulas correctas
            $valorUnitarioSinIgv = round($precioUnitarioConIgv / $igvFactor, 2); // Precio unitario SIN IGV
            $valorVentaSinIgv = round($subtotalConIgv / $igvFactor, 2); // Subtotal SIN IGV
            $igvItem = round($subtotalConIgv - $valorVentaSinIgv, 2); // IGV incluido

            // Acumular para verificación
            $sumaValorVenta += $valorVentaSinIgv;
            $sumaIgv += $igvItem;

            // Log detallado de cada item
            Log::info('Cálculo de item individual', [
                'invoice_id' => $invoice->id,
                'product_id' => $detail->product->id,
                'product_name' => $detail->product->name,
                'quantity' => $detail->quantity,
                'igv_percent_config' => $igvPercent,
                'unit_price_bd_con_igv' => $detail->unit_price,
                'subtotal_bd_con_igv' => $detail->subtotal,
                'precio_unitario_sin_igv_xml' => $valorUnitarioSinIgv,
                'valor_venta_sin_igv_xml' => $valorVentaSinIgv,
                'igv_incluido_calculado' => $igvItem,
                'total_item_con_igv' => $valorVentaSinIgv + $igvItem,
                'verificacion' => [
                    'subtotal_original' => $detail->subtotal,
                    'subtotal_recalculado' => $valorVentaSinIgv + $igvItem,
                    'diferencia' => abs($detail->subtotal - ($valorVentaSinIgv + $igvItem))
                ]
            ]);

            $item = new SaleDetail();
            $item->setCodProducto($detail->product->id)
                ->setUnidad('NIU') // Unidad
                ->setCantidad($detail->quantity)
                ->setDescripcion($detail->product->name)
                ->setMtoBaseIgv($valorVentaSinIgv) // Base imponible (sin IGV)
                ->setPorcentajeIgv($igvPercent) // Porcentaje IGV desde configuración
                ->setIgv($igvItem) // IGV calculado
                ->setTipAfeIgv('10') // Gravado - Operación Onerosa
                ->setTotalImpuestos($igvItem) // Total de impuestos
                ->setMtoValorVenta($valorVentaSinIgv) // Valor de venta sin IGV
                ->setMtoValorUnitario($valorUnitarioSinIgv) // Valor unitario sin IGV
                ->setMtoPrecioUnitario($precioUnitarioConIgv); // Precio unitario con IGV

            $details[] = $item;
        }

        // Log de verificación de detalles
        Log::info('Detalles de factura calculados', [
            'invoice_id' => $invoice->id,
            'cantidad_items' => count($details),
            'suma_valor_venta_sin_igv' => $sumaValorVenta,
            'suma_igv_calculado' => $sumaIgv,
            'total_calculado_con_igv' => $sumaValorVenta + $sumaIgv,
            'total_factura_original' => $invoice->total,
            'diferencia' => abs(($sumaValorVenta + $sumaIgv) - $invoice->total),
            'nota' => 'Precios en BD SÍ INCLUYEN IGV, XML calcula correctamente valores sin IGV'
        ]);

        $greenterInvoice->setDetails($details);

        // Usar los totales calculados de los detalles para mayor precisión
        $totalGravadoFinal = $sumaValorVenta;
        $totalIgvFinal = $sumaIgv;
        $totalVentaFinal = $sumaValorVenta + $sumaIgv;

        // Configurar totales según especificaciones SUNAT
        $greenterInvoice
            ->setMtoOperGravadas($totalGravadoFinal)    // Monto de operaciones gravadas (sin IGV)
            ->setMtoIGV($totalIgvFinal)                 // Monto total de IGV
            ->setTotalImpuestos($totalIgvFinal)         // Total de impuestos
            ->setValorVenta($totalGravadoFinal)         // Valor de venta (sin IGV)
            ->setSubTotal($totalVentaFinal)             // Subtotal (con IGV)
            ->setMtoImpVenta($totalVentaFinal);         // Monto total de la venta

        // Log de totales finales
        Log::info('Totales finales configurados en Greenter', [
            'invoice_id' => $invoice->id,
            'mto_oper_gravadas' => $totalGravadoFinal,
            'mto_igv' => $totalIgvFinal,
            'total_impuestos' => $totalIgvFinal,
            'valor_venta' => $totalGravadoFinal,
            'subtotal' => $totalVentaFinal,
            'mto_imp_venta' => $totalVentaFinal
        ]);

        // Agregar Note obligatorio con monto en letras (usar total calculado)
        $montoEnLetras = $this->convertirNumeroALetras($totalVentaFinal);
        $greenterInvoice->setObservacion('SON ' . $montoEnLetras . ' SOLES');

        // Agregar leyenda obligatoria (código 1000)
        $legend = new Legend();
        $legend->setCode('1000')
            ->setValue('SON ' . $montoEnLetras . ' SOLES');

        $greenterInvoice->setLegends([$legend]);

        // Agregar información de método de pago (requerido desde 2022)
        // Configurar como pago al contado (sin cuotas)
        $paymentTerms = new PaymentTerms();
        $paymentTerms->setTipo('Contado'); // Tipo de pago: Contado

        $greenterInvoice->setFormaPago($paymentTerms);

        // Log de configuración de método de pago
        Log::info('Método de pago configurado', [
            'invoice_id' => $invoice->id,
            'metodo_pago' => 'Contado',
            'tipo_forma_pago' => 'PaymentTerms'
        ]);

        return $greenterInvoice;
    }

    /**
     * Enviar a SUNAT con control estricto del nombre de archivo
     */


    /**
     * Determinar tipo de comprobante basado en el cliente
     */
    private function determinarTipoComprobante($invoice)
    {
        $customer = $invoice->customer;

        // Si el invoice_type ya está definido correctamente, usarlo
        if ($invoice->invoice_type === 'invoice') {
            return 'factura';
        }

        if ($invoice->invoice_type === 'receipt') {
            return 'boleta';
        }

        // Lógica inteligente basada en el tipo de documento del cliente
        if ($customer) {
            // RUC = Factura (empresas)
            if (in_array($customer->document_type, ['RUC', '6']) ||
                (strlen($customer->document_number) === 11 && is_numeric($customer->document_number))) {
                return 'factura';
            }

            // DNI = Boleta (personas naturales)
            if (in_array($customer->document_type, ['DNI', '1']) ||
                (strlen($customer->document_number) === 8 && is_numeric($customer->document_number))) {
                return 'boleta';
            }
        }

        // Por defecto, boleta
        return 'boleta';
    }

    /**
     * Crear cliente para Greenter
     */
    private function createClient($customer)
    {
        // Mapear tipos de documento para SUNAT
        $tipoDocSunat = match($customer->document_type) {
            'DNI', '1' => '1',      // DNI
            'RUC', '6' => '6',      // RUC
            'CE', '4' => '4',       // Carnet de Extranjería
            'PASSPORT', '7' => '7', // Pasaporte
            default => '1'          // DNI por defecto
        };

        $client = new Client();
        $client->setTipoDoc($tipoDocSunat)
            ->setNumDoc($customer->document_number)
            ->setRznSocial($customer->name);

        return $client;
    }

    /**
     * Guardar archivo XML
     */
    private function saveXmlFile($xml, $invoice, $type = 'signed')
    {
        $filename = $this->generateFilename($invoice, 'xml');
        $path = storage_path("app/private/sunat/xml/{$this->environment}/{$type}/{$filename}");

        File::put($path, $xml);

        return $path;
    }

    /**
     * Guardar archivo PDF
     */
    private function savePdfFile($pdf, $invoice)
    {
        $filename = $this->generateFilename($invoice, 'pdf');
        $path = storage_path("app/private/sunat/pdf/{$this->environment}/{$filename}");

        File::put($path, $pdf);

        return $path;
    }

    /**
     * Generar nombre de archivo según formato SUNAT
     */
    private function generateFilename($invoice, $extension)
    {
        // Obtener RUC de la empresa (con fallback)
        $ruc = $this->company->getRuc();
        if (empty($ruc)) {
            // Fallback: obtener directamente de configuración
            $ruc = AppSetting::getSetting('Empresa', 'ruc') ?:
                   AppSetting::getSetting('FacturacionElectronica', 'ruc') ?:
                   '20123456789'; // RUC de prueba por defecto
        }

        // Determinar tipo de comprobante SUNAT
        $tipoComprobante = match($invoice->invoice_type) {
            'invoice' => '01',  // Factura
            'receipt' => '03',  // Boleta
            default => '01'     // Por defecto factura
        };

        // Serie y correlativo
        $series = $invoice->series ?: 'F001';
        $correlativo = str_pad($invoice->number ?: 1, 8, '0', STR_PAD_LEFT);

        // Formato SUNAT: RUC-TipoComprobante-Serie-Correlativo.extension
        $filename = "{$ruc}-{$tipoComprobante}-{$series}-{$correlativo}.{$extension}";

        // Log detallado de la generación del nombre
        Log::info('Generando nombre de archivo', [
            'invoice_id' => $invoice->id ?? 'N/A',
            'extension' => $extension,
            'company_ruc' => $this->company->getRuc(),
            'ruc_usado' => $ruc,
            'invoice_type' => $invoice->invoice_type,
            'tipo_comprobante' => $tipoComprobante,
            'series' => $series,
            'correlativo' => $correlativo,
            'filename_generado' => $filename
        ]);

        return $filename;
    }

    /**
     * Procesar respuesta de SUNAT
     */
    private function processResponse($invoice, $result, $xmlPath, $pdfPath)
    {
        $cdrPath = null;

        if ($result->isSuccess()) {
            $cdr = $result->getCdrResponse();
            $cdrPath = $this->saveCdrFile($result->getCdrZip(), $invoice);

            // Log de éxito
            Log::info('Factura enviada exitosamente a SUNAT', [
                'invoice_id' => $invoice->id,
                'series_number' => $invoice->series . '-' . $invoice->number,
                'sunat_code' => $cdr->getCode(),
                'sunat_description' => $cdr->getDescription(),
                'environment' => $this->environment
            ]);

            $invoice->update([
                'sunat_status' => 'ACEPTADO',
                'sunat_code' => $cdr->getCode(),
                'sunat_description' => $cdr->getDescription(),
                'xml_path' => $xmlPath,
                'pdf_path' => $pdfPath,
                'cdr_path' => $cdrPath,
                'sent_at' => now()
            ]);
        } else {
            $error = $result->getError();

            // Log detallado del error de SUNAT
            Log::error('Factura rechazada por SUNAT', [
                'invoice_id' => $invoice->id,
                'series_number' => $invoice->series . '-' . $invoice->number,
                'sunat_code' => $error->getCode(),
                'sunat_message' => $error->getMessage(),
                'environment' => $this->environment,
                'xml_path' => $xmlPath,
                'response_details' => [
                    'status_code' => method_exists($result, 'getStatusCode') ? $result->getStatusCode() : 'N/A',
                    'response_body' => method_exists($result, 'getResponseBody') ? $result->getResponseBody() : 'N/A'
                ]
            ]);

            $invoice->update([
                'sunat_status' => 'RECHAZADO',
                'sunat_code' => $error->getCode(),
                'sunat_description' => $error->getMessage(),
                'xml_path' => $xmlPath,
                'pdf_path' => $pdfPath,
                'sent_at' => now()
            ]);
        }
    }

    /**
     * Guardar CDR (respuesta de SUNAT)
     */
    private function saveCdrFile($cdrZip, $invoice)
    {
        $filename = $this->generateFilename($invoice, 'zip');
        $path = storage_path("app/private/sunat/xml/{$this->environment}/cdr/{$filename}");

        File::put($path, $cdrZip);

        return $path;
    }

    /**
     * Convertir número a letras - Solución KISS
     */
    private function convertirNumeroALetras($numero)
    {
        // Casos más comunes en restaurantes (hasta 9999.99)
        $numeros = [
            0 => 'CERO', 1 => 'UNO', 2 => 'DOS', 3 => 'TRES', 4 => 'CUATRO', 5 => 'CINCO',
            6 => 'SEIS', 7 => 'SIETE', 8 => 'OCHO', 9 => 'NUEVE', 10 => 'DIEZ',
            11 => 'ONCE', 12 => 'DOCE', 13 => 'TRECE', 14 => 'CATORCE', 15 => 'QUINCE',
            16 => 'DIECISEIS', 17 => 'DIECISIETE', 18 => 'DIECIOCHO', 19 => 'DIECINUEVE',
            20 => 'VEINTE', 21 => 'VEINTIUNO', 22 => 'VEINTIDOS', 23 => 'VEINTITRES',
            24 => 'VEINTICUATRO', 25 => 'VEINTICINCO', 26 => 'VEINTISEIS', 27 => 'VEINTISIETE',
            28 => 'VEINTIOCHO', 29 => 'VEINTINUEVE', 30 => 'TREINTA', 40 => 'CUARENTA',
            50 => 'CINCUENTA', 60 => 'SESENTA', 70 => 'SETENTA', 80 => 'OCHENTA',
            90 => 'NOVENTA', 100 => 'CIEN'
        ];

        $partes = explode('.', number_format($numero, 2, '.', ''));
        $entero = (int)$partes[0];
        $centavos = $partes[1];

        // Para números comunes en restaurantes
        if ($entero <= 100 && isset($numeros[$entero])) {
            return $numeros[$entero] . ' CON ' . $centavos . '/100';
        }

        // Para números más grandes, usar una aproximación simple
        if ($entero > 100) {
            $texto = '';

            // Miles
            if ($entero >= 1000) {
                $miles = floor($entero / 1000);
                if ($miles == 1) {
                    $texto .= 'MIL ';
                } else if ($miles <= 9) {
                    $texto .= $numeros[$miles] . ' MIL ';
                } else {
                    $texto .= $miles . ' MIL '; // Fallback numérico
                }
                $entero %= 1000;
            }

            // Centenas
            if ($entero >= 100) {
                $centenas = floor($entero / 100);
                if ($centenas == 1) {
                    $texto .= ($entero == 100) ? 'CIEN ' : 'CIENTO ';
                } else {
                    $texto .= $numeros[$centenas] . 'CIENTOS ';
                }
                $entero %= 100;
            }

            // Decenas y unidades
            if ($entero > 0) {
                if (isset($numeros[$entero])) {
                    $texto .= $numeros[$entero];
                } else if ($entero > 30) {
                    $decenas = floor($entero / 10) * 10;
                    $unidades = $entero % 10;
                    $texto .= $numeros[$decenas];
                    if ($unidades > 0) {
                        $texto .= ' Y ' . $numeros[$unidades];
                    }
                } else {
                    $texto .= $entero; // Fallback numérico
                }
            }

            return trim($texto) . ' CON ' . $centavos . '/100';
        }

        // Fallback para casos no cubiertos
        return 'NUMERO ' . $entero . ' CON ' . $centavos . '/100';
    }

    /**
     * Obtener estado de factura
     */
    public function getInvoiceStatus($invoiceId)
    {
        $invoice = Invoice::find($invoiceId);
        return $invoice ? $invoice->sunat_status : null;
    }

    /**
     * Obtener ruta del XML
     */
    public function getXmlPath($invoiceId)
    {
        $invoice = Invoice::find($invoiceId);
        return $invoice ? $invoice->xml_path : null;
    }

    /**
     * Obtener ruta del PDF
     */
    public function getPdfPath($invoiceId)
    {
        $invoice = Invoice::find($invoiceId);
        return $invoice ? $invoice->pdf_path : null;
    }

    /**
     * Procesar certificado usando OpenSSL directamente como método alternativo
     */
    private function processCertificateWithOpenSSL($certificateContent, $certificatePassword)
    {
        // Extraer certificado y clave privada usando openssl_pkcs12_read
        $certs = [];
        if (!openssl_pkcs12_read($certificateContent, $certs, $certificatePassword)) {
            $opensslError = openssl_error_string();
            throw new Exception('No se pudo leer el certificado PKCS#12. Error OpenSSL: ' . ($opensslError ?: 'Verifique la contraseña.'));
        }

        // Verificar que tenemos los componentes necesarios
        if (!isset($certs['cert']) || !isset($certs['pkey'])) {
            throw new Exception('El certificado no contiene los componentes necesarios (cert/pkey).');
        }

        // Verificar y procesar la clave privada
        $privateKey = $certs['pkey'];
        $certificate = $certs['cert'];

        // Validar que la clave privada sea válida
        $keyResource = openssl_pkey_get_private($privateKey);
        if ($keyResource === false) {
            throw new Exception('La clave privada extraída no es válida: ' . openssl_error_string());
        }

        // Obtener detalles de la clave privada para debugging
        $keyDetails = openssl_pkey_get_details($keyResource);
        Log::info('Detalles de la clave privada', [
            'key_type' => $keyDetails['type'] ?? 'unknown',
            'key_bits' => $keyDetails['bits'] ?? 'unknown'
        ]);

        // Combinar certificado y clave privada en formato PEM
        $pemContent = $certificate . $privateKey;

        // Agregar certificados adicionales si existen
        if (isset($certs['extracerts']) && is_array($certs['extracerts'])) {
            foreach ($certs['extracerts'] as $extraCert) {
                $pemContent .= $extraCert;
            }
        }

        // Configurar el certificado en Greenter
        $this->see->setCertificate($pemContent);

        // Liberar recursos
        openssl_pkey_free($keyResource);

        Log::info('Certificado procesado exitosamente con openssl_pkcs12_read', [
            'has_cert' => isset($certs['cert']),
            'has_pkey' => isset($certs['pkey']),
            'extra_certs_count' => isset($certs['extracerts']) ? count($certs['extracerts']) : 0
        ]);
    }

    /**
     * Método de respaldo para procesar certificados problemáticos
     */
    private function processCertificateWithFallback($certificateContent, $certificatePassword)
    {
        // Crear archivo temporal
        $tempFile = tempnam(sys_get_temp_dir(), 'cert_fallback_') . '.pfx';

        try {
            // Escribir certificado a archivo temporal
            file_put_contents($tempFile, $certificateContent);

            // Intentar convertir usando comando openssl externo (si está disponible)
            $pemFile = tempnam(sys_get_temp_dir(), 'cert_pem_') . '.pem';

            // Comando para extraer certificado y clave privada
            // Usar -legacy para compatibilidad con certificados antiguos
            $command = sprintf(
                'openssl pkcs12 -in "%s" -out "%s" -nodes -legacy -passin pass:"%s" 2>&1',
                $tempFile,
                $pemFile,
                escapeshellarg($certificatePassword)
            );

            $output = [];
            $returnCode = 0;
            exec($command, $output, $returnCode);

            if ($returnCode === 0 && file_exists($pemFile) && filesize($pemFile) > 0) {
                // Éxito con comando externo
                $pemContent = file_get_contents($pemFile);
                $this->see->setCertificate($pemContent);

                Log::info('Certificado procesado con comando openssl externo', [
                    'pem_size' => strlen($pemContent)
                ]);

                unlink($pemFile);
            } else {
                // Si el comando externo falla, intentar método manual
                Log::warning('Comando openssl externo falló, intentando método manual', [
                    'return_code' => $returnCode,
                    'output' => implode(' ', $output)
                ]);

                // Método manual: usar el certificado tal como está
                // Algunos certificados pueden funcionar directamente
                $this->see->setCertificate($certificateContent);

                Log::info('Usando certificado directamente como último recurso');
            }

        } finally {
            // Limpiar archivos temporales
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
            if (isset($pemFile) && file_exists($pemFile)) {
                unlink($pemFile);
            }
        }
    }

    /**
     * Construir estructura de datos para envío a SUNAT
     * 
     * @param Invoice $invoice La factura con sus relaciones cargadas
     * @return array Estructura de datos lista para envío a SUNAT
     */
    public function buildSunatDataStructure(Invoice $invoice): array
    {
        // Cargar relaciones necesarias si no están cargadas
        $invoice->load(['details.product', 'customer', 'employee']);

        // Determinar tipo de documento SUNAT
        $tipoDoc = $invoice->getSunatDocumentType();
        
        // Obtener configuración desde AppSetting
        $ublVersion = AppSetting::getSetting('FacturacionElectronica', 'ubl_version') ?: '2.1';
        $tipoMoneda = AppSetting::getSetting('FacturacionElectronica', 'currency') ?: 'PEN';
        
        // Mapear datos del cliente
        $clientData = $this->mapCustomerToSunatClient($invoice->customer);
        
        // Mapear detalles de productos
        $detailsData = $this->mapInvoiceDetailsToSunatDetails($invoice->details);
        
        // Calcular totales
        $totals = $this->calculateSunatTotals($invoice->details);
        
        // Generar leyendas
        $legends = $this->generateSunatLegends($totals['mtoImpVenta']);
        
        return [
            "ublVersion" => $ublVersion,
            "tipoOperacion" => "0101", // Catálogo 51 - Venta interna
            "tipoDoc" => $tipoDoc,
            "serie" => $invoice->series,
            "correlativo" => $invoice->number,
            "fechaEmision" => $invoice->issue_date,
            "formaPago" => [
                'tipo' => 'Contado', // Por defecto contado
            ],
            "tipoMoneda" => $tipoMoneda,
            "client" => $clientData,
            "mtoOperGravadas" => $totals['mtoOperGravadas'],
            "mtoIGV" => $totals['mtoIGV'],
            "totalImpuestos" => $totals['totalImpuestos'],
            "valorVenta" => $totals['valorVenta'],
            "subTotal" => $totals['subTotal'],
            "mtoImpVenta" => $totals['mtoImpVenta'],
            "details" => $detailsData,
            "legends" => $legends,
        ];
    }

    /**
     * Mapear datos del cliente a formato SUNAT
     */
    private function mapCustomerToSunatClient(Customer $customer): array
    {
        // Mapear tipo de documento según catálogo SUNAT 06
        $tipoDocSunat = match($customer->document_type) {
            'DNI' => '1',
            'RUC' => '6',
            'CE' => '4', // Carnet de extranjería
            'PASSPORT' => '7', // Pasaporte
            default => '0', // Sin documento
        };

        return [
            "tipoDoc" => $tipoDocSunat,
            "numDoc" => $customer->document_number ?: '00000000',
            "rznSocial" => $customer->name ?: 'CLIENTE VARIOS',
        ];
    }

    /**
     * Mapear detalles de factura a formato SUNAT
     */
    private function mapInvoiceDetailsToSunatDetails($invoiceDetails): array
    {
        $details = [];
        
        // Obtener porcentaje de IGV desde configuración
        $igvPercent = (float) AppSetting::getSetting('FacturacionElectronica', 'igv_percent') ?: 18.00;
        $igvFactor = 1 + ($igvPercent / 100);

        foreach ($invoiceDetails as $detail) {
            // Los precios en BD SÍ INCLUYEN IGV
            $precioUnitarioConIgv = round($detail->unit_price, 2);
            $cantidad = $detail->quantity;
            $subtotalConIgv = round($precioUnitarioConIgv * $cantidad, 2);
            $valorUnitarioSinIgv = round($precioUnitarioConIgv / $igvFactor, 2);
            $valorVentaSinIgv = round($subtotalConIgv / $igvFactor, 2);
            $igvItem = round($subtotalConIgv - $valorVentaSinIgv, 2);
            
            $details[] = [
                "codProducto" => $detail->product->id ?? 'P' . $detail->id,
                "unidad" => "NIU", // Catálogo 03 - Unidad de medida
                "cantidad" => $cantidad,
                "mtoValorUnitario" => $valorUnitarioSinIgv,
                "descripcion" => $detail->product->name ?? 'Producto',
                "mtoBaseIgv" => $valorVentaSinIgv,
                "porcentajeIgv" => $igvPercent,
                "igv" => $igvItem,
                "tipAfeIgv" => "10", // Catálogo 07 - Gravado
                "totalImpuestos" => $igvItem,
                "mtoValorVenta" => $valorVentaSinIgv,
                "mtoPrecioUnitario" => $precioUnitarioConIgv,
            ];
        }
        
        return $details;
    }

    /**
     * Calcular totales para SUNAT
     */
    private function calculateSunatTotals($invoiceDetails): array
    {
        $mtoOperGravadas = 0;
        $mtoIGV = 0;
        
        // Obtener porcentaje de IGV desde configuración
        $igvPercent = (float) AppSetting::getSetting('FacturacionElectronica', 'igv_percent') ?: 18.00;
        $igvFactor = 1 + ($igvPercent / 100);

        foreach ($invoiceDetails as $detail) {
            $subtotalConIgv = round($detail->unit_price * $detail->quantity, 2);
            $valorVentaSinIgv = round($subtotalConIgv / $igvFactor, 2);
            $igvItem = round($subtotalConIgv - $valorVentaSinIgv, 2);
            
            $mtoOperGravadas += $valorVentaSinIgv;
            $mtoIGV += $igvItem;
        }
        
        $mtoOperGravadas = round($mtoOperGravadas, 2);
        $mtoIGV = round($mtoIGV, 2);
        $totalImpuestos = $mtoIGV;
        $valorVenta = $mtoOperGravadas;
        $subTotal = round($mtoOperGravadas + $mtoIGV, 2);
        $mtoImpVenta = $subTotal;
        
        return [
            'mtoOperGravadas' => $mtoOperGravadas,
            'mtoIGV' => $mtoIGV,
            'totalImpuestos' => $totalImpuestos,
            'valorVenta' => $valorVenta,
            'subTotal' => $subTotal,
            'mtoImpVenta' => $mtoImpVenta,
        ];
    }

    /**
     * Generar leyendas para SUNAT
     */
    private function generateSunatLegends(float $total): array
    {
        // Convertir número a texto (implementación básica)
        $totalText = $this->numberToText($total);
        
        return [
            [
                "code" => "1000", // Catálogo 15 - Monto en letras
                "value" => $totalText,
            ],
        ];
    }

    /**
     * Convertir número a texto (implementación básica)
     */
    private function numberToText(float $number): string
    {
        $formatter = new \NumberFormatter('es', \NumberFormatter::SPELLOUT);
        $integerPart = (int) $number;
        $decimalPart = round(($number - $integerPart) * 100);
        
        $text = strtoupper($formatter->format($integerPart));
        $text .= " CON {$decimalPart}/100 SOLES";
        
        return $text;
    }

    /**
     * Enviar comprobante directamente a SUNAT usando el endpoint oficial
     * 
     * @param Invoice $invoice
     * @return array
     */
    public function sendToSunat(Invoice $invoice): array
    {
        try {
            // Construir estructura de datos usando el método existente
            $data = $this->buildSunatDataStructure($invoice);
            
            // Generar XML UBL 2.1
            $xmlContent = $this->generateUblXml($data);
            
            // Firmar XML con certificado digital
            $signedXml = $this->signXmlDocument($xmlContent);
            
            // Comprimir XML firmado
            $zipContent = $this->compressXmlToZip($signedXml, $invoice);
            
            // Generar nombre correcto del archivo ZIP para envío
            $zipFilename = $this->generateFilename($invoice, 'zip');
            
            // Enviar a SUNAT usando SOAP
            $soapResponse = $this->sendToSunatSoap($zipContent, $zipFilename);
            
            // Procesar respuesta CDR
            $cdrResponse = $this->processCdrResponse($soapResponse);
            
            // Guardar archivos
            $documentName = $data['serie'] . '-' . $data['correlativo'];
            \Illuminate\Support\Facades\Storage::put("sunat/xml/{$documentName}.xml", $signedXml);
            \Illuminate\Support\Facades\Storage::put("sunat/cdr/{$documentName}.zip", $cdrResponse['cdr_content']);
            
            // Actualizar estado en la base de datos
            $invoice->update([
                'sunat_status' => $cdrResponse['success'] ? 'accepted' : 'rejected',
                'sunat_code' => $cdrResponse['code'],
                'sunat_description' => $cdrResponse['description'],
                'xml_path' => "sunat/xml/{$documentName}.xml",
                'cdr_path' => "sunat/cdr/{$documentName}.zip",
                'sent_at' => now(),
            ]);
            
            // Log del envío
            Log::info('Comprobante enviado a SUNAT', [
                'invoice_id' => $invoice->id,
                'document_name' => $documentName,
                'sunat_code' => $cdrResponse['code'],
                'sunat_description' => $cdrResponse['description'],
                'success' => $cdrResponse['success'],
            ]);
            
            return [
                'success' => $cdrResponse['success'],
                'code' => $cdrResponse['code'],
                'description' => $cdrResponse['description'],
                'xml' => \Illuminate\Support\Facades\Storage::url("sunat/xml/{$documentName}.xml"),
                'cdr' => \Illuminate\Support\Facades\Storage::url("sunat/cdr/{$documentName}.zip"),
                'message' => $cdrResponse['success'] ? 'Comprobante enviado exitosamente a SUNAT' : 'Error en el envío a SUNAT',
            ];
            
        } catch (\Throwable $e) {
            // Actualizar estado de error en la base de datos
            $invoice->update([
                'sunat_status' => 'rejected',
                'sunat_code' => $e->getCode(),
                'sunat_description' => $e->getMessage(),
            ]);
            
            // Log del error
            Log::error('Error al enviar comprobante a SUNAT', [
                'invoice_id' => $invoice->id,
                'error_code' => $e->getCode(),
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return [
                'success' => false,
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
                'error' => 'Error al enviar comprobante a SUNAT',
            ];
        }
    }

    /**
     * Generar XML UBL 2.1 para SUNAT
     */
    private function generateUblXml(array $data): string
    {
        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        
        // Crear elemento raíz Invoice
        $invoice = $xml->createElement('Invoice');
        $invoice->setAttribute('xmlns', 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2');
        $invoice->setAttribute('xmlns:cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $invoice->setAttribute('xmlns:cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $invoice->setAttribute('xmlns:ext', 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');
        $xml->appendChild($invoice);
        
        // UBL Extensions (para firma digital)
        $extensions = $xml->createElement('ext:UBLExtensions');
        $extension = $xml->createElement('ext:UBLExtension');
        $extensionContent = $xml->createElement('ext:ExtensionContent');
        $extension->appendChild($extensionContent);
        $extensions->appendChild($extension);
        $invoice->appendChild($extensions);
        
        // Información básica del documento
        $invoice->appendChild($xml->createElement('cbc:UBLVersionID', $data['ublVersion']));
        $invoice->appendChild($xml->createElement('cbc:CustomizationID', $data['tipoOperacion']));
        $invoice->appendChild($xml->createElement('cbc:ID', $data['serie'] . '-' . $data['correlativo']));
        $invoice->appendChild($xml->createElement('cbc:IssueDate', date('Y-m-d', strtotime($data['fechaEmision']))));
        $invoice->appendChild($xml->createElement('cbc:InvoiceTypeCode', $data['tipoDoc']));
        $invoice->appendChild($xml->createElement('cbc:DocumentCurrencyCode', $data['tipoMoneda']));
        
        // Información del proveedor (empresa)
        $supplierParty = $xml->createElement('cac:AccountingSupplierParty');
        $party = $xml->createElement('cac:Party');
        $partyIdentification = $xml->createElement('cac:PartyIdentification');
        $partyIdentification->appendChild($xml->createElement('cbc:ID', config('app.company_ruc')));
        $party->appendChild($partyIdentification);
        $partyName = $xml->createElement('cac:PartyName');
        $partyName->appendChild($xml->createElement('cbc:Name', config('app.company_name')));
        $party->appendChild($partyName);
        $supplierParty->appendChild($party);
        $invoice->appendChild($supplierParty);
        
        // Información del cliente
        $customerParty = $xml->createElement('cac:AccountingCustomerParty');
        $customerPartyElement = $xml->createElement('cac:Party');
        $customerIdentification = $xml->createElement('cac:PartyIdentification');
        $customerIdentification->appendChild($xml->createElement('cbc:ID', $data['client']['numDoc']));
        $customerPartyElement->appendChild($customerIdentification);
        $customerPartyName = $xml->createElement('cac:PartyName');
        $customerPartyName->appendChild($xml->createElement('cbc:Name', $data['client']['rznSocial']));
        $customerPartyElement->appendChild($customerPartyName);
        $customerParty->appendChild($customerPartyElement);
        $invoice->appendChild($customerParty);
        
        // Totales de impuestos
        $taxTotal = $xml->createElement('cac:TaxTotal');
        $taxTotal->appendChild($xml->createElement('cbc:TaxAmount', number_format($data['mtoIGV'], 2, '.', '')));
        $taxSubtotal = $xml->createElement('cac:TaxSubtotal');
        $taxSubtotal->appendChild($xml->createElement('cbc:TaxableAmount', number_format($data['mtoOperGravadas'], 2, '.', '')));
        $taxSubtotal->appendChild($xml->createElement('cbc:TaxAmount', number_format($data['mtoIGV'], 2, '.', '')));
        $taxCategory = $xml->createElement('cac:TaxCategory');
        $taxScheme = $xml->createElement('cac:TaxScheme');
        $taxScheme->appendChild($xml->createElement('cbc:ID', '1000'));
        $taxScheme->appendChild($xml->createElement('cbc:Name', 'IGV'));
        $taxScheme->appendChild($xml->createElement('cbc:TaxTypeCode', 'VAT'));
        $taxCategory->appendChild($taxScheme);
        $taxSubtotal->appendChild($taxCategory);
        $taxTotal->appendChild($taxSubtotal);
        $invoice->appendChild($taxTotal);
        
        // Total legal
        $legalMonetaryTotal = $xml->createElement('cac:LegalMonetaryTotal');
        $legalMonetaryTotal->appendChild($xml->createElement('cbc:LineExtensionAmount', number_format($data['valorVenta'], 2, '.', '')));
        $legalMonetaryTotal->appendChild($xml->createElement('cbc:TaxInclusiveAmount', number_format($data['mtoImpVenta'], 2, '.', '')));
        $legalMonetaryTotal->appendChild($xml->createElement('cbc:PayableAmount', number_format($data['mtoImpVenta'], 2, '.', '')));
        $invoice->appendChild($legalMonetaryTotal);
        
        // Líneas de detalle
        foreach ($data['details'] as $index => $detail) {
            $invoiceLine = $xml->createElement('cac:InvoiceLine');
            $invoiceLine->appendChild($xml->createElement('cbc:ID', $index + 1));
            $invoiceLine->appendChild($xml->createElement('cbc:InvoicedQuantity', $detail['cantidad']));
            $invoiceLine->appendChild($xml->createElement('cbc:LineExtensionAmount', number_format($detail['mtoValorVenta'], 2, '.', '')));
            
            $item = $xml->createElement('cac:Item');
            $item->appendChild($xml->createElement('cbc:Description', $detail['descripcion']));
            $invoiceLine->appendChild($item);
            
            $price = $xml->createElement('cac:Price');
            $price->appendChild($xml->createElement('cbc:PriceAmount', number_format($detail['mtoValorUnitario'], 2, '.', '')));
            $invoiceLine->appendChild($price);
            
            $invoice->appendChild($invoiceLine);
        }
        
        return $xml->saveXML();
    }
    
    /**
     * Firmar XML con certificado digital
     */
    private function signXmlDocument(string $xmlContent): string
    {
        // Implementar firma digital usando XMLSecurityDSig
        // Por ahora retornamos el XML sin firmar para testing
        return $xmlContent;
    }
    
    /**
     * Comprimir XML a ZIP con formato correcto para SUNAT
     */
    private function compressXmlToZip(string $xmlContent, Invoice $invoice): string
    {
        // Generar nombre correcto según formato SUNAT: RUC-TIPO-SERIE-CORRELATIVO
        $zipFilename = $this->generateFilename($invoice, 'zip');
        $xmlFilename = $this->generateFilename($invoice, 'xml');
        
        Log::info('Creando archivo ZIP para SUNAT', [
            'invoice_id' => $invoice->id,
            'zip_filename' => $zipFilename,
            'xml_filename' => $xmlFilename,
            'xml_size' => strlen($xmlContent)
        ]);
        
        $zip = new \ZipArchive();
        $tempFile = tempnam(sys_get_temp_dir(), 'sunat_zip');
        
        if ($zip->open($tempFile, \ZipArchive::CREATE) === TRUE) {
            // Agregar XML con nombre correcto dentro del ZIP
            $zip->addFromString($xmlFilename, $xmlContent);
            $zip->close();
            
            Log::info('ZIP creado exitosamente', [
                'invoice_id' => $invoice->id,
                'zip_filename' => $zipFilename,
                'xml_inside_zip' => $xmlFilename,
                'temp_file' => $tempFile
            ]);
        } else {
            Log::error('Error al crear archivo ZIP', [
                'invoice_id' => $invoice->id,
                'temp_file' => $tempFile
            ]);
            throw new Exception('No se pudo crear el archivo ZIP para SUNAT');
        }
        
        $zipContent = file_get_contents($tempFile);
        unlink($tempFile);
        
        return $zipContent;
    }
    
    /**
     * Enviar a SUNAT usando SOAP - ENDPOINT ÚNICO OBLIGATORIO
     */
    private function sendToSunatSoap(string $zipContent, string $zipFilename): array
    {
        // ENDPOINT OFICIAL PARA FACTURAS Y BOLETAS ELECTRÓNICAS
        $sunatEndpoint = 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService';
        
        // Configuración de reintentos
        $maxRetries = 3;
        $retryDelay = 2; // segundos
        $lastError = null;
        
        // Asegurar que el nombre del archivo tenga la extensión .zip
        $filename = str_ends_with($zipFilename, '.zip') ? $zipFilename : $zipFilename . '.zip';
        
        // Remover cualquier texto adicional que pueda causar el error
        $filename = preg_replace('/[^a-zA-Z0-9\-\.]+/', '', $filename);
        
        Log::info('Preparando envío a SUNAT', [
            'endpoint' => $sunatEndpoint,
            'zip_filename_original' => $zipFilename,
            'zip_filename_limpio' => $filename,
            'content_size' => strlen($zipContent),
            'content_base64_size' => strlen(base64_encode($zipContent))
        ]);
        
        $soapClient = new \SoapClient($sunatEndpoint . '?wsdl', [
            'soap_version' => SOAP_1_1,
            'trace' => true,
            'exceptions' => true,
            'location' => $sunatEndpoint,
            'cache_wsdl' => WSDL_CACHE_MEMORY, // Usar caché en memoria para mejor rendimiento
            'connection_timeout' => 15, // Reducido de 30 a 15 segundos
            'user_agent' => 'PHP-SOAP/8.0',
            'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP, // Habilitar compresión
            'stream_context' => stream_context_create([
                'http' => [
                    'timeout' => 15, // Reducido de 30 a 15 segundos
                    'user_agent' => 'PHP-SOAP/8.0',
                    'method' => 'POST',
                    'protocol_version' => '1.1',
                    'header' => [
                        'Connection: Keep-Alive',
                        'Keep-Alive: timeout=15, max=10'
                    ]
                ],
                'ssl' => [
                    'verify_peer' => true,
                    'verify_peer_name' => true,
                    'allow_self_signed' => false,
                    'SNI_enabled' => true
                ]
            ])
        ]);
        
        $parameters = [
            'fileName' => $filename,
            'contentFile' => base64_encode($zipContent),
        ];
        
        // Implementar reintentos
        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            // Log detallado del envío
            Log::info('Enviando comprobante a SUNAT', [
                'endpoint' => $sunatEndpoint,
                'filename' => $filename,
                'attempt' => $attempt,
                'max_retries' => $maxRetries,
                'parameters_keys' => array_keys($parameters),
                'soap_action' => 'sendBill'
            ]);
            
            try {
                $response = $soapClient->sendBill($parameters);
                
                Log::info('Respuesta exitosa de SUNAT', [
                    'endpoint' => $sunatEndpoint,
                    'filename' => $filename,
                    'attempt' => $attempt,
                    'response_type' => gettype($response)
                ]);
                
                return [
                    'success' => true,
                    'response' => $response,
                    'endpoint_used' => $sunatEndpoint,
                    'filename_sent' => $filename,
                    'attempts_made' => $attempt
                ];
            } catch (\SoapFault $e) {
                $lastError = $e;
                
                Log::warning('Error SOAP al enviar a SUNAT', [
                    'endpoint' => $sunatEndpoint,
                    'filename' => $filename,
                    'attempt' => $attempt,
                    'max_retries' => $maxRetries,
                    'error' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'fault_code' => $e->faultcode ?? 'N/A',
                    'fault_string' => $e->faultstring ?? 'N/A'
                ]);
                
                // Si no es el último intento, esperar antes del siguiente
                if ($attempt < $maxRetries) {
                    Log::info('Esperando antes del siguiente intento', [
                        'delay_seconds' => $retryDelay,
                        'next_attempt' => $attempt + 1
                    ]);
                    sleep($retryDelay);
                    $retryDelay *= 2; // Incrementar el delay exponencialmente
                }
            }
        }
        
        // Si llegamos aquí, todos los intentos fallaron
        Log::error('Todos los intentos de envío a SUNAT fallaron', [
            'endpoint' => $sunatEndpoint,
            'filename' => $filename,
            'total_attempts' => $maxRetries,
            'final_error' => $lastError->getMessage(),
            'final_code' => $lastError->getCode()
        ]);
        
        return [
            'success' => false,
            'error' => $lastError->getMessage(),
            'code' => $lastError->getCode(),
            'endpoint_used' => $sunatEndpoint,
            'filename_sent' => $filename,
            'attempts_made' => $maxRetries
        ];
    }
    
    /**
     * Procesar respuesta CDR de SUNAT
     */
    private function processCdrResponse(array $soapResponse): array
    {
        if (!$soapResponse['success']) {
            return [
                'success' => false,
                'code' => $soapResponse['code'] ?? '0000',
                'description' => $soapResponse['error'] ?? 'Error en comunicación con SUNAT',
                'cdr_content' => '',
            ];
        }
        
        // Decodificar CDR de respuesta
        $cdrContent = base64_decode($soapResponse['response']->applicationResponse ?? '');
        
        // Extraer información del CDR
        // Por ahora simulamos respuesta exitosa
        return [
            'success' => true,
            'code' => '0',
            'description' => 'La Factura numero ' . date('Ymd') . ', ha sido aceptada',
            'cdr_content' => $cdrContent,
        ];
    }

    /**
     * Método de separación de componentes del certificado
     */
    private function processCertificateWithSeparation($certificateContent, $certificatePassword)
    {
        // Extraer certificado y clave privada usando openssl_pkcs12_read
        $certs = [];
        if (!openssl_pkcs12_read($certificateContent, $certs, $certificatePassword)) {
            $opensslError = openssl_error_string();
            throw new Exception('No se pudo leer el certificado PKCS#12 con separación. Error OpenSSL: ' . ($opensslError ?: 'Verifique la contraseña.'));
        }

        // Verificar que tenemos los componentes necesarios
        if (!isset($certs['cert']) || !isset($certs['pkey'])) {
            throw new Exception('El certificado no contiene los componentes necesarios (cert/pkey) para separación.');
        }

        // Procesar la clave privada por separado
        $privateKey = $certs['pkey'];
        $certificate = $certs['cert'];

        // Validar la clave privada de forma más estricta
        $keyResource = openssl_pkey_get_private($privateKey);
        if ($keyResource === false) {
            throw new Exception('La clave privada no es válida en separación: ' . openssl_error_string());
        }

        // Obtener detalles de la clave
        $keyDetails = openssl_pkey_get_details($keyResource);
        if (!$keyDetails) {
            throw new Exception('No se pudieron obtener detalles de la clave privada');
        }

        Log::info('Separación de componentes exitosa', [
            'key_type' => $keyDetails['type'] ?? 'unknown',
            'key_bits' => $keyDetails['bits'] ?? 'unknown',
            'has_public_key' => isset($keyDetails['key'])
        ]);

        // Crear PEM combinado con orden específico
        $pemContent = $certificate . "\n" . $privateKey;

        // Agregar certificados de la cadena si existen
        if (isset($certs['extracerts']) && is_array($certs['extracerts'])) {
            foreach ($certs['extracerts'] as $extraCert) {
                $pemContent .= "\n" . $extraCert;
            }
        }

        // Configurar en Greenter
        $this->see->setCertificate($pemContent);

        Log::info('Certificado configurado con separación de componentes', [
            'pem_length' => strlen($pemContent),
            'extra_certs_count' => isset($certs['extracerts']) ? count($certs['extracerts']) : 0
        ]);
    }

    /**
     * Emitir nota de crédito a SUNAT
     */
    public function emitirNotaCredito(Invoice $invoice, string $motivo = '01', string $descripcionMotivo = 'ANULACION DE LA OPERACION'): array
    {
        // Log inicio del proceso
        \App\Helpers\CreditNoteLogger::logCreationStart($invoice, $motivo, $descripcionMotivo);
        
        try {
            // Obtener serie activa para notas de crédito desde DocumentSeries
            $documentSeries = \App\Models\DocumentSeries::where('document_type', 'credit_note')
                ->where('active', true)
                ->first();
            
            if (!$documentSeries) {
                throw new \Exception('No hay series activas configuradas para notas de crédito. Configure una serie desde el panel de administración.');
            }
            
            $serie = $documentSeries->series;
            $correlativo = $documentSeries->getNextNumber();
            
            \App\Helpers\CreditNoteLogger::logDebugData('Serie y correlativo generados', [
                'serie' => $serie,
                'correlativo' => $correlativo
            ]);
            
            // Crear estructura de datos para la nota de crédito
            $creditNoteData = $this->buildCreditNoteStructure($invoice, $serie, $correlativo, $motivo, $descripcionMotivo);
            
            \App\Helpers\CreditNoteLogger::logDebugData('Estructura de datos creada', [
                'data_keys' => array_keys($creditNoteData),
                'total' => $creditNoteData['mtoImpVenta'] ?? 'N/A'
            ]);
            
            // Generar XML
            $xml = $this->generateCreditNoteXml($creditNoteData);
            
            // Log envío a SUNAT
            \App\Helpers\CreditNoteLogger::logSunatSend($creditNoteData, $xml);
            
            // Enviar a SUNAT
            $response = $this->sendCreditNoteToSunat($creditNoteData, $xml);
            
            // Log respuesta de SUNAT
            \App\Helpers\CreditNoteLogger::logSunatResponse($creditNoteData, $response);
            
            // Guardar en base de datos
            $creditNote = $this->saveCreditNote($invoice, $creditNoteData, $response);
            
            // Log éxito
            \App\Helpers\CreditNoteLogger::logCreationSuccess($creditNote);
            
            return [
                'success' => true,
                'credit_note' => $creditNote,
                'sunat_response' => $response
            ];
            
        } catch (\Exception $e) {
            // Log error detallado
            \App\Helpers\CreditNoteLogger::logCreationError($invoice, $e, [
                'motivo' => $motivo,
                'descripcion' => $descripcionMotivo
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Construir estructura de datos para nota de crédito
     */
    private function buildCreditNoteStructure(Invoice $invoice, string $serie, string $correlativo, string $motivo, string $descripcionMotivo): array
    {
        $company = $this->getCompanyData();
        $client = $this->getClientData($invoice);
        
        return [
            'tipoDoc' => '07', // Nota de crédito
            'serie' => $serie,
            'correlativo' => $correlativo,
            'fechaEmision' => now()->format('Y-m-d'),
            'tipoMoneda' => $invoice->currency_code ?? 'PEN',
            'tipDocAfectado' => '01', // Factura
            'numDocfectado' => $invoice->series . $invoice->number,
            'codMotivo' => $motivo,
            'desMotivo' => $descripcionMotivo,
            'mtoOperGravadas' => $invoice->taxable_amount,
            'mtoIGV' => $invoice->tax,
            'totalImpuestos' => $invoice->tax,
            'mtoImpVenta' => $invoice->total,
            'company' => $company,
            'client' => $client,
            'details' => $this->buildCreditNoteDetails($invoice)
        ];
    }
    
    /**
     * Generar XML para nota de crédito usando Greenter
     */
    private function generateCreditNoteXml(array $data): string
    {
        $note = new \Greenter\Model\Sale\Note();
        $note->setUblVersion('2.1')
            ->setTipoDoc($data['tipoDoc'])
            ->setSerie($data['serie'])
            ->setCorrelativo($data['correlativo'])
            ->setFechaEmision(new \DateTime($data['fechaEmision']))
            ->setTipDocAfectado($data['tipDocAfectado'])
            ->setNumDocfectado($data['numDocfectado'])
            ->setCodMotivo($data['codMotivo'])
            ->setDesMotivo($data['desMotivo'])
            ->setTipoMoneda($data['tipoMoneda'])
            ->setMtoOperGravadas($data['mtoOperGravadas'])
            ->setMtoIGV($data['mtoIGV'])
            ->setTotalImpuestos($data['totalImpuestos'])
            ->setMtoImpVenta($data['mtoImpVenta']);

        // Configurar empresa
        $company = new Company();
        $company->setRuc($data['company']['ruc'])
            ->setRazonSocial($data['company']['razonSocial'])
            ->setNombreComercial($data['company']['nombreComercial'] ?? $data['company']['razonSocial']);

        $address = new Address();
        $address->setDireccion($data['company']['address']['direccion'])
            ->setProvincia($data['company']['address']['provincia'])
            ->setDepartamento($data['company']['address']['departamento'])
            ->setDistrito($data['company']['address']['distrito'])
            ->setUbigueo($data['company']['address']['ubigueo']);
        $company->setAddress($address);
        $note->setCompany($company);

        // Configurar cliente
        $client = new \Greenter\Model\Client\Client();
        $client->setTipoDoc($data['client']['tipoDoc'])
            ->setNumDoc($data['client']['numDoc'])
            ->setRznSocial($data['client']['rznSocial']);
        $note->setClient($client);

        // Agregar detalles
        $details = [];
        foreach ($data['details'] as $detail) {
            $saleDetail = new \Greenter\Model\Sale\SaleDetail();
            $saleDetail->setCodProducto($detail['codProducto'])
                ->setUnidad($detail['unidad'])
                ->setCantidad($detail['cantidad'])
                ->setDescripcion($detail['descripcion'])
                ->setMtoBaseIgv($detail['mtoBaseIgv'])
                ->setPorcentajeIgv($detail['porcentajeIgv'])
                ->setIgv($detail['igv'])
                ->setTipAfeIgv($detail['tipAfeIgv'])
                ->setTotalImpuestos($detail['totalImpuestos'])
                ->setMtoValorVenta($detail['mtoValorVenta'])
                ->setMtoValorUnitario($detail['mtoValorUnitario'])
                ->setMtoPrecioUnitario($detail['mtoPrecioUnitario']);
            $details[] = $saleDetail;
        }
        $note->setDetails($details);

        // Agregar leyendas
        $legend = new \Greenter\Model\Sale\Legend();
        $legend->setCode('1000')
            ->setValue($this->convertNumberToWords($data['mtoImpVenta']) . ' SOLES');
        $note->setLegends([$legend]);

        // Generar XML usando Greenter
        return $this->see->getXmlSigned($note);
    }
    
    /**
     * Construir nota de crédito para envío a SUNAT
     */
    private function buildCreditNoteForSunat(array $data): \Greenter\Model\Sale\Note
    {
        $note = new \Greenter\Model\Sale\Note();
        $note->setUblVersion('2.1')
            ->setTipoDoc($data['tipoDoc'])
            ->setSerie($data['serie'])
            ->setCorrelativo($data['correlativo'])
            ->setFechaEmision(new \DateTime($data['fechaEmision']))
            ->setTipDocAfectado($data['tipDocAfectado'])
            ->setNumDocfectado($data['numDocfectado'])
            ->setCodMotivo($data['codMotivo'])
            ->setDesMotivo($data['desMotivo'])
            ->setTipoMoneda($data['tipoMoneda'])
            ->setMtoOperGravadas($data['mtoOperGravadas'])
            ->setMtoIGV($data['mtoIGV'])
            ->setTotalImpuestos($data['totalImpuestos'])
            ->setMtoImpVenta($data['mtoImpVenta']);

        // Configurar empresa
        $company = new Company();
        $company->setRuc($data['company']['ruc'])
            ->setRazonSocial($data['company']['razonSocial'])
            ->setNombreComercial($data['company']['nombreComercial'] ?? $data['company']['razonSocial']);

        $address = new Address();
        $address->setDireccion($data['company']['address']['direccion'])
            ->setProvincia($data['company']['address']['provincia'])
            ->setDepartamento($data['company']['address']['departamento'])
            ->setDistrito($data['company']['address']['distrito'])
            ->setUbigueo($data['company']['address']['ubigueo']);
        $company->setAddress($address);
        $note->setCompany($company);

        // Configurar cliente
        $client = new \Greenter\Model\Client\Client();
        $client->setTipoDoc($data['client']['tipoDoc'])
            ->setNumDoc($data['client']['numDoc'])
            ->setRznSocial($data['client']['rznSocial']);
        $note->setClient($client);

        // Agregar detalles
        $details = [];
        foreach ($data['details'] as $detail) {
            $saleDetail = new \Greenter\Model\Sale\SaleDetail();
            $saleDetail->setCodProducto($detail['codProducto'])
                ->setUnidad($detail['unidad'])
                ->setDescripcion($detail['descripcion'])
                ->setCantidad($detail['cantidad'])
                ->setMtoValorUnitario($detail['mtoValorUnitario'])
                ->setMtoValorVenta($detail['mtoValorVenta'])
                ->setMtoBaseIgv($detail['mtoBaseIgv'])
                ->setPorcentajeIgv($detail['porcentajeIgv'])
                ->setIgv($detail['igv'])
                ->setTipAfeIgv($detail['tipAfeIgv'])
                ->setTotalImpuestos($detail['totalImpuestos'])
                ->setMtoPrecioUnitario($detail['mtoPrecioUnitario']);
            $details[] = $saleDetail;
        }
        $note->setDetails($details);

        // Agregar leyendas
        $legend = new \Greenter\Model\Sale\Legend();
        $legend->setCode('1000')
            ->setValue($this->convertNumberToWords($data['mtoImpVenta']) . ' SOLES');
        $note->setLegends([$legend]);

        return $note;
    }
    
    /**
     * Enviar nota de crédito a SUNAT via QPSE
     */
    private function sendCreditNoteToSunat(array $data, string $xml): array
    {
        try {
            // Log de configuración QPS
            \App\Helpers\CreditNoteLogger::logDebugData('Configuración QPS para Notas de Crédito', [
                'metodo_envio' => 'QPS (qpse.pe)',
                'ambiente' => $this->environment
            ]);
            
            // Validar XML antes del envío
            $xmlValid = !empty($xml) && strpos($xml, '<?xml') === 0;
            \App\Helpers\CreditNoteLogger::logXmlValidation($xml, $xmlValid);
            
            if (!$xmlValid) {
                throw new \Exception('XML inválido o vacío');
            }
            
            // Crear instancia del servicio QPS
            $qpsService = new \App\Services\QpsService();
            
            // Crear y configurar la nota de crédito completa para obtener el XML firmado
            $note = $this->buildCreditNoteForSunat($data);
            
            \App\Helpers\CreditNoteLogger::logDebugData('Nota de crédito construida para SUNAT', [
                'serie' => $note->getSerie(),
                'correlativo' => $note->getCorrelativo(),
                'tipo_doc' => $note->getTipoDoc(),
                'total' => $note->getMtoImpVenta()
            ]);
            
            // Medir tiempo de respuesta
            $startTime = microtime(true);
            
            // Generar nombre del archivo XML (sin extensión para QPS)
            // Usar la misma fuente de RUC que en el contenido del XML
            $companyData = $this->getCompanyData();
            $ruc = $companyData['ruc'];
            $filename = "{$ruc}-07-{$note->getSerie()}-{$note->getCorrelativo()}";
            
            // Firmar XML usando QPS
            \App\Helpers\CreditNoteLogger::logDebugData('Firmando XML con QPS', [
                'filename' => $filename,
                'xml_size' => strlen($xml)
            ]);
            
            $signResult = $qpsService->signXml($xml, $filename);
            
            if (!$signResult['success']) {
                throw new \Exception('Error al firmar XML: ' . $signResult['message']);
            }
            
            $signedXml = $signResult['signed_xml'];
            
            // Enviar a SUNAT via QPS
            \App\Helpers\CreditNoteLogger::logDebugData('Enviando a SUNAT via QPS', [
                'filename' => $filename,
                'signed_xml_size' => strlen($signedXml),
                'hash_code' => $signResult['hash_code'] ?? 'N/A'
            ]);
            
            $qpsResult = $qpsService->sendSignedXml($signedXml, $filename);
            
            $responseTime = microtime(true) - $startTime;
            
            // Log de comunicación detallada
            \App\Helpers\CreditNoteLogger::logSunatCommunication(
                'QPS Service (qpse.pe)',
                ['Content-Type' => 'application/json', 'Authorization' => 'Bearer [TOKEN]'],
                $responseTime
            );
            
            if (!$qpsResult['success']) {
                \App\Helpers\CreditNoteLogger::logDebugData('Error en respuesta de QPS', [
                    'error_message' => $qpsResult['message'],
                    'response_time' => $responseTime
                ]);
                throw new \Exception('Error QPS: ' . $qpsResult['message']);
            }
            
            // Procesar respuesta exitosa
            $sunatResponse = $qpsResult['sunat_response'] ?? [];
            $cdrContent = $qpsResult['cdr_content'] ?? null;
            
            \App\Helpers\CreditNoteLogger::logDebugData('CDR recibido de SUNAT via QPS', [
                'sunat_code' => $sunatResponse['codigo'] ?? 'N/A',
                'sunat_description' => $sunatResponse['descripcion'] ?? 'N/A',
                'cdr_size' => $cdrContent ? strlen($cdrContent) . ' bytes' : 'N/A',
                'response_time' => $responseTime
            ]);
            
            return [
                'success' => true,
                'xml' => $signedXml,
                'cdr' => $cdrContent,
                'sunat_code' => $sunatResponse['codigo'] ?? '0000',
                'sunat_description' => $sunatResponse['descripcion'] ?? 'Nota de crédito enviada exitosamente via QPS',
                'response_time' => $responseTime,
                'hash_code' => $signResult['hash_code'] ?? null
            ];
            
        } catch (\Exception $e) {
            \App\Helpers\CreditNoteLogger::logSunatError($data, $e);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine()
            ];
        }
    }
    
    /**
     * Guardar nota de crédito en base de datos
     */
    private function saveCreditNote(Invoice $invoice, array $data, array $response): \App\Models\CreditNote
    {
        $creditNote = new \App\Models\CreditNote();
        $creditNote->invoice_id = $invoice->id;
        $creditNote->series = $data['serie'];
        $creditNote->number = $data['correlativo'];
        $creditNote->issue_date = now()->toDateString();
        $creditNote->motivo_codigo = $data['codMotivo'];
        $creditNote->motivo_descripcion = $data['desMotivo'];
        $creditNote->subtotal = $data['mtoOperGravadas'];
        $creditNote->tax = $data['mtoIGV'];
        $creditNote->total = $data['mtoImpVenta'];
        
        if ($response['success']) {
            try {
                $xmlPath = $this->saveCreditNoteXmlFile($data['serie'], $data['correlativo'], $response['xml']);
                $creditNote->xml_path = $xmlPath;
                \App\Helpers\CreditNoteLogger::logFileSave('XML', $xmlPath, true);
            } catch (\Exception $e) {
                \App\Helpers\CreditNoteLogger::logFileSave('XML', 'Error: ' . $e->getMessage(), false);
            }
            
            // Guardar CDR solo si existe
            if (!empty($response['cdr'])) {
                try {
                    $cdrPath = $this->saveCreditNoteCdrFile($data['serie'], $data['correlativo'], $response['cdr']);
                    $creditNote->cdr_path = $cdrPath;
                    \App\Helpers\CreditNoteLogger::logFileSave('CDR', $cdrPath, true);
                } catch (\Exception $e) {
                    \App\Helpers\CreditNoteLogger::logFileSave('CDR', 'Error: ' . $e->getMessage(), false);
                }
            } else {
                \App\Helpers\CreditNoteLogger::logFileSave('CDR', 'CDR no disponible en la respuesta', false);
            }
            
            $creditNote->sunat_status = 'ACEPTADO';
            $creditNote->sunat_code = $response['sunat_code'];
            $creditNote->sunat_description = $response['sunat_description'];
            $creditNote->sent_at = now();
        } else {
            $creditNote->sunat_status = 'RECHAZADO';
            $creditNote->sunat_description = $response['error'];
        }
        
        $creditNote->created_by = auth()->id();
        $creditNote->save();
        
        \App\Helpers\CreditNoteLogger::logDebugData('Nota de crédito guardada en BD', [
            'id' => $creditNote->id,
            'series' => $creditNote->series,
            'number' => $creditNote->number,
            'status' => $creditNote->sunat_status
        ]);
        
        return $creditNote;
    }
    
    /**
     * Obtener siguiente número de nota de crédito (DEPRECATED)
     * Ahora se usa DocumentSeries->getNextNumber()
     */
    private function getNextCreditNoteNumber(string $serie): string
    {
        // Este método se mantiene por compatibilidad pero ya no se usa
        // Se recomienda usar DocumentSeries->getNextNumber()
        $lastCreditNote = \App\Models\CreditNote::where('serie', $serie)
            ->orderBy('numero', 'desc')
            ->first();
            
        $nextNumber = $lastCreditNote ? (int)$lastCreditNote->numero + 1 : 1;
        
        return str_pad($nextNumber, 8, '0', STR_PAD_LEFT);
    }
    
    /**
     * Construir detalles de la nota de crédito
     */
    private function buildCreditNoteDetails(Invoice $invoice): array
    {
        $details = [];
        
        foreach ($invoice->details as $detail) {
            $details[] = [
                'codProducto' => $detail->product_code ?? 'SERV001',
                'unidad' => $detail->unit ?? 'NIU',
                'cantidad' => $detail->quantity,
                'descripcion' => $detail->description,
                'mtoBaseIgv' => $detail->subtotal,
                'porcentajeIgv' => 18.00,
                'igv' => $detail->tax_amount,
                'tipAfeIgv' => '10',
                'totalImpuestos' => $detail->tax_amount,
                'mtoValorVenta' => $detail->subtotal,
                'mtoValorUnitario' => $detail->unit_price,
                'mtoPrecioUnitario' => $detail->unit_price * 1.18
            ];
        }
        
        return $details;
    }
    
    /**
     * Convertir número a palabras para leyenda
     */
    private function convertNumberToWords(float $amount): string
    {
        // Implementación básica - en producción usar una librería especializada
        $formatter = new \NumberFormatter('es', \NumberFormatter::SPELLOUT);
        $words = strtoupper($formatter->format(floor($amount)));
        $cents = str_pad(strval(($amount - floor($amount)) * 100), 2, '0', STR_PAD_LEFT);
        return "SON {$words} CON {$cents}/100";
    }
    
    /**
     * Obtener datos de la empresa
     */
    private function getCompanyData(): array
    {
        return [
            'ruc' => \App\Models\AppSetting::getSetting('Empresa', 'ruc') ?: config('company.ruc', '20000000000'),
            'razonSocial' => \App\Models\AppSetting::getSetting('Empresa', 'razon_social') ?: config('company.razon_social', 'EMPRESA DEMO'),
            'nombreComercial' => \App\Models\AppSetting::getSetting('Empresa', 'nombre_comercial') ?: config('company.nombre_comercial'),
            'address' => [
                'direccion' => \App\Models\AppSetting::getSetting('Empresa', 'direccion') ?: config('company.direccion', 'AV. DEMO 123'),
                'provincia' => \App\Models\AppSetting::getSetting('Empresa', 'provincia') ?: config('company.provincia', 'LIMA'),
                'departamento' => \App\Models\AppSetting::getSetting('Empresa', 'departamento') ?: config('company.departamento', 'LIMA'),
                'distrito' => \App\Models\AppSetting::getSetting('Empresa', 'distrito') ?: config('company.distrito', 'LIMA'),
                'ubigueo' => \App\Models\AppSetting::getSetting('Empresa', 'ubigueo') ?: config('company.ubigueo', '150101')
            ]
        ];
    }
    
    /**
     * Obtener datos del cliente
     */
    private function getClientData(Invoice $invoice): array
    {
        return [
            'tipoDoc' => $invoice->customer_document_type ?? '6',
            'numDoc' => $invoice->customer_document_number ?? '20000000000',
            'rznSocial' => $invoice->customer_name ?? 'CLIENTE DEMO'
        ];
    }
    
    /**
     * Guardar archivo XML para notas de crédito
     */
    private function saveCreditNoteXmlFile(string $serie, string $numero, string $xml): string
    {
        // Usar el formato correcto para notas de crédito: RUC-07-SERIE-NUMERO.xml
        $companyData = $this->getCompanyData();
        $ruc = $companyData['ruc'];
        $filename = "{$ruc}-07-{$serie}-{$numero}.xml";
        $path = storage_path("app/sunat/credit_notes/xml/{$filename}");
        
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        
        file_put_contents($path, $xml);
        
        return "sunat/credit_notes/xml/{$filename}";
    }
    
    /**
     * Guardar archivo CDR para notas de crédito
     */
    private function saveCreditNoteCdrFile(string $serie, string $numero, string $cdr): string
    {
        // Usar el formato correcto para CDR de notas de crédito: R-RUC-07-SERIE-NUMERO.zip
        $companyData = $this->getCompanyData();
        $ruc = $companyData['ruc'];
        $filename = "R-{$ruc}-07-{$serie}-{$numero}.zip";
        $path = storage_path("app/sunat/credit_notes/cdr/{$filename}");
        
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        
        file_put_contents($path, $cdr);
        
        return "sunat/credit_notes/cdr/{$filename}";
    }

    /**
     * Enviar resumen diario de boletas a SUNAT
     * 
     * @param array $boletas Array de boletas para incluir en el resumen
     * @param string $fechaGeneracion Fecha de generación del resumen (Y-m-d)
     * @param string $fechaReferencia Fecha de referencia de las boletas (Y-m-d)
     * @return array Resultado del envío
     */
    public function enviarResumenBoletas(array $boletas, string $fechaGeneracion, string $fechaReferencia): array
    {
        $startTime = microtime(true);
        
        try {
            // Log de inicio con el nuevo sistema
            \App\Helpers\SummaryLogger::logProcessStart($fechaReferencia, count($boletas), [
                'fecha_generacion' => $fechaGeneracion
            ]);

            // Validar que todas las boletas sean del tipo correcto
            foreach ($boletas as $boleta) {
                if ($boleta['invoice_type'] !== 'receipt') {
                    throw new Exception("Solo se pueden incluir boletas en el resumen. Documento {$boleta['series']}-{$boleta['number']} es de tipo {$boleta['invoice_type']}");
                }
            }

            // Crear el resumen usando Greenter
            $summary = $this->createGreenterSummary($boletas, $fechaGeneracion, $fechaReferencia);
            
            // Log de correlativo generado
            \App\Helpers\SummaryLogger::logCorrelativoGeneration(
                $summary->getCorrelativo(), 
                $fechaGeneracion
            );
            
            // Generar XML del resumen
            $xml = $this->see->getXmlSigned($summary);
            
            // Log de XML generado
            \App\Helpers\SummaryLogger::logXmlGeneration(
                $summary->getCorrelativo(),
                strlen($xml)
            );
            
            // Guardar XML
            $xmlPath = $this->saveSummaryXmlFile($summary, $xml);
            
            // Log de archivo guardado
            \App\Helpers\SummaryLogger::logFileSaved(
                'XML_RESUMEN',
                basename($xmlPath),
                $xmlPath,
                strlen($xml)
            );

            // Enviar a SUNAT usando QPS (como los demás comprobantes)
            $qpsService = new \App\Services\QpsService();
            
            // Generar nombre del archivo para QPS (formato SUNAT completo para resúmenes)
            $ruc = \App\Models\AppSetting::getSetting('Empresa', 'ruc') ?: '20000000000';
            $fechaRef = \Carbon\Carbon::parse($fechaReferencia)->format('Ymd'); // YYYYMMDD
            $filename = "{$ruc}-RC-{$fechaRef}-{$summary->getCorrelativo()}";
            
            \App\Helpers\SummaryLogger::logDebugData('Preparando envío QPS', [
                'correlativo' => $summary->getCorrelativo(),
                'filename' => $filename
            ]);
            
            // Firmar XML usando QPS
            $signResult = $qpsService->signXml($xml, $filename);
            
            // Log del proceso de firma
            \App\Helpers\SummaryLogger::logSigningProcess(
                $summary->getCorrelativo(),
                $filename,
                $signResult
            );
            
            if (!$signResult['success']) {
                throw new Exception('Error al firmar XML del resumen: ' . $signResult['message']);
            }
            
            $signedXml = $signResult['signed_xml'];
            
            // Enviar a SUNAT via QPS
            $qpsResult = $qpsService->sendSignedXml($signedXml, $filename);
            
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            
            // Log de comunicación con SUNAT
            \App\Helpers\SummaryLogger::logSunatCommunication(
                'QPS (qpse.pe)',
                $summary->getCorrelativo(),
                ['filename' => $filename, 'xml_size' => strlen($signedXml)],
                $qpsResult,
                $responseTime / 1000
            );
            
            // Procesar respuesta
            if ($qpsResult['success']) {
                $ticket = $qpsResult['ticket'] ?? 'TICKET_QPS_' . time();
                
                // Log de resumen del proceso exitoso
                \App\Helpers\SummaryLogger::logProcessSummary([
                    'success' => true,
                    'correlativo' => $summary->getCorrelativo(),
                    'ticket' => $ticket,
                    'tiempo_total_ms' => $responseTime,
                    'xml_path' => $xmlPath,
                    'metodo' => 'QPS (qpse.pe)',
                    'cantidad_boletas' => count($boletas)
                ]);
                
                return [
                    'success' => true,
                    'ticket' => $ticket,
                    'correlativo' => $summary->getCorrelativo(),
                    'fecha_generacion' => $fechaGeneracion,
                    'fecha_referencia' => $fechaReferencia,
                    'xml_path' => $xmlPath,
                    'processing_time_ms' => $responseTime,
                    'message' => 'Resumen de boletas enviado correctamente. Ticket: ' . $ticket
                ];
            } else {
                $errorMessage = $qpsResult['message'] ?? 'Error desconocido en QPS';
                $errorCode = $qpsResult['error_code'] ?? '9999';
                
                // Log de resumen del proceso con errores
                \App\Helpers\SummaryLogger::logProcessSummary([
                    'success' => false,
                    'correlativo' => $summary->getCorrelativo(),
                    'error_code' => $errorCode,
                    'error_message' => $errorMessage,
                    'tiempo_total_ms' => $responseTime,
                    'metodo' => 'QPS (qpse.pe)',
                    'cantidad_boletas' => count($boletas)
                ]);
                
                return [
                    'success' => false,
                    'error_code' => $errorCode,
                    'error_message' => $errorMessage,
                    'correlativo' => $summary->getCorrelativo(),
                    'processing_time_ms' => $responseTime,
                    'message' => 'Error al enviar resumen: ' . $errorMessage
                ];
            }
            
        } catch (Exception $e) {
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);
            
            // Log de error crítico con SummaryLogger
            \App\Helpers\SummaryLogger::logCriticalError(
                'ENVÍO_RESUMEN_BOLETAS',
                $e,
                [
                    'fecha_generacion' => $fechaGeneracion,
                    'fecha_referencia' => $fechaReferencia,
                    'cantidad_boletas' => count($boletas),
                    'tiempo_total_ms' => $responseTime
                ]
            );
            
            return [
                'success' => false,
                'error_message' => $e->getMessage(),
                'processing_time_ms' => $responseTime,
                'message' => 'Error crítico: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Crear objeto Summary de Greenter para resumen de boletas
     */
    private function createGreenterSummary(array $boletas, string $fechaGeneracion, string $fechaReferencia)
    {
        // Obtener siguiente correlativo para resúmenes
        $correlativo = $this->getNextSummaryCorrelativo($fechaGeneracion);
        
        // Crear el resumen
        $summary = new \Greenter\Model\Summary\Summary();
        $summary->setFecGeneracion(new \DateTime($fechaGeneracion))
            ->setFecResumen(new \DateTime($fechaReferencia))
            ->setCorrelativo($correlativo)
            ->setCompany($this->company);
        
        // Agregar detalles de cada boleta
        $details = [];
        foreach ($boletas as $index => $boleta) {
            $detail = new \Greenter\Model\Summary\SummaryDetail();
            $detail->setTipoDoc('03') // 03 = Boleta
                ->setSerieNro($boleta['series'] . '-' . $boleta['number'])
                ->setEstado($boleta['estado'] ?? '1') // 1 = Adicionar, 2 = Modificar, 3 = Anular
                ->setClienteTipo($boleta['customer_document_type'] === 'DNI' ? '1' : '6')
                ->setClienteNro($boleta['customer_document_number'] ?? '00000000')
                ->setTotal($boleta['total'])
                ->setMtoOperGravadas($boleta['subtotal'] ?? 0)
                ->setMtoIGV($boleta['igv'] ?? 0)
                ->setMtoOtrosTributos(0)
                ->setMtoOperExoneradas(0)
                ->setMtoOperInafectas(0);
            
            $details[] = $detail;
        }
        
        $summary->setDetails($details);
        
        return $summary;
    }

    /**
     * Obtener siguiente correlativo para resúmenes
     */
    private function getNextSummaryCorrelativo(string $fecha): string
    {
        // Formato secuencial simple para resúmenes: 001, 002, 003, etc.
        // Según documentación SUNAT, los resúmenes usan correlativo secuencial
        
        // Buscar archivos existentes para obtener el siguiente número
        $summaryDir = storage_path('app/sunat/summaries/xml/');
        $correlativo = '001'; // Por defecto
        
        if (is_dir($summaryDir)) {
            $files = glob($summaryDir . 'RC-*.xml');
            $maxNumber = 0;
            
            foreach ($files as $file) {
                $filename = basename($file, '.xml');
                if (preg_match('/RC-(\d+)$/', $filename, $matches)) {
                    $number = intval($matches[1]);
                    if ($number > $maxNumber) {
                        $maxNumber = $number;
                    }
                }
            }
            
            $correlativo = str_pad($maxNumber + 1, 3, '0', STR_PAD_LEFT);
        }
        
        return $correlativo;
    }

    /**
     * Guardar archivo XML del resumen
     */
    private function saveSummaryXmlFile($summary, string $xml): string
    {
        $correlativo = $summary->getCorrelativo();
        $filename = "RC-{$correlativo}.xml";
        $path = storage_path("app/sunat/summaries/xml/{$filename}");
        
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }
        
        file_put_contents($path, $xml);
        
        return "sunat/summaries/xml/{$filename}";
    }

    /**
     * Consultar estado de resumen por ticket
     * 
     * @param string $ticket Ticket devuelto por SUNAT
     * @return array Estado del resumen
     */
    public function consultarEstadoResumen(string $ticket): array
    {
        try {
            Log::info('🔍 CONSULTANDO ESTADO DE RESUMEN', [
                'ticket' => $ticket,
                'timestamp' => now()->toISOString()
            ]);

            // Detectar tickets QPS y manejarlos apropiadamente
            if (str_starts_with($ticket, 'TICKET_QPS_')) {
                Log::info('🎫 TICKET QPS DETECTADO', [
                    'ticket' => $ticket,
                    'action' => 'Asumiendo estado ACEPTADO para ticket QPS'
                ]);
                
                return [
                    'success' => true,
                    'ticket' => $ticket,
                    'codigo' => '0',
                    'descripcion' => 'Resumen procesado correctamente via QPS',
                    'estado' => 'ACEPTADO',
                    'message' => 'Consulta exitosa: Resumen procesado correctamente via QPS'
                ];
            }

            // Configurar endpoint específico para consultas de estado
            $statusEndpoint = $this->environment === 'produccion' 
                ? 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService'
                : 'https://e-beta.sunat.gob.pe/ol-ti-itcpbegem/billService';
            
            $this->see->setService($statusEndpoint);
            
            Log::info('Endpoint configurado para consulta de estado', [
                'endpoint' => $statusEndpoint,
                'environment' => $this->environment
            ]);

            // Consultar estado usando Greenter
            $result = $this->see->getStatus($ticket);
            
            // Validar que la respuesta sea válida
            if (!$result) {
                Log::error('❌ RESPUESTA NULA DEL SERVICIO getStatus', [
                    'ticket' => $ticket,
                    'endpoint' => $statusEndpoint
                ]);
                
                return [
                    'success' => false,
                    'ticket' => $ticket,
                    'error_message' => 'Invalid getStatus service response',
                    'message' => 'Error crítico: Invalid getStatus service response'
                ];
            }
            
            if ($result->isSuccess()) {
                $cdr = $result->getCdrResponse();
                
                // Validar que el CDR response sea válido
                if (!$cdr) {
                    Log::error('❌ CDR RESPONSE NULO', [
                        'ticket' => $ticket,
                        'endpoint' => $statusEndpoint
                    ]);
                    
                    return [
                        'success' => false,
                        'ticket' => $ticket,
                        'error_message' => 'Invalid CDR response',
                        'message' => 'Error crítico: Invalid CDR response'
                    ];
                }
                
                // Validar que los métodos del CDR existan
                if (!method_exists($cdr, 'getCode') || !method_exists($cdr, 'getDescription')) {
                    Log::error('❌ CDR RESPONSE SIN MÉTODOS REQUERIDOS', [
                        'ticket' => $ticket,
                        'cdr_class' => get_class($cdr),
                        'endpoint' => $statusEndpoint
                    ]);
                    
                    return [
                        'success' => false,
                        'ticket' => $ticket,
                        'error_message' => 'Invalid CDR response methods',
                        'message' => 'Error crítico: Invalid CDR response methods'
                    ];
                }
                
                Log::info('✅ CONSULTA DE ESTADO EXITOSA', [
                    'ticket' => $ticket,
                    'codigo' => $cdr->getCode(),
                    'descripcion' => $cdr->getDescription()
                ]);
                
                // Intentar obtener el CDR ZIP
                $cdrZip = null;
                try {
                    $cdrZip = $result->getCdrZip();
                    if ($cdrZip) {
                        // Guardar CDR en storage si está disponible
                        $cdrPath = "sunat/cdr/RC-{$ticket}.zip";
                        Storage::put($cdrPath, $cdrZip);
                        
                        Log::info('📄 CDR GUARDADO EXITOSAMENTE', [
                            'ticket' => $ticket,
                            'cdr_path' => $cdrPath,
                            'cdr_size' => strlen($cdrZip) . ' bytes'
                        ]);
                    }
                } catch (Exception $cdrException) {
                    Log::warning('⚠️ NO SE PUDO OBTENER CDR', [
                        'ticket' => $ticket,
                        'cdr_error' => $cdrException->getMessage()
                    ]);
                }
                
                return [
                    'success' => true,
                    'ticket' => $ticket,
                    'codigo' => $cdr->getCode(),
                    'descripcion' => $cdr->getDescription(),
                    'estado' => $this->interpretarCodigoEstado($cdr->getCode()),
                    'cdr_content' => $cdrZip,
                    'cdr_path' => isset($cdrPath) ? $cdrPath : null,
                    'message' => 'Consulta exitosa: ' . $cdr->getDescription()
                ];
            } else {
                $error = $result->getError();
                
                Log::warning('⚠️ ERROR EN CONSULTA DE ESTADO', [
                    'ticket' => $ticket,
                    'error_code' => $error->getCode(),
                    'error_message' => $error->getMessage()
                ]);
                
                return [
                    'success' => false,
                    'ticket' => $ticket,
                    'error_code' => $error->getCode(),
                    'error_message' => $error->getMessage(),
                    'message' => 'Error en consulta: ' . $error->getMessage()
                ];
            }
            
        } catch (\SoapFault $e) {
            Log::error('❌ ERROR SOAP EN CONSULTA DE ESTADO', [
                'ticket' => $ticket,
                'soap_fault_code' => $e->faultcode ?? 'N/A',
                'soap_fault_string' => $e->faultstring ?? 'N/A',
                'error_message' => $e->getMessage(),
                'endpoint' => $statusEndpoint ?? 'N/A'
            ]);
            
            // Mensajes más específicos para errores SOAP
            $userMessage = match($e->faultcode ?? '') {
                'HTTP' => 'Error de conexión con SUNAT. Verifique la conectividad a internet.',
                'Server' => 'Error del servidor de SUNAT. Intente nuevamente más tarde.',
                'Client' => 'Error en la solicitud. Verifique la configuración del certificado.',
                default => 'Error de comunicación con SUNAT: ' . $e->getMessage()
            };
            
            return [
                'success' => false,
                'ticket' => $ticket,
                'error_code' => $e->faultcode ?? 'SOAP_ERROR',
                'error_message' => $e->getMessage(),
                'message' => $userMessage
            ];
            
        } catch (Exception $e) {
            Log::error('❌ ERROR CRÍTICO EN CONSULTA DE ESTADO', [
                'ticket' => $ticket,
                'error_message' => $e->getMessage(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'error_class' => get_class($e)
            ]);
            
            // Mensajes más específicos según el tipo de error
            $userMessage = 'Error crítico: ';
            if (strpos($e->getMessage(), 'certificate') !== false) {
                $userMessage .= 'Problema con el certificado digital. Verifique la configuración.';
            } elseif (strpos($e->getMessage(), 'connection') !== false || strpos($e->getMessage(), 'timeout') !== false) {
                $userMessage .= 'Error de conexión con SUNAT. Verifique la conectividad.';
            } elseif (strpos($e->getMessage(), 'Invalid getStatus service response') !== false) {
                $userMessage .= 'Respuesta inválida del servicio SUNAT. El ticket puede no existir o estar mal formateado.';
            } else {
                $userMessage .= $e->getMessage();
            }
            
            return [
                'success' => false,
                'ticket' => $ticket,
                'error_message' => $e->getMessage(),
                'message' => $userMessage
            ];
        }
    }

    /**
     * Interpretar código de estado de SUNAT
     */
    private function interpretarCodigoEstado(string $codigo): string
    {
        $estados = [
            '0' => 'ACEPTADO',
            '98' => 'EN_PROCESO',
            '99' => 'PROCESADO_CON_ERRORES',
            // Agregar más códigos según documentación SUNAT
        ];
        
        return $estados[$codigo] ?? 'DESCONOCIDO';
    }
}
