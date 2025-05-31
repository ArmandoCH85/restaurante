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

        // Configurar endpoint según entorno
        if ($this->environment === 'produccion') {
            $this->see->setService(SunatEndpoints::FE_PRODUCCION);
        } else {
            $this->see->setService(SunatEndpoints::FE_BETA);
        }

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
        try {
            // Obtener datos de la factura
            $invoice = Invoice::with(['details.product', 'customer', 'employee'])
                ->findOrFail($invoiceId);

            // Validar que sea Boleta o Factura (NO Nota de Venta)
            if (!in_array($invoice->invoice_type, ['invoice', 'receipt'])) {
                throw new Exception('Solo se pueden enviar Boletas y Facturas a SUNAT. Las Notas de Venta son documentos internos.');
            }

            // Actualizar estado a enviando
            Invoice::where('id', $invoiceId)->update(['sunat_status' => 'ENVIANDO']);

            // Log inicial del proceso
            Log::info('Iniciando emisión de factura', [
                'invoice_id' => $invoiceId,
                'series_number' => $invoice->series . '-' . $invoice->number,
                'environment' => $this->environment,
                'company_ruc' => $this->company->getRuc()
            ]);

            // Crear factura Greenter
            $greenterInvoice = $this->createGreenterInvoice($invoice);

            // Log de la factura Greenter creada
            Log::info('Factura Greenter creada', [
                'invoice_id' => $invoiceId,
                'greenter_serie' => $greenterInvoice->getSerie(),
                'greenter_correlativo' => $greenterInvoice->getCorrelativo(),
                'greenter_tipo_doc' => $greenterInvoice->getTipoDoc(),
                'greenter_company_ruc' => $greenterInvoice->getCompany()->getRuc()
            ]);

            // Generar XML
            $xml = $this->see->getXmlSigned($greenterInvoice);
            $xmlPath = $this->saveXmlFile($xml, $invoice, 'signed');

            // Log del XML generado
            Log::info('XML generado y guardado', [
                'invoice_id' => $invoiceId,
                'xml_path' => $xmlPath,
                'xml_size' => strlen($xml) . ' bytes'
            ]);

            // Generar PDF (opcional - Greenter no tiene método getPdf nativo)
            $pdfPath = null; // Por ahora no generamos PDF

            // Log antes del envío
            Log::info('Enviando a SUNAT', [
                'invoice_id' => $invoiceId,
                'endpoint' => $this->environment === 'produccion' ? 'PRODUCCION' : 'BETA',
                'xml_filename' => basename($xmlPath)
            ]);

            // Enviar a SUNAT
            $result = $this->see->send($greenterInvoice);

            // Log del resultado del envío
            Log::info('Respuesta de SUNAT recibida', [
                'invoice_id' => $invoiceId,
                'is_success' => $result->isSuccess(),
                'has_error' => $result->getError() !== null,
                'error_code' => $result->getError() ? $result->getError()->getCode() : null,
                'error_message' => $result->getError() ? $result->getError()->getMessage() : null
            ]);

            // Procesar respuesta
            $this->processResponse($invoice, $result, $xmlPath, $pdfPath);

            return [
                'success' => true,
                'message' => 'Factura enviada correctamente',
                'xml_path' => $xmlPath,
                'pdf_path' => $pdfPath,
                'sunat_response' => $result->getCdrResponse()
            ];

        } catch (Exception $e) {
            // Log detallado del error de excepción
            Log::error('Error crítico al emitir factura', [
                'invoice_id' => $invoiceId,
                'error_message' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_file' => $e->getFile(),
                'error_line' => $e->getLine(),
                'environment' => $this->environment,
                'trace' => $e->getTraceAsString(),
                'context' => [
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

            return [
                'success' => false,
                'message' => 'Error al emitir factura: ' . $e->getMessage(),
                'error_code' => $e->getCode(),
                'error_details' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ];
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
