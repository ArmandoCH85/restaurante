<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Order;
use App\Models\CashRegister;
use Carbon\Carbon;

echo "=== DIAGNÓSTICO DE VENTAS PASADAS ===\n\n";

// 1. Total de ventas facturadas
$totalVentas = Order::where('billed', true)->count();
echo "1. Total ventas facturadas: {$totalVentas}\n";

// 2. Ventas con caja vs sin caja
$conCaja = Order::where('billed', true)->whereNotNull('cash_register_id')->count();
$sinCaja = Order::where('billed', true)->whereNull('cash_register_id')->count();
echo "   - Con caja asignada: {$conCaja}\n";
echo "   - Sin caja asignada: {$sinCaja}\n\n";

// 3. Cajas
$cajasAbiertas = CashRegister::where('is_active', 1)->count();
$cajasCerradas = CashRegister::where('is_active', 0)->count();
echo "2. Cajas registradoras:\n";
echo "   - Abiertas: {$cajasAbiertas}\n";
echo "   - Cerradas: {$cajasCerradas}\n\n";

// 4. Ventas por fecha
echo "3. Ventas por fecha (últimos 7 días):\n";
for ($i = 6; $i >= 0; $i--) {
    $fecha = Carbon::now()->subDays($i)->startOfDay();
    $count = Order::whereDate('created_at', $fecha)->where('billed', true)->count();
    $total = Order::whereDate('created_at', $fecha)->where('billed', true)->sum('total');
    $conCajaCount = Order::whereDate('created_at', $fecha)->where('billed', true)->whereNotNull('cash_register_id')->count();
    $sinCajaCount = Order::whereDate('created_at', $fecha)->where('billed', true)->whereNull('cash_register_id')->count();
    
    $label = $i == 0 ? "HOY" : ($i == 1 ? "AYER" : "{$i} días atrás");
    echo "   {$fecha->format('Y-m-d')} ({$label}): {$count} órdenes, S/ {$total}\n";
    echo "      Con caja: {$conCajaCount}, Sin caja: {$sinCajaCount}\n";
}

echo "\n4. Estado de las cajas por fecha:\n";
$cajas = CashRegister::orderBy('opening_datetime', 'desc')->take(10)->get();
foreach ($cajas as $caja) {
    $estado = $caja->is_active ? 'ABIERTA' : 'CERRADA';
    $apertura = $caja->opening_datetime->format('Y-m-d H:i');
    $cierre = $caja->closing_datetime ? $caja->closing_datetime->format('Y-m-d H:i') : 'N/A';
    echo "   Caja #{$caja->id}: {$estado}, Apertura: {$apertura}, Cierre: {$cierre}\n";
}

echo "\n5. Probando filtro para AYER:\n";
$yesterday = Carbon::yesterday();
$endYesterday = Carbon::yesterday()->endOfDay();

// Simular la lógica actual
$includesToday = $endYesterday->isToday() || $endYesterday->isFuture();
echo "   ¿Incluye hoy? " . ($includesToday ? 'SÍ' : 'NO') . "\n";

// Consulta de ayer con cajas cerradas + sin caja
$ventasAyer = Order::whereDate('created_at', $yesterday)
    ->where('billed', true)
    ->where(function($q) {
        $q->whereHas('cashRegister', function ($subQ) {
            $subQ->where('is_active', CashRegister::STATUS_CLOSED);
        })
        ->orWhereNull('cash_register_id');
    })
    ->get();

echo "   Ventas encontradas con filtro: {$ventasAyer->count()}\n";
echo "   Total: S/ " . $ventasAyer->sum('total') . "\n";

if ($ventasAyer->count() > 0) {
    echo "\n   Detalle de ventas:\n";
    foreach ($ventasAyer as $venta) {
        $cajaInfo = $venta->cash_register_id 
            ? "Caja #{$venta->cash_register_id} (is_active: {$venta->cashRegister->is_active})" 
            : "SIN CAJA";
        echo "      - Orden #{$venta->id}: S/ {$venta->total}, {$cajaInfo}\n";
    }
}

echo "\n=== FIN DEL DIAGNÓSTICO ===\n";
