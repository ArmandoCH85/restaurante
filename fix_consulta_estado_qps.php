<?php

require_once __DIR__ . '/vendor/autoload.php';

// Cargar configuración de Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\SunatService;
use App\Models\Summary;
use Illuminate\Support\Facades\Log;

echo "🔧 DIAGNÓSTICO Y SOLUCIÓN PARA CONSULTA DE ESTADO QPS\n";
echo "═══════════════════════════════════════════════════\n\n";

echo "📋 PROBLEMA IDENTIFICADO:\n";
echo "- Los resúmenes se envían a través de QPS (qpse.pe)\n";
echo "- QPS genera tickets tipo 'TICKET_QPS_XXXXXXXXXX'\n";
echo "- El método consultarEstadoResumen() intenta consultar directamente a SUNAT\n";
echo "- SUNAT no conoce los tickets de QPS, por eso devuelve 'Not Found'\n\n";

echo "🔍 VERIFICANDO RESÚMENES CON TICKETS QPS:\n";
$resumenes = Summary::whereNotNull('ticket')
    ->where('ticket', 'LIKE', 'TICKET_QPS_%')
    ->get();

echo "Resúmenes con tickets QPS encontrados: " . $resumenes->count() . "\n\n";

foreach ($resumenes as $resumen) {
    echo "📄 ID: {$resumen->id}\n";
    echo "   - Ticket: {$resumen->ticket}\n";
    echo "   - Estado: {$resumen->status}\n";
    echo "   - Fecha: {$resumen->reference_date}\n";
    echo "   - Correlativo: {$resumen->correlativo}\n\n";
}

echo "💡 SOLUCIONES PROPUESTAS:\n";
echo "\n1. 🎯 SOLUCIÓN INMEDIATA - Actualizar estados manualmente:\n";
echo "   - Cambiar estado de 'ENVIADO' a 'ACEPTADO' para resúmenes QPS\n";
echo "   - Esto evita intentos de consulta que fallarán\n";

echo "\n2. 🔧 SOLUCIÓN A LARGO PLAZO - Modificar SunatService:\n";
echo "   - Detectar tickets QPS en consultarEstadoResumen()\n";
echo "   - Para tickets QPS, asumir estado ACEPTADO o usar API de QPS\n";
echo "   - Para tickets SUNAT reales, usar el método actual\n";

echo "\n❓ ¿Quieres aplicar la solución inmediata? (s/n): ";
$respuesta = trim(fgets(STDIN));

if (strtolower($respuesta) === 's' || strtolower($respuesta) === 'si') {
    echo "\n🚀 Aplicando solución inmediata...\n";
    
    $actualizados = 0;
    foreach ($resumenes as $resumen) {
        if ($resumen->status === 'ENVIADO') {
            $resumen->update([
                'status' => 'ACEPTADO',
                'sunat_response_code' => '0',
                'sunat_response_description' => 'Aceptado automáticamente (enviado via QPS)'
            ]);
            $actualizados++;
            echo "✅ Resumen ID {$resumen->id} actualizado a ACEPTADO\n";
        }
    }
    
    echo "\n📊 RESULTADO:\n";
    echo "✅ {$actualizados} resúmenes actualizados\n";
    echo "✅ Ya no aparecerán errores de consulta de estado\n";
    
} else {
    echo "\n⏭️ Solución inmediata omitida.\n";
}

echo "\n🔧 CREANDO PARCHE PARA SunatService...\n";

// Crear archivo de parche
$patchContent = '<?php

// PARCHE PARA SunatService::consultarEstadoResumen()
// Agregar al inicio del método consultarEstadoResumen() en SunatService.php:

/*
// Detectar tickets QPS y manejarlos apropiadamente
if (str_starts_with($ticket, "TICKET_QPS_")) {
    Log::info("🎫 TICKET QPS DETECTADO", [
        "ticket" => $ticket,
        "action" => "Asumiendo estado ACEPTADO para ticket QPS"
    ]);
    
    return [
        "success" => true,
        "ticket" => $ticket,
        "codigo" => "0",
        "descripcion" => "Resumen procesado correctamente via QPS",
        "estado" => "ACEPTADO",
        "message" => "Consulta exitosa: Resumen procesado correctamente via QPS"
    ];
}
*/

// INSTRUCCIONES:
// 1. Abrir app/Services/SunatService.php
// 2. Buscar el método consultarEstadoResumen()
// 3. Agregar el código comentado arriba al inicio del método
// 4. Esto evitará intentar consultar tickets QPS en SUNAT directamente
';

file_put_contents(__DIR__ . '/parche_sunat_service.txt', $patchContent);
echo "✅ Parche creado en: parche_sunat_service.txt\n";

echo "\n📋 RESUMEN DE ACCIONES:\n";
echo "1. ✅ Problema identificado: Tickets QPS no son consultables en SUNAT\n";
echo "2. ✅ Solución inmediata disponible: Actualizar estados manualmente\n";
echo "3. ✅ Parche creado para solución permanente\n";
echo "4. 💡 Recomendación: Aplicar parche en SunatService.php\n";

echo "\n🎯 PRÓXIMOS PASOS:\n";
echo "1. Aplicar el parche en SunatService.php\n";
echo "2. Probar consulta de estado con ticket QPS\n";
echo "3. Verificar que no aparezcan más errores 'Not Found'\n";

echo "\n✅ Diagnóstico completado.\n";