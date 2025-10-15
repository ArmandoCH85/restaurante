<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\Purchase;
use App\Filament\Resources\PurchaseResource;

// Configurar la aplicación Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== SIMULACIÓN DE INTERFAZ FILAMENT PARA COMPRAS ===\n\n";

// 1. Simular exactamente lo que hace Filament
echo "1. SIMULANDO CONSULTA EXACTA DE FILAMENT:\n";

// Habilitar log de consultas
DB::enableQueryLog();

// Simular la consulta que hace Filament con paginación
$query = Purchase::query();

// Aplicar ordenamiento por defecto (como en Filament)
$query->orderBy('id', 'desc');

// Obtener con relaciones (como hace Filament)
$purchases = $query->with(['supplier', 'warehouse'])->paginate(15);

$queries = DB::getQueryLog();
echo "Consultas ejecutadas por Filament:\n";
foreach ($queries as $index => $query) {
    echo "  " . ($index + 1) . ". " . $query['query'] . "\n";
}

echo "\nResultados obtenidos: " . $purchases->count() . " de " . $purchases->total() . " total\n";

// 2. Verificar cada registro individualmente
echo "\n2. VERIFICANDO CADA REGISTRO:\n";
foreach ($purchases as $index => $purchase) {
    echo "Registro " . ($index + 1) . ":\n";
    echo "  ID: {$purchase->id}\n";
    echo "  Fecha: {$purchase->purchase_date}\n";
    echo "  Proveedor: " . ($purchase->supplier ? $purchase->supplier->business_name : 'N/A') . "\n";
    echo "  Almacén: " . ($purchase->warehouse ? $purchase->warehouse->name : 'N/A') . "\n";
    echo "  Documento: {$purchase->document_number}\n";
    echo "  Total: S/ {$purchase->total}\n";
    echo "  Estado: {$purchase->status}\n";
    echo "  ---\n";
}

// 3. Verificar si hay problemas con la configuración de Filament
echo "\n3. VERIFICANDO CONFIGURACIÓN DE FILAMENT:\n";

// Verificar el modelo configurado
$resourceModel = PurchaseResource::getModel();
echo "Modelo configurado: " . $resourceModel . "\n";

// Verificar si hay algún scope global aplicado
$modelInstance = new $resourceModel;
echo "Scopes globales aplicados: ";
$globalScopes = $modelInstance->getGlobalScopes();
if (empty($globalScopes)) {
    echo "Ninguno\n";
} else {
    echo implode(', ', array_keys($globalScopes)) . "\n";
}

// 4. Verificar la tabla directamente
echo "\n4. VERIFICACIÓN DIRECTA DE LA TABLA:\n";
$directQuery = DB::table('purchases')
    ->select('id', 'purchase_date', 'document_number', 'total', 'status')
    ->orderBy('id', 'desc')
    ->get();

echo "Registros directos de la tabla:\n";
foreach ($directQuery as $record) {
    echo "  ID: {$record->id}, Fecha: {$record->purchase_date}, Doc: {$record->document_number}, Total: {$record->total}\n";
}

// 5. Verificar si hay algún problema con soft deletes
echo "\n5. VERIFICANDO SOFT DELETES:\n";
$withTrashed = Purchase::withTrashed()->count();
$onlyTrashed = Purchase::onlyTrashed()->count();
$normal = Purchase::count();

echo "Registros normales: $normal\n";
echo "Registros eliminados: $onlyTrashed\n";
echo "Total con eliminados: $withTrashed\n";

// 6. Verificar si hay algún problema con las relaciones
echo "\n6. VERIFICANDO RELACIONES PROBLEMÁTICAS:\n";

DB::enableQueryLog();
DB::getQueryLog(); // Limpiar

// Hacer una consulta con eager loading como Filament
$purchasesWithRelations = Purchase::with(['supplier', 'warehouse', 'details'])->get();

$relationQueries = DB::getQueryLog();
echo "Consultas para relaciones:\n";
foreach ($relationQueries as $index => $query) {
    echo "  " . ($index + 1) . ". " . $query['query'] . "\n";
}

echo "Registros con relaciones: " . $purchasesWithRelations->count() . "\n";

// 7. Verificar si hay duplicados por alguna relación específica
echo "\n7. VERIFICANDO DUPLICADOS POR RELACIONES:\n";

$purchasesWithDetails = Purchase::with('details')->get();
foreach ($purchasesWithDetails as $purchase) {
    $detailsCount = $purchase->details->count();
    echo "Compra ID {$purchase->id}: {$detailsCount} detalles\n";
    
    if ($detailsCount > 1) {
        echo "  ⚠️  Esta compra tiene múltiples detalles, podría causar duplicación visual\n";
    }
}

// 8. Simular la respuesta JSON que devolvería Filament
echo "\n8. SIMULANDO RESPUESTA JSON DE FILAMENT:\n";
$filamentData = [];
foreach ($purchases as $purchase) {
    $filamentData[] = [
        'id' => $purchase->id,
        'purchase_date' => $purchase->purchase_date->format('Y-m-d'),
        'supplier_name' => $purchase->supplier ? $purchase->supplier->business_name : null,
        'warehouse_name' => $purchase->warehouse ? $purchase->warehouse->name : null,
        'document_number' => $purchase->document_number,
        'total' => $purchase->total,
        'status' => $purchase->status,
    ];
}

echo "Datos que devolvería Filament:\n";
echo json_encode($filamentData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";

echo "\n=== FIN DE LA SIMULACIÓN ===\n";