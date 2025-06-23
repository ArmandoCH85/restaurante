<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;

class PreBillPrintController extends Controller
{
    /**
     * Muestra la pre-cuenta optimizada para impresión térmica.
     */
    public function show(Order $order): View
    {
        // Cargar relaciones necesarias
        $order->load(['orderDetails.product', 'table', 'employee']);

        // Registrar la acción
        Log::info('🖨️ Imprimiendo pre-cuenta', [
            'order_id' => $order->id,
            'table' => $order->table?->number ?? 'N/A',
            'total' => $order->total,
            'items_count' => $order->orderDetails->count(),
            'timestamp' => now()->format('Y-m-d H:i:s')
        ]);

        return view('print.prebill-ticket', [
            'order' => $order
        ]);
    }
}
