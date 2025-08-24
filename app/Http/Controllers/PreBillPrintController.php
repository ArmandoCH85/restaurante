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
        $user = auth()->user();
        
        // ✅ SEGURIDAD: Si es waiter, aplicar restricciones flexibles
        if ($user && $user->hasRole('waiter')) {
            // Permitir acceso si:
            // 1. La orden no tiene employee_id (orden genérica)
            // 2. La orden pertenece al waiter (employee_id coincide)
            // 3. La orden es de una mesa que está actualmente activa (para flexibilidad operativa)
            $canAccess = !$order->employee_id || 
                        $order->employee_id === $user->id ||
                        ($order->table && in_array($order->table->status, ['occupied', 'prebill']));
            
            if (!$canAccess) {
                Log::warning('⚠️ Waiter intentó acceder a pre-cuenta sin permisos', [
                    'waiter_id' => $user->id,
                    'waiter_name' => $user->name,
                    'order_id' => $order->id,
                    'order_employee_id' => $order->employee_id,
                    'table_status' => $order->table?->status
                ]);
                abort(403, 'No tienes permiso para ver esta pre-cuenta');
            }
        }
        
        // Cargar relaciones necesarias
        $order->load(['orderDetails.product', 'table', 'employee']);

        // Registrar la acción
        Log::info('🖨️ Imprimiendo pre-cuenta', [
            'order_id' => $order->id,
            'table' => $order->table?->number ?? 'N/A',
            'total' => $order->total,
            'items_count' => $order->orderDetails->count(),
            'user_role' => $user?->roles->first()?->name ?? 'guest',
            'user_id' => $user?->id,
            'timestamp' => now()->format('Y-m-d H:i:s')
        ]);

        return view('print.prebill-ticket', [
            'order' => $order
        ]);
    }
}
