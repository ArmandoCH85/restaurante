<?php

/**
 * Ejemplo de uso del método enviarResumenBoletas
 * 
 * Este archivo demuestra cómo usar la nueva funcionalidad de resumen de boletas
 * implementada en SunatService siguiendo el patrón del código Greenter proporcionado.
 */

require_once 'vendor/autoload.php';

use App\Services\SunatService;
use App\Models\Invoice;
use Illuminate\Support\Facades\Log;

try {
    // Inicializar el servicio SUNAT
    $sunatService = new SunatService();
    
    // Fecha de referencia (fecha de las boletas)
    $fechaReferencia = '2024-01-15';
    
    // Fecha de generación del resumen (hoy)
    $fechaGeneracion = date('Y-m-d');
    
    // Obtener boletas del día de referencia desde la base de datos
    $boletasQuery = Invoice::where('invoice_type', 'receipt')
        ->whereDate('issue_date', $fechaReferencia)
        ->with(['customer'])
        ->get();
    
    // Convertir a array con la estructura requerida
    $boletas = [];
    foreach ($boletasQuery as $boleta) {
        $boletas[] = [
            'series' => $boleta->series,
            'number' => $boleta->number,
            'invoice_type' => $boleta->invoice_type,
            'total' => $boleta->total,
            'subtotal' => $boleta->subtotal,
            'igv' => $boleta->igv,
            'customer_document_type' => $boleta->customer->document_type ?? 'DNI',
            'customer_document_number' => $boleta->customer->document_number ?? '00000000',
            'estado' => '1' // 1 = Adicionar, 2 = Modificar, 3 = Anular
        ];
    }
    
    echo "📋 Preparando resumen de boletas\n";
    echo "Fecha de referencia: {$fechaReferencia}\n";
    echo "Fecha de generación: {$fechaGeneracion}\n";
    echo "Cantidad de boletas: " . count($boletas) . "\n\n";
    
    if (empty($boletas)) {
        echo "⚠️ No se encontraron boletas para la fecha {$fechaReferencia}\n";
        exit;
    }
    
    // Enviar resumen a SUNAT
    echo "🚀 Enviando resumen a SUNAT...\n";
    $resultado = $sunatService->enviarResumenBoletas($boletas, $fechaGeneracion, $fechaReferencia);
    
    if ($resultado['success']) {
        echo "✅ Resumen enviado exitosamente\n";
        echo "Ticket: {$resultado['ticket']}\n";
        echo "Correlativo: {$resultado['correlativo']}\n";
        echo "XML guardado en: {$resultado['xml_path']}\n";
        echo "Tiempo de procesamiento: {$resultado['processing_time_ms']} ms\n\n";
        
        // Guardar el ticket para consultar el estado después
        $ticket = $resultado['ticket'];
        
        // Esperar un momento antes de consultar el estado
        echo "⏳ Esperando 5 segundos antes de consultar el estado...\n";
        sleep(5);
        
        // Consultar estado del resumen
        echo "🔍 Consultando estado del resumen...\n";
        $estadoResultado = $sunatService->consultarEstadoResumen($ticket);
        
        if ($estadoResultado['success']) {
            echo "✅ Consulta de estado exitosa\n";
            echo "Código: {$estadoResultado['codigo']}\n";
            echo "Descripción: {$estadoResultado['descripcion']}\n";
            echo "Estado interpretado: {$estadoResultado['estado']}\n";
            
            if (isset($estadoResultado['cdr_content'])) {
                echo "📄 CDR recibido y disponible\n";
            }
        } else {
            echo "❌ Error al consultar estado: {$estadoResultado['message']}\n";
        }
        
    } else {
        echo "❌ Error al enviar resumen\n";
        echo "Mensaje: {$resultado['message']}\n";
        
        if (isset($resultado['error_code'])) {
            echo "Código de error: {$resultado['error_code']}\n";
        }
        
        if (isset($resultado['error_message'])) {
            echo "Detalle del error: {$resultado['error_message']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error crítico: {$e->getMessage()}\n";
    echo "Archivo: {$e->getFile()}\n";
    echo "Línea: {$e->getLine()}\n";
}

echo "\n🏁 Proceso completado\n";

/**
 * NOTAS IMPORTANTES:
 * 
 * 1. PROCESO ASÍNCRONO:
 *    - El envío del resumen es asíncrono
 *    - SUNAT devuelve un ticket
 *    - Debes consultar el estado usando el ticket
 * 
 * 2. HORARIOS DE ENVÍO:
 *    - Los resúmenes se pueden enviar hasta las 12:00 PM del día siguiente
 *    - Para boletas del día anterior
 * 
 * 3. ESTADOS POSIBLES:
 *    - 0: ACEPTADO
 *    - 98: EN_PROCESO
 *    - 99: PROCESADO_CON_ERRORES
 * 
 * 4. ESTRUCTURA DE BOLETAS:
 *    - Solo boletas (invoice_type = 'receipt')
 *    - Deben tener todos los campos requeridos
 *    - El estado puede ser: 1=Adicionar, 2=Modificar, 3=Anular
 * 
 * 5. ARCHIVOS GENERADOS:
 *    - XML del resumen: storage/app/sunat/summaries/xml/
 *    - CDR de respuesta: se obtiene al consultar el estado
 * 
 * 6. LOGS:
 *    - Todos los procesos se registran en los logs de Laravel
 *    - Usar Log::info() para seguimiento detallado
 */