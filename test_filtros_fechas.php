<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Order;
use App\Models\CashRegister;
use Carbon\Carbon;

echo "=== PROBANDO FILTROS PARA FECHAS CON VENTAS ===\n\n";

// Fecha 1: 08 de octubre (3 días atrás)
$fecha1 = Carbon::parse('2025-10-08');
$endFecha1 = $fecha1->copy()->endOfDay();
$includesToday1 = $endFecha1->isToday() || $endFecha1->isFuture();

echo "1. FECHA: 08 de Octubre (3 días atrás)\n";
echo "   ¿Incluye hoy? " . ($includesToday1 ? 'SÍ' : 'NO') . "\n";

$ventas1 = Order::whereDate('created_at', $fecha1)
    ->where('billed', true)
    ->where(function($q) {
        $q->whereHas('cashRegister', function ($subQ) {
            $subQ->where('is_active', CashRegister::STATUS_CLOSED);
        })
        ->orWhereNull('cash_register_id');
    })
    ->get();

echo "   Ventas encontradas: {$ventas1->count()}\n";
echo "   Total: S/ " . $ventas1->sum('total') . "\n\n";

// Fecha 2: 09 de octubre (2 días atrás)
$fecha2 = Carbon::parse('2025-10-09');
$endFecha2 = $fecha2->copy()->endOfDay();
$includesToday2 = $endFecha2->isToday() || $endFecha2->isFuture();

echo "2. FECHA: 09 de Octubre (2 días atrás)\n";
echo "   ¿Incluye hoy? " . ($includesToday2 ? 'SÍ' : 'NO') . "\n";

$ventas2 = Order::whereDate('created_at', $fecha2)
    ->where('billed', true)
    ->where(function($q) {
        $q->whereHas('cashRegister', function ($subQ) {
            $subQ->where('is_active', CashRegister::STATUS_CLOSED);
        })
        ->orWhereNull('cash_register_id');
    })
    ->get();

echo "   Ventas encontradas: {$ventas2->count()}\n";
echo "   Total: S/ " . $ventas2->sum('total') . "\n\n";

// Fecha 3: HOY (11 de octubre)
$hoy = Carbon::today();
$endHoy = $hoy->copy()->endOfDay();
$includ esTodayHoy = $endHoy->isToday() || $endHoy->isFuture();

echo "3. FECHA: 11 de Octubre (HOY)\n";
echo "   ¿Incluye hoy? " . ($includ esTodayHoy ? 'SÍ' : 'NO') . "\n";

$ventasHoy = Order::whereDate('created_at', $hoy)
    ->where('billed', true)
    ->where(function($q) {
        $q->whereHas('cashRegister', function ($subQ) {
            $subQ->where('is_active', CashRegister::STATUS_OPEN);
        })
        ->orWhereNull('cash_register_id');
    })
    ->get();

echo "   Ventas encontradas: {$ventasHoy->count()}\n";
echo "   Total: S/ " . $ventasHoy->sum('total') . "\n\n";

echo "=== FIN ===\n";
