<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\Purchase;

// Configurar la aplicación Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== ANÁLISIS DE DUPLICACIÓN EN COMPRAS ===\n\n";

// 1. Verificar registros únicos en la tabla purchases
echo "1. VERIFICANDO REGISTROS EN LA TABLA PURCHASES:\n";
$totalPurchases = DB::table('purchases')->count();
echo "Total de registros en purchases: $totalPurchases\n";

$uniquePurchases = DB::table('purchases')->distinct('id')->count();
echo "Registros únicos por ID: $uniquePurchases\n";

if ($totalPurchases !== $uniquePurchases) {
    echo "⚠️  PROBLEMA: Hay duplicación de IDs en la tabla purchases!\n";
} else {
    echo "✅ No hay duplicación de IDs en la tabla purchases\n";
}

// 2. Verificar duplicados por otros campos
echo "\n2. VERIFICANDO DUPLICADOS POR DOCUMENTO:\n";
$duplicateDocuments = DB::table('purchases')
    ->select('document_number', 'supplier_id', DB::raw('COUNT(*) as count'))
    ->whereNotNull('document_number')
    ->groupBy('document_number', 'supplier_id')
    ->having('count', '>', 1)
    ->get();

if ($duplicateDocuments->count() > 0) {
    echo "⚠️  DOCUMENTOS DUPLICADOS ENCONTRADOS:\n";
    foreach ($duplicateDocuments as $duplicate) {
        echo "- Documento: {$duplicate->document_number}, Proveedor: {$duplicate->supplier_id}, Cantidad: {$duplicate->count}\n";
    }
} else {
    echo "✅ No hay documentos duplicados\n";
}

// 3. Verificar las consultas que hace Filament
echo "\n3. SIMULANDO CONSULTA DE FILAMENT:\n";

// Habilitar log de consultas
DB::enableQueryLog();

// Simular la consulta que hace Filament
$purchases = Purchase::with(['supplier', 'warehouse'])
    ->orderBy('id', 'desc')
    ->paginate(15);

$queries = DB::getQueryLog();
echo "Número de consultas ejecutadas: " . count($queries) . "\n";

foreach ($queries as $index => $query) {
    echo "Consulta " . ($index + 1) . ": " . $query['query'] . "\n";
    if (!empty($query['bindings'])) {
        echo "Bindings: " . json_encode($query['bindings']) . "\n";
    }
    echo "Tiempo: " . $query['time'] . "ms\n\n";
}

// 4. Verificar las relaciones
echo "4. VERIFICANDO RELACIONES:\n";

$purchaseWithRelations = Purchase::with(['supplier', 'warehouse', 'details'])->first();
if ($purchaseWithRelations) {
    echo "Compra ID: {$purchaseWithRelations->id}\n";
    echo "Proveedor: " . ($purchaseWithRelations->supplier ? $purchaseWithRelations->supplier->business_name : 'N/A') . "\n";
    echo "Almacén: " . ($purchaseWithRelations->warehouse ? $purchaseWithRelations->warehouse->name : 'N/A') . "\n";
    echo "Detalles: " . $purchaseWithRelations->details->count() . "\n";
}

// 5. Verificar si hay problemas con joins
echo "\n5. VERIFICANDO JOINS PROBLEMÁTICOS:\n";

DB::enableQueryLog();
DB::getQueryLog(); // Limpiar log

$purchasesWithJoins = DB::table('purchases')
    ->leftJoin('suppliers', 'purchases.supplier_id', '=', 'suppliers.id')
    ->leftJoin('warehouses', 'purchases.warehouse_id', '=', 'warehouses.id')
    ->select('purchases.*', 'suppliers.business_name', 'warehouses.name as warehouse_name')
    ->get();

$joinQueries = DB::getQueryLog();
echo "Consulta con joins: " . $joinQueries[0]['query'] . "\n";
echo "Registros obtenidos: " . $purchasesWithJoins->count() . "\n";

// Comparar con el total de purchases
if ($purchasesWithJoins->count() !== $totalPurchases) {
    echo "⚠️  PROBLEMA: Los joins están devolviendo más registros de los esperados!\n";
    echo "Diferencia: " . ($purchasesWithJoins->count() - $totalPurchases) . " registros extra\n";
} else {
    echo "✅ Los joins están funcionando correctamente\n";
}

// 6. Verificar registros recientes
echo "\n6. ÚLTIMAS 5 COMPRAS:\n";
$recentPurchases = Purchase::with(['supplier', 'warehouse'])
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get();

foreach ($recentPurchases as $purchase) {
    echo "ID: {$purchase->id}, Fecha: {$purchase->purchase_date}, ";
    echo "Proveedor: " . ($purchase->supplier ? $purchase->supplier->business_name : 'N/A') . ", ";
    echo "Total: S/ {$purchase->total}\n";
}

echo "\n=== FIN DEL ANÁLISIS ===\n";