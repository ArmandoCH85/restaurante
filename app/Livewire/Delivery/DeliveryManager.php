<?php

namespace App\Livewire\Delivery;

use Livewire\Component;
use App\Models\Order;
use App\Models\Table;
use App\Models\User;
use App\Models\Customer;
use App\Models\DeliveryOrder;
use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;

class DeliveryManager extends Component
{
    use WithPagination;

    // Filtros
    public $statusFilter = '';
    public $deliveryUserFilter = '';
    public $dateFilter = '';

    // Para asignación de repartidor
    public $selectedOrderId;
    public $selectedDeliveryUserId;

    // Para cambio de estado
    public $orderStatusId;
    public $newStatus;

    // Para modal de detalles
    public $viewingOrder;
    public $showOrderDetailsModal = false;

    // Para modal de asignación
    public $showAssignModal = false;

    // Para modal de cambio de estado
    public $showStatusModal = false;

    protected $listeners = ['refreshDeliveryManager' => '$refresh'];

    public function mount()
    {
        $this->dateFilter = now()->format('Y-m-d');
    }

    public function render()
    {
        // Obtener usuarios con rol delivery
        $deliveryUsers = User::role('delivery')->get();

        // Consulta base para pedidos de delivery
        $query = Order::where('service_type', 'delivery')
                      ->with(['customer', 'deliveryOrder', 'orderDetails.product']);

        // Aplicar filtros
        if ($this->statusFilter) {
            $query->whereHas('deliveryOrder', function($q) {
                $q->where('status', $this->statusFilter);
            });
        }

        if ($this->deliveryUserFilter) {
            if ($this->deliveryUserFilter === 'unassigned') {
                $query->whereHas('deliveryOrder', function($q) {
                    $q->whereNull('delivery_user_id');
                });
            } else {
                $query->whereHas('deliveryOrder', function($q) {
                    $q->where('delivery_user_id', $this->deliveryUserFilter);
                });
            }
        }

        if ($this->dateFilter) {
            $query->whereDate('created_at', $this->dateFilter);
        }

        // Obtener pedidos paginados
        $deliveryOrders = $query->latest()->paginate(10);

        // Contar pedidos por estado
        $pendingCount = Order::where('service_type', 'delivery')
            ->whereHas('deliveryOrder', function($q) {
                $q->whereIn('status', ['pending', 'preparing']);
            })
            ->count();

        $preparingCount = Order::where('service_type', 'delivery')
            ->whereHas('deliveryOrder', function($q) {
                $q->where('status', 'ready');
            })
            ->count();

        $inTransitCount = Order::where('service_type', 'delivery')
            ->whereHas('deliveryOrder', function($q) {
                $q->where('status', 'in_transit');
            })
            ->count();

        $deliveredTodayCount = Order::where('service_type', 'delivery')
            ->whereHas('deliveryOrder', function($q) {
                $q->where('status', 'delivered');
            })
            ->whereDate('created_at', now())
            ->count();

        return view('livewire.delivery.delivery-manager', [
            'deliveryOrders' => $deliveryOrders,
            'deliveryUsers' => $deliveryUsers,
            'pendingCount' => $pendingCount,
            'preparingCount' => $preparingCount,
            'inTransitCount' => $inTransitCount,
            'deliveredTodayCount' => $deliveredTodayCount,
        ]);
    }

    // Abrir modal para asignar repartidor
    public function openAssignModal($orderId)
    {
        $this->selectedOrderId = $orderId;
        $this->showAssignModal = true;
    }

    // Asignar repartidor a un pedido
    public function assignDeliveryUser()
    {
        $this->validate([
            'selectedOrderId' => 'required|exists:orders,id',
            'selectedDeliveryUserId' => 'required|exists:users,id',
        ]);

        $order = Order::findOrFail($this->selectedOrderId);

        if ($order->deliveryOrder) {
            $order->deliveryOrder->update([
                'delivery_user_id' => $this->selectedDeliveryUserId,
                'assigned_at' => now(),
            ]);

            $this->dispatch('notification', [
                'type' => 'success',
                'title' => 'Repartidor Asignado',
                'message' => 'El repartidor ha sido asignado correctamente'
            ]);
        }

        $this->reset(['selectedOrderId', 'selectedDeliveryUserId', 'showAssignModal']);
    }

    // Abrir modal para cambiar estado
    public function openStatusModal($orderId)
    {
        $this->orderStatusId = $orderId;
        $this->showStatusModal = true;
    }

    // Cambiar estado de un pedido
    public function updateOrderStatus()
    {
        $this->validate([
            'orderStatusId' => 'required|exists:orders,id',
            'newStatus' => 'required|in:pending,preparing,ready,in_transit,delivered,cancelled',
        ]);

        $order = Order::findOrFail($this->orderStatusId);

        if ($order->deliveryOrder) {
            // Actualizar estado
            $order->deliveryOrder->update([
                'status' => $this->newStatus,
            ]);

            // Actualizar timestamps según el estado
            if ($this->newStatus === 'in_transit') {
                $order->deliveryOrder->update(['picked_up_at' => now()]);
            } elseif ($this->newStatus === 'delivered') {
                $order->deliveryOrder->update(['delivered_at' => now()]);
            }

            $this->dispatch('notification', [
                'type' => 'success',
                'title' => 'Estado Actualizado',
                'message' => 'El estado del pedido ha sido actualizado correctamente'
            ]);
        }

        $this->reset(['orderStatusId', 'newStatus', 'showStatusModal']);
    }

    // Ver detalles de un pedido
    public function viewOrderDetails($orderId)
    {
        $this->viewingOrder = Order::with(['customer', 'deliveryOrder', 'orderDetails.product'])
            ->findOrFail($orderId);
        $this->showOrderDetailsModal = true;
    }

    // Cerrar modal de detalles
    public function closeOrderDetailsModal()
    {
        $this->reset(['viewingOrder', 'showOrderDetailsModal']);
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

    // Resetear filtros
    public function resetFilters()
    {
        $this->reset(['statusFilter', 'deliveryUserFilter']);
        $this->dateFilter = now()->format('Y-m-d');
    }

    // Imprimir ticket de pedido
    public function printOrder($orderId)
    {
        // Aquí iría la lógica para imprimir el ticket
        // Por ahora solo mostramos una notificación
        $this->dispatch('notification', [
            'type' => 'success',
            'title' => 'Impresión',
            'message' => 'Ticket de pedido enviado a impresión'
        ]);
    }
}
