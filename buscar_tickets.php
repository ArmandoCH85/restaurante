<?php

require_once __DIR__ . '/vendor/autoload.php';

// Cargar configuración de Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Summary;
use Illuminate\Support\Facades\DB;

echo "🔍 BUSCANDO TICKETS DE RESÚMENES ENVIADOS\n";
echo "═══════════════════════════════════════\n\n";

try {
    // Buscar resúmenes con ticket
    $resumenes = Summary::whereNotNull('ticket')
        ->where('status', 'ENVIADO')
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();
    
    if ($resumenes->isEmpty()) {
        echo "❌ No se encontraron resúmenes con tickets.\n";
        echo "\n💡 Intentando buscar cualquier resumen...\n";
        
        $todosResumenes = Summary::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        if ($todosResumenes->isEmpty()) {
            echo "❌ No hay resúmenes en la base de datos.\n";
        } else {
            echo "📋 RESÚMENES ENCONTRADOS (sin tickets):\n";
            foreach ($todosResumenes as $resumen) {
                echo "- ID: {$resumen->id} | Estado: {$resumen->status} | Fecha: {$resumen->reference_date} | Ticket: " . ($resumen->ticket ?? 'N/A') . "\n";
            }
        }
    } else {
        echo "✅ RESÚMENES CON TICKETS ENCONTRADOS:\n";
        echo "═══════════════════════════════════════\n";
        
        foreach ($resumenes as $resumen) {
            echo "📄 ID: {$resumen->id}\n";
            echo "   - Ticket: {$resumen->ticket}\n";
            echo "   - Estado: {$resumen->status}\n";
            echo "   - Fecha referencia: {$resumen->reference_date}\n";
            echo "   - Correlativo: {$resumen->correlativo}\n";
            echo "   - Cantidad boletas: {$resumen->receipt_count}\n";
            echo "   - Total: S/ " . number_format($resumen->total_amount, 2) . "\n";
            echo "   - Creado: {$resumen->created_at}\n";
            echo "\n";
        }
        
        // Usar el primer ticket para la prueba
        $primerTicket = $resumenes->first()->ticket;
        echo "🎫 TICKET PARA PRUEBA: {$primerTicket}\n";
        echo "\n💡 Ejecuta: echo '{$primerTicket}' | php debug_consulta_estado.php\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "📁 Archivo: " . $e->getFile() . "\n";
    echo "📍 Línea: " . $e->getLine() . "\n";
}

echo "\n✅ Búsqueda completada.\n";