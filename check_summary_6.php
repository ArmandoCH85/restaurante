<?php

require_once 'vendor/autoload.php';

// Configurar Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Summary;
use App\Models\Invoice;
use Carbon\Carbon;

echo "🔍 VERIFICANDO RESUMEN ID 6\n";
echo "============================\n\n";

// Obtener el resumen 6
$summary = Summary::find(6);

if (!$summary) {
    echo "❌ No se encontró el resumen con ID 6\n";
    exit(1);
}

echo "📋 DATOS DEL RESUMEN:\n";
echo "ID: {$summary->id}\n";
echo "Correlativo: {$summary->correlativo}\n";
echo "Fecha de referencia: {$summary->fecha_referencia}\n";
echo "Estado: {$summary->status}\n";
echo "Cantidad de boletas: {$summary->receipts_count}\n";
echo "Monto total: S/ {$summary->total_amount}\n";
echo "\n";

// Buscar boletas que deberían estar en este resumen
echo "🔍 BUSCANDO BOLETAS PARA ESTA FECHA:\n";
echo "Fecha de búsqueda: {$summary->fecha_referencia}\n";
echo "\n";

$boletas = Invoice::where('issue_date', $summary->fecha_referencia)
    ->where('sunat_status', 'ACEPTADO')
    ->where('invoice_type', 'receipt')
    ->get();

echo "📊 BOLETAS ENCONTRADAS: {$boletas->count()}\n";
echo "\n";

if ($boletas->count() > 0) {
    echo "📄 DETALLE DE BOLETAS:\n";
    echo "ID\tSerie-Número\tFecha\t\tTotal\n";
    echo "--\t------------\t-----\t\t-----\n";
    
    foreach ($boletas as $boleta) {
        echo sprintf(
            "%d\t%s-%s\t%s\tS/ %.2f\n",
            $boleta->id,
            $boleta->series,
            $boleta->number,
            $boleta->issue_date->format('Y-m-d'),
            $boleta->total
        );
    }
} else {
    echo "❌ No se encontraron boletas que cumplan los criterios:\n";
    echo "   - Fecha de emisión: {$summary->fecha_referencia}\n";
    echo "   - Estado SUNAT: ACEPTADO\n";
    echo "   - Tipo: receipt\n";
    echo "\n";
    
    // Verificar si hay boletas en esa fecha con otros estados
    $todasLasBoletas = Invoice::where('issue_date', $summary->fecha_referencia)
        ->where('invoice_type', 'receipt')
        ->get();
    
    echo "📊 TODAS LAS BOLETAS DE ESA FECHA: {$todasLasBoletas->count()}\n";
    
    if ($todasLasBoletas->count() > 0) {
        echo "\n📄 DETALLE (TODOS LOS ESTADOS):\n";
        echo "ID\tSerie-Número\tEstado SUNAT\tTotal\n";
        echo "--\t------------\t------------\t-----\n";
        
        foreach ($todasLasBoletas as $boleta) {
            echo sprintf(
                "%d\t%s-%s\t%s\t\tS/ %.2f\n",
                $boleta->id,
                $boleta->series,
                $boleta->number,
                $boleta->sunat_status ?? 'NULL',
                $boleta->total
            );
        }
    }
}

echo "\n✅ Verificación completada.\n";