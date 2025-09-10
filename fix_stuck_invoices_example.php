<?php

/**
 * Ejemplo de uso del comando para corregir facturas atascadas
 * 
 * Este script demuestra cómo usar el nuevo comando artisan para corregir
 * facturas que quedaron en estado ENVIANDO por timeout de 30 segundos
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CORRECTOR DE FACTURAS ATASCADAS ===\n";
echo "Demostrando el uso del comando fix-stuck-invoices\n\n";

try {
    // 1. Primero, verificar qué facturas están atascadas
    echo "📋 1. VERIFICANDO FACTURAS ATASCADAS\n";
    echo "=====================================\n";
    
    $stuckInvoices = \App\Models\Invoice::whereIn('invoice_type', ['invoice', 'receipt'])
        ->where('sunat_status', 'ENVIANDO')
        ->where('updated_at', '<=', \Carbon\Carbon::now()->subHours(2))
        ->with(['customer'])
        ->get();
    
    if ($stuckInvoices->isEmpty()) {
        echo "✅ No hay facturas atascadas en este momento\n";
        echo "\n💡 Para simular facturas atascadas, puede ejecutar:\n";
        echo "UPDATE invoices SET sunat_status = 'ENVIANDO', updated_at = NOW() - INTERVAL 3 HOUR WHERE id = [ID_FACTURA];\n\n";
    } else {
        echo "⚠️  Encontradas {$stuckInvoices->count()} facturas atascadas:\n\n";
        
        foreach ($stuckInvoices as $invoice) {
            echo "- ID: {$invoice->id} | {$invoice->series}-{$invoice->number} | ";
            echo "Estado: {$invoice->sunat_status} | ";
            echo "Actualizada: {$invoice->updated_at->format('d/m/Y H:i:s')}\n";
        }
    }
    
    echo "\n📚 2. EJEMPLOS DE USO DEL COMANDO\n";
    echo "=================================\n\n";
    
    echo "🔍 A) Ver qué facturas se corregirían (sin ejecutar):\n";
    echo "php artisan sunat:fix-stuck-invoices --dry-run\n\n";
    
    echo "🔧 B) Corregir facturas en estado ENVIANDO (últimas 2 horas):\n";
    echo "php artisan sunat:fix-stuck-invoices --method=qps\n\n";
    
    echo "⚡ C) Corregir facturas en estado ERROR (últimas 4 horas):\n";
    echo "php artisan sunat:fix-stuck-invoices --status=ERROR --hours=4 --method=qps\n\n";
    
    echo "🎯 D) Corregir una factura específica:\n";
    echo "php artisan sunat:fix-stuck-invoices --invoice-id=123 --method=qps\n\n";
    
    echo "🚀 E) Corrección forzada sin confirmación:\n";
    echo "php artisan sunat:fix-stuck-invoices --force --method=qps\n\n";
    
    echo "📊 3. PARÁMETROS DISPONIBLES\n";
    echo "============================\n";
    echo "--status=ENVIANDO|ERROR    Estado a corregir (default: ENVIANDO)\n";
    echo "--hours=N                  Horas desde última actualización (default: 2)\n";
    echo "--method=qps|sunat         Método de reenvío (default: qps)\n";
    echo "--dry-run                  Solo mostrar, no ejecutar\n";
    echo "--force                    No pedir confirmación\n";
    echo "--invoice-id=N             Corregir factura específica\n\n";
    
    echo "🎯 4. CASOS DE USO COMUNES\n";
    echo "==========================\n\n";
    
    echo "📋 Caso 1: Timeout de 30 segundos\n";
    echo "Problema: Facturas quedan en ENVIANDO por timeout\n";
    echo "Solución: php artisan sunat:fix-stuck-invoices --method=qps\n\n";
    
    echo "📋 Caso 2: Error de conexión SUNAT\n";
    echo "Problema: Facturas en estado ERROR por fallas de red\n";
    echo "Solución: php artisan sunat:fix-stuck-invoices --status=ERROR --method=qps\n\n";
    
    echo "📋 Caso 3: Corrección masiva nocturna\n";
    echo "Problema: Múltiples facturas atascadas del día\n";
    echo "Solución: php artisan sunat:fix-stuck-invoices --hours=24 --force --method=qps\n\n";
    
    echo "📋 Caso 4: Corrección específica urgente\n";
    echo "Problema: Una factura importante atascada\n";
    echo "Solución: php artisan sunat:fix-stuck-invoices --invoice-id=123 --force --method=qps\n\n";
    
    echo "🔧 5. INTERFAZ WEB\n";
    echo "==================\n";
    echo "También puede corregir facturas desde la interfaz web:\n";
    echo "1. Ir a /admin/invoices\n";
    echo "2. Buscar facturas con estado 'Enviando' o 'Error'\n";
    echo "3. Hacer clic en 'Editar' en la factura\n";
    echo "4. Usar el botón 'Corregir Envío' (azul)\n";
    echo "5. Seleccionar método (QPS recomendado)\n";
    echo "6. Agregar motivo y confirmar\n\n";
    
    echo "⚠️  6. RECOMENDACIONES IMPORTANTES\n";
    echo "==================================\n";
    echo "✅ Use siempre --dry-run primero para verificar\n";
    echo "✅ Prefiera método 'qps' sobre 'sunat' (más estable)\n";
    echo "✅ Revise los logs después de la corrección\n";
    echo "✅ Verifique los estados en /admin/invoices\n";
    echo "✅ Para automatizar, agregue a cron job:\n";
    echo "   0 */2 * * * cd /ruta/proyecto && php artisan sunat:fix-stuck-invoices --force --method=qps\n\n";
    
    echo "📝 7. LOGS Y MONITOREO\n";
    echo "======================\n";
    echo "Los logs se guardan en:\n";
    echo "- storage/logs/laravel.log (errores generales)\n";
    echo "- Logs específicos del comando con detalles completos\n";
    echo "- Logs de QPS/SUNAT según el método usado\n\n";
    
    echo "🆘 8. SOLUCIÓN DE PROBLEMAS\n";
    echo "===========================\n";
    echo "❌ 'No se encontraron facturas': Normal si no hay atascadas\n";
    echo "❌ 'Error de certificado': Verificar configuración SUNAT\n";
    echo "❌ 'Timeout persiste': Usar método QPS en lugar de SUNAT\n";
    echo "❌ 'Error de permisos': Verificar permisos de archivos\n\n";
    
    // Mostrar ejemplo práctico si hay facturas
    if (!$stuckInvoices->isEmpty()) {
        $firstInvoice = $stuckInvoices->first();
        echo "🚀 9. EJEMPLO PRÁCTICO\n";
        echo "=====================\n";
        echo "Para corregir la factura {$firstInvoice->series}-{$firstInvoice->number} (ID: {$firstInvoice->id}):\n\n";
        echo "Comando específico:\n";
        echo "php artisan sunat:fix-stuck-invoices --invoice-id={$firstInvoice->id} --method=qps\n\n";
        echo "O desde interfaz web:\n";
        echo "http://restaurante.test/admin/invoices/{$firstInvoice->id}/edit\n";
        echo "(Buscar botón 'Corregir Envío' azul)\n\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: {$e->getMessage()}\n";
    echo "Archivo: {$e->getFile()}\n";
    echo "Línea: {$e->getLine()}\n";
}

echo "=== FIN DEL EJEMPLO ===\n";
echo "\n💡 Para más información, consulte la documentación del comando:\n";
echo "php artisan help sunat:fix-stuck-invoices\n";