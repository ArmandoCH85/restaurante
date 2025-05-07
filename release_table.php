<?php

// Script para liberar manualmente una mesa

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Obtener todas las mesas
$tables = \App\Models\Table::where('status', '!=', \App\Models\Table::STATUS_AVAILABLE)->get();

echo "Mesas ocupadas encontradas: " . $tables->count() . "\n";

foreach ($tables as $table) {
    echo "Procesando mesa #{$table->number} (ID: {$table->id}) - Estado actual: {$table->status}\n";

    // Buscar órdenes activas para esta mesa
    $activeOrders = \App\Models\Order::where('table_id', $table->id)
        ->where(function($query) {
            $query->where('status', '!=', \App\Models\Order::STATUS_COMPLETED)
                  ->where('status', '!=', \App\Models\Order::STATUS_CANCELLED);
        })
        ->where('billed', false)
        ->get();

    echo "  Órdenes activas encontradas: " . $activeOrders->count() . "\n";

    foreach ($activeOrders as $order) {
        echo "  Orden #{$order->id} - Estado: {$order->status} - Facturada: " . ($order->billed ? 'Sí' : 'No') . "\n";

        // Marcar la orden como facturada
        $order->billed = true;
        $order->status = \App\Models\Order::STATUS_COMPLETED;
        $order->save();

        echo "  Orden #{$order->id} marcada como facturada y completada.\n";
    }

    // Liberar la mesa
    $table->status = \App\Models\Table::STATUS_AVAILABLE;
    $table->save();

    echo "  Mesa {$table->number} liberada. Nuevo estado: {$table->status}\n";

    // Limpiar la sesión para esta mesa
    $sessionKey = "cart_table_{$table->id}";
    \Illuminate\Support\Facades\Session::forget($sessionKey);

    echo "  Sesión del carrito para la mesa {$table->number} limpiada.\n";
}

echo "Proceso completado.\n";
exit;


