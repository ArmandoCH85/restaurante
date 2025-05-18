<?php

namespace App\Livewire\Delivery;

use Livewire\Component;
use App\Models\Order;
use App\Models\DeliveryOrder;
use Illuminate\Support\Facades\Auth;

class DeliveryDriver extends Component
{
    public $viewingOrder;
    public $showOrderDetailsModal = false;

    protected $listeners = ['refreshDeliveryDriver' => '$refresh'];

    public function render()
    {
        $userId = Auth::id();

        // Obtener pedidos asignados al repartidor actual
        $assignedOrders = Order::where('service_type', 'delivery')
            ->whereHas('deliveryOrder', function($q) use ($userId) {
                $q->where('delivery_user_id', $userId)
                  ->whereIn('status', ['ready', 'in_transit']);
            })
            ->with(['customer', 'deliveryOrder', 'orderDetails.product'])
            ->latest()
            ->get();

        // Contar pedidos por estado
        $assignedCount = Order::where('service_type', 'delivery')
            ->whereHas('deliveryOrder', function($q) use ($userId) {
                $q->where('delivery_user_id', $userId)
                  ->where('status', 'ready');
            })
            ->count();

        $inTransitCount = Order::where('service_type', 'delivery')
            ->whereHas('deliveryOrder', function($q) use ($userId) {
                $q->where('delivery_user_id', $userId)
                  ->where('status', 'in_transit');
            })
            ->count();

        $deliveredTodayCount = Order::where('service_type', 'delivery')
            ->whereHas('deliveryOrder', function($q) use ($userId) {
                $q->where('delivery_user_id', $userId)
                  ->where('status', 'delivered');
            })
            ->whereDate('created_at', now())
            ->count();

        // Obtener historial de entregas recientes (últimos 7 días)
        $deliveryHistory = Order::where('service_type', 'delivery')
            ->whereHas('deliveryOrder', function($q) use ($userId) {
                $q->where('delivery_user_id', $userId)
                  ->where('status', 'delivered');
            })
            ->whereDate('created_at', '>=', now()->subDays(7))
            ->with(['customer', 'deliveryOrder'])
            ->latest()
            ->take(10)
            ->get();

        return view('livewire.delivery.delivery-driver', [
            'assignedOrders' => $assignedOrders,
            'assignedCount' => $assignedCount,
            'inTransitCount' => $inTransitCount,
            'deliveredTodayCount' => $deliveredTodayCount,
            'deliveryHistory' => $deliveryHistory,
        ]);
    }

    // Ver detalles de un pedido
    public function viewOrderDetails($orderId)
    {
        $userId = Auth::id();

        // Verificar que el pedido pertenece a este repartidor
        $this->viewingOrder = Order::whereHas('deliveryOrder', function($q) use ($userId) {
                $q->where('delivery_user_id', $userId);
            })
            ->with(['customer', 'deliveryOrder', 'orderDetails.product'])
            ->findOrFail($orderId);

        $this->showOrderDetailsModal = true;
    }

    // Cerrar modal de detalles
    public function closeOrderDetailsModal()
    {
        $this->reset(['viewingOrder', 'showOrderDetailsModal']);
    }

    // Actualizar estado del pedido
    public function updateStatus($orderId, $status)
    {
        $userId = Auth::id();
        $user = Auth::user();

        // Verificar que el pedido pertenece a este repartidor
        $order = Order::whereHas('deliveryOrder', function($q) use ($userId) {
                $q->where('delivery_user_id', $userId);
            })
            ->findOrFail($orderId);

        // Verificar permisos según el rol y el estado solicitado
        if ($status === 'delivered') {
            // Solo Admin, Super Admin o Cashier pueden marcar como entregado
            if (!($user->hasRole('admin') || $user->hasRole('super_admin') || $user->hasRole('cashier'))) {
                $this->dispatch('notification', [
                    'type' => 'error',
                    'title' => 'Permiso denegado',
                    'message' => 'Solo administradores y cajeros pueden finalizar pedidos de delivery'
                ]);
                return;
            }
        }

        if ($order->deliveryOrder) {
            // Actualizar estado
            $order->deliveryOrder->update([
                'status' => $status,
            ]);

            // Actualizar timestamps según el estado
            if ($status === 'in_transit') {
                $order->deliveryOrder->update(['picked_up_at' => now()]);
            } elseif ($status === 'delivered') {
                $order->deliveryOrder->update(['delivered_at' => now()]);
            }

            $this->dispatch('notification', [
                'type' => 'success',
                'title' => 'Estado Actualizado',
                'message' => 'El estado del pedido ha sido actualizado correctamente'
            ]);
        }
    }

    // Obtener nombre del estado en español
    public function getStatusName($status)
    {
        $statusNames = [
            'pending' => 'Pendiente',
            'preparing' => 'En preparación',
            'ready' => 'Listo para entrega',
            'in_transit' => 'En ruta',
            'delivered' => 'Entregado',
            'cancelled' => 'Cancelado'
        ];

        return $statusNames[$status] ?? $status;
    }
}
