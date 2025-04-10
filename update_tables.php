<?php

// Este script actualiza todas las mesas a estado "available"
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Table;
use Illuminate\Support\Facades\DB;

// Contar las mesas antes de la actualización
$mesasAntes = Table::select('status', DB::raw('count(*) as total'))
               ->groupBy('status')
               ->get()
               ->pluck('total', 'status')
               ->toArray();

echo "Estado de las mesas antes de la actualización:\n";
foreach ($mesasAntes as $status => $total) {
    echo "- $status: $total mesas\n";
}

// Actualizar todas las mesas a "available"
$actualizadas = Table::where('status', '!=', 'available')
                ->update(['status' => 'available']);

echo "\nSe actualizaron $actualizadas mesas a estado 'available'.\n";

// Verificar el estado actual
$mesasDespues = Table::select('status', DB::raw('count(*) as total'))
                 ->groupBy('status')
                 ->get()
                 ->pluck('total', 'status')
                 ->toArray();

echo "\nEstado de las mesas después de la actualización:\n";
foreach ($mesasDespues as $status => $total) {
    echo "- $status: $total mesas\n";
}

echo "\n¡Actualización completada!\n";
