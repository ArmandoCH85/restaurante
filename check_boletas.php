<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Invoice;
use Carbon\Carbon;

echo "=== VERIFICANDO BOLETAS DEL 2025-09-07 ===\n\n";

// Consultar boletas del 7 de septiembre de 2025
$boletas = Invoice::where('invoice_type', 'receipt')
    ->whereDate('issue_date', '2025-09-07')
    ->get(['id', 'series', 'number', 'issue_date', 'sunat_status', 'total']);

echo "Total de boletas encontradas: " . $boletas->count() . "\n\n";

if ($boletas->count() > 0) {
    echo "Detalle de boletas:\n";
    echo str_repeat('-', 80) . "\n";
    printf("%-5s %-15s %-12s %-15s %-10s\n", 'ID', 'Serie-Número', 'Fecha', 'Estado SUNAT', 'Total');
    echo str_repeat('-', 80) . "\n";
    
    foreach ($boletas as $boleta) {
        printf("%-5s %-15s %-12s %-15s S/ %.2f\n", 
            $boleta->id,
            $boleta->series . '-' . $boleta->number,
            $boleta->issue_date,
            $boleta->sunat_status ?? 'NULL',
            $boleta->total
        );
    }
    
    echo "\n=== ANÁLISIS PARA RESUMEN ===\n";
    
    // Boletas que deberían incluirse en resumen (ACEPTADO)
    $boletasAceptadas = $boletas->where('sunat_status', 'ACEPTADO');
    echo "Boletas con estado ACEPTADO: " . $boletasAceptadas->count() . "\n";
    
    // Boletas pendientes
    $boletasPendientes = $boletas->whereIn('sunat_status', [null, 'PENDIENTE']);
    echo "Boletas PENDIENTES: " . $boletasPendientes->count() . "\n";
    
    // Otras boletas
    $otrasboletas = $boletas->whereNotIn('sunat_status', [null, 'PENDIENTE', 'ACEPTADO']);
    echo "Otras boletas: " . $otrasboletas->count() . "\n";
    
    if ($otrasboletas->count() > 0) {
        echo "Estados encontrados: " . $otrasboletas->pluck('sunat_status')->unique()->implode(', ') . "\n";
    }
    
} else {
    echo "No se encontraron boletas para la fecha 2025-09-07\n";
    
    // Verificar si hay boletas en otras fechas
    echo "\n=== VERIFICANDO OTRAS FECHAS ===\n";
    $todasBoletas = Invoice::where('invoice_type', 'receipt')
        ->orderBy('issue_date', 'desc')
        ->limit(10)
        ->get(['id', 'series', 'number', 'issue_date', 'sunat_status']);
        
    echo "Últimas 10 boletas en el sistema:\n";
    foreach ($todasBoletas as $boleta) {
        echo "ID: {$boleta->id}, {$boleta->series}-{$boleta->number}, Fecha: {$boleta->issue_date}, Estado: " . ($boleta->sunat_status ?? 'NULL') . "\n";
    }
}

echo "\n=== FIN DEL ANÁLISIS ===\n";