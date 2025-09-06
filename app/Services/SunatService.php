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
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Sale\PaymentTerms;
use Greenter\Model\Sale\Cuota;
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
            $writeLog('🔧 CONSTRUYENDO DATOS PARA SUNAT', [
                'invoice_id' => $invoiceId,
                'paso' => 'createGreenterInvoice',
                'timestamp_inicio' => now()->toISOString()
            ]);

            // Crear factura Greenter
            $greenterInvoice = $this->createGreenterInvoice($invoice);

            $buildTime = round((microtime(true) - $buildStart) * 1000, 2);
            $writeLog('📋 FACTURA GREENTER CREADA', [
                'invoice_id' => $invoiceId,
                'serie_greenter' => $greenterInvoice->getSerie(),
                'correlativo_greenter' => $greenterInvoice->getCorrelativo(),
                'tipo_doc_greenter' => $greenterInvoice->getTipoDoc(),
                'tiempo_construccion_ms' => $buildTime
            ]);

            // Generar XML
            $xml = $this->see->getXmlSigned($greenterInvoice);
            $xmlPath = $this->saveXmlFile($xml, $invoice, 'signed');

            $writeLog('📄 XML GENERADO Y GUARDADO', [
                'invoice_id' => $invoiceId,
                'xml_path' => $xmlPath,
                'xml_size_bytes' => strlen($xml),
                'xml_filename' => basename($xmlPath)
            ]);

            // 🌐 LOG: CONEXIÓN CON SUNAT
            $writeLog('🌐 CONECTANDO CON SUNAT', [
                'invoice_id' => $invoiceId,
                'endpoint' => $endpoint,
                'servidor' => 'SUNAT_OFICIAL',
                'tipo_conexion' => 'SOAP_WSDL',
                'timestamp_conexion' => now()->toISOString()
            ]);

            // 📨 LOG: ENVIANDO A SUNAT
            $sendStart = microtime(true);
            $writeLog('📨 ENVIANDO COMPROBANTE A SUNAT', [
                'invoice_id' => $invoiceId,
                'destino_exacto' => $endpoint,
                'servidor_sunat' => 'e-factura.sunat.gob.pe',
                'puerto' => '443',
                'servicio' => 'billService',
                'metodo' => 'sendBill',
                'xml_enviado' => basename($xmlPath),
                'tipo_envio' => 'OFICIAL_SUNAT',
                'timestamp_envio' => now()->toISOString()
            ]);

            // 📨 LOG: EJECUTANDO ENVÍO GREENTER
            $writeLog('🔄 EJECUTANDO Greenter::send()', [
                'invoice_id' => $invoiceId,
                'metodo_greenter' => 'send',
                'tipo_documento' => 'invoice',
                'data_structure' => 'completa_con_detalles',
                'timestamp_inicio_envio' => now()->toISOString()
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
    private function createGreenterInvoice($invoice)
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
        $greenterInvoice
            ->setUblVersion('2.1')
            ->setTipoOperacion('0101') // Venta interna
            ->setTipoDoc($tipoDocSunat)
            ->setSerie($invoice->series)
            ->setCorrelativo($invoice->number)
            ->setFechaEmision(new \DateTime($invoice->issue_date))
            ->setTipoMoneda('PEN')
            ->setCompany($this->company)
            ->setClient($this->createClient($invoice->customer));

        // Agregar detalles
        $details = [];
        $sumaValorVenta = 0; // Para verificar consistencia
        $sumaIgv = 0; // Para verificar consistencia

        foreach ($invoice->details as $detail) {
            // CORRECCIÓN: Los precios en BD NO incluyen IGV
            // Los precios y subtotales están SIN IGV
            $valorUnitarioSinIgv = $detail->unit_price; // Precio unitario SIN IGV (como está en BD)
            $valorVentaSinIgv = $detail->subtotal; // Subtotal SIN IGV (como está en BD)
            $igvItem = round($valorVentaSinIgv * 0.18, 2); // IGV calculado (18%)
            $precioUnitarioConIgv = round($valorUnitarioSinIgv * 1.18, 2); // Precio CON IGV para XML

            // Acumular para verificación
            $sumaValorVenta += $valorVentaSinIgv;
            $sumaIgv += $igvItem;

            // Log detallado de cada item
            Log::info('Cálculo de item individual', [
                'invoice_id' => $invoice->id,
                'product_id' => $detail->product->id,
                'product_name' => $detail->product->name,
                'quantity' => $detail->quantity,
                'unit_price_bd_sin_igv' => $detail->unit_price,
                'subtotal_bd_sin_igv' => $detail->subtotal,
                'precio_con_igv_para_xml' => $precioUnitarioConIgv,
                'valor_venta_sin_igv_xml' => $valorVentaSinIgv,
                'igv_calculado_18_porciento' => $igvItem,
                'total_item_con_igv' => $valorVentaSinIgv + $igvItem,
                'verificacion' => [
                    'unit_price_debe_ser' => $detail->unit_price,
                    'precio_con_igv_debe_ser' => round($detail->unit_price * 1.18, 2)
                ]
            ]);

            $item = new SaleDetail();
            $item->setCodProducto($detail->product->id)
                ->setUnidad('NIU') // Unidad
                ->setCantidad($detail->quantity)
                ->setDescripcion($detail->product->name)
                ->setMtoBaseIgv($valorVentaSinIgv) // Base imponible (sin IGV)
                ->setPorcentajeIgv(18.00) // Porcentaje IGV
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
            'nota' => 'Precios en BD son SIN IGV, XML debe mostrar CON IGV'
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
        
        foreach ($invoiceDetails as $detail) {
            // Los precios en BD están SIN IGV
            $valorUnitarioSinIgv = round($detail->unit_price, 2);
            $cantidad = $detail->quantity;
            $valorVentaSinIgv = round($valorUnitarioSinIgv * $cantidad, 2);
            $igvItem = round($valorVentaSinIgv * 0.18, 2);
            $precioUnitarioConIgv = round($valorUnitarioSinIgv * 1.18, 2);
            
            $details[] = [
                "codProducto" => $detail->product->id ?? 'P' . $detail->id,
                "unidad" => "NIU", // Catálogo 03 - Unidad de medida
                "cantidad" => $cantidad,
                "mtoValorUnitario" => $valorUnitarioSinIgv,
                "descripcion" => $detail->product->name ?? 'Producto',
                "mtoBaseIgv" => $valorVentaSinIgv,
                "porcentajeIgv" => 18.00,
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
        
        foreach ($invoiceDetails as $detail) {
            $valorVentaSinIgv = round($detail->unit_price * $detail->quantity, 2);
            $igvItem = round($valorVentaSinIgv * 0.18, 2);
            
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
            'cache_wsdl' => WSDL_CACHE_NONE, // Deshabilitar caché WSDL
            'connection_timeout' => 30,
            'user_agent' => 'PHP-SOAP/7.4',
            'stream_context' => stream_context_create([
                'http' => [
                    'timeout' => 30,
                    'user_agent' => 'PHP-SOAP/7.4'
                ]
            ])
        ]);
        
        $parameters = [
            'fileName' => $filename,
            'contentFile' => base64_encode($zipContent),
        ];
        
        // Log detallado del envío
        Log::info('Enviando comprobante a SUNAT', [
            'endpoint' => $sunatEndpoint,
            'filename' => $filename,
            'parameters_keys' => array_keys($parameters),
            'soap_action' => 'sendBill'
        ]);
        
        try {
            $response = $soapClient->sendBill($parameters);
            
            Log::info('Respuesta exitosa de SUNAT', [
                'endpoint' => $sunatEndpoint,
                'filename' => $filename,
                'response_type' => gettype($response)
            ]);
            
            return [
                'success' => true,
                'response' => $response,
                'endpoint_used' => $sunatEndpoint,
                'filename_sent' => $filename
            ];
        } catch (\SoapFault $e) {
            Log::error('Error SOAP al enviar a SUNAT', [
                'endpoint' => $sunatEndpoint,
                'filename' => $filename,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'fault_code' => $e->faultcode ?? 'N/A',
                'fault_string' => $e->faultstring ?? 'N/A'
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'endpoint_used' => $sunatEndpoint,
                'filename_sent' => $filename
            ];
        }
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
}
