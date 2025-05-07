<?php

namespace App\Livewire\Delivery;

use App\Models\DeliveryOrder;
use App\Models\Employee;
use Livewire\Component;
use Illuminate\Support\Collection;

class DeliveryTracking extends Component
{
    public Collection $activeDeliveries;
    public Collection $deliveryPersons;
    public ?string $statusFilter = null;
    public ?string $deliveryPersonFilter = null;
    public ?string $searchQuery = null;
    public bool $isDeliveryPerson = false;

    protected $listeners = ['echo:delivery-orders,delivery.status.changed' => 'refreshDeliveries'];

    public function mount(): void
    {
        // Verificar si el usuario actual tiene rol de Delivery
        $user = \Illuminate\Support\Facades\Auth::user();
        $this->isDeliveryPerson = $user && $user->roles->where('name', 'Delivery')->count() > 0;

        $this->loadDeliveryPersons();
        $this->loadDeliveries();
    }

    public function loadDeliveryPersons(): void
    {
        $this->deliveryPersons = Employee::where('position', 'Delivery')->get();
    }

    public function loadDeliveries(): void
    {
        $query = DeliveryOrder::with(['order.customer', 'deliveryPerson'])
            ->whereNotIn('status', ['delivered', 'cancelled']);

        // Verificar si el usuario actual tiene rol de Delivery
        $user = \Illuminate\Support\Facades\Auth::user();
        $isDeliveryPerson = $user && $user->roles->where('name', 'Delivery')->count() > 0;

        // Si el usuario es un repartidor, solo mostrar sus pedidos asignados
        if ($isDeliveryPerson) {
            // Obtener el empleado asociado al usuario actual
            $employee = \App\Models\Employee::where('user_id', $user->id)->first();

            if ($employee) {
                $query->where('delivery_person_id', $employee->id);

                // Si el usuario es repartidor, ignorar el filtro de repartidor
                $this->deliveryPersonFilter = null;
            }
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        // Solo aplicar el filtro de repartidor si el usuario actual NO es un repartidor
        if ($this->deliveryPersonFilter && !$isDeliveryPerson) {
            $query->where('delivery_person_id', $this->deliveryPersonFilter);
        }

        if ($this->searchQuery) {
            $query->where(function ($q) {
                $q->where('delivery_address', 'like', "%{$this->searchQuery}%")
                  ->orWhereHas('order.customer', function ($q) {
                      $q->where('name', 'like', "%{$this->searchQuery}%")
                        ->orWhere('phone', 'like', "%{$this->searchQuery}%");
                  });
            });
        }

        $this->activeDeliveries = $query->orderBy('created_at', 'desc')->get();
    }

    public function refreshDeliveries(): void
    {
        $this->loadDeliveries();
    }

    public function updatedStatusFilter(): void
    {
        $this->loadDeliveries();
    }

    public function updatedDeliveryPersonFilter(): void
    {
        $this->loadDeliveries();
    }

    public function search(): void
    {
        $this->loadDeliveries();
    }

    public function resetFilters(): void
    {
        $this->statusFilter = null;
        $this->deliveryPersonFilter = null;
        $this->searchQuery = null;
        $this->loadDeliveries();
    }

    public function assignDeliveryPerson(int $deliveryOrderId, int $employeeId): void
    {
        $deliveryOrder = DeliveryOrder::find($deliveryOrderId);
        $employee = Employee::find($employeeId);

        if ($deliveryOrder && $employee) {
            $previousStatus = $deliveryOrder->status;
            $deliveryOrder->assignDeliveryPerson($employee);

            // Disparar evento de cambio de estado
            event(new \App\Events\DeliveryStatusChanged($deliveryOrder, $previousStatus));

            $this->dispatch('notification', [
                'type' => 'success',
                'title' => 'Repartidor asignado',
                'message' => "Pedido #{$deliveryOrder->order_id} asignado a {$employee->full_name}"
            ]);

            $this->loadDeliveries();
        }
    }

    public function markAsInTransit(int $deliveryOrderId): void
    {
        $deliveryOrder = DeliveryOrder::find($deliveryOrderId);

        if ($deliveryOrder) {
            $previousStatus = $deliveryOrder->status;
            $deliveryOrder->markAsInTransit();

            // Disparar evento de cambio de estado
            event(new \App\Events\DeliveryStatusChanged($deliveryOrder, $previousStatus));

            $this->dispatch('notification', [
                'type' => 'success',
                'title' => 'Pedido en tránsito',
                'message' => "Pedido #{$deliveryOrder->order_id} marcado como en tránsito"
            ]);

            $this->loadDeliveries();
        }
    }

    public function markAsDelivered(int $deliveryOrderId): void
    {
        $deliveryOrder = DeliveryOrder::find($deliveryOrderId);

        if ($deliveryOrder) {
            $previousStatus = $deliveryOrder->status;
            $deliveryOrder->markAsDelivered();

            // Disparar evento de cambio de estado
            event(new \App\Events\DeliveryStatusChanged($deliveryOrder, $previousStatus));

            $this->dispatch('notification', [
                'type' => 'success',
                'title' => 'Pedido entregado',
                'message' => "Pedido #{$deliveryOrder->order_id} marcado como entregado"
            ]);

            $this->loadDeliveries();
        }
    }

    public function cancelDelivery(int $deliveryOrderId, ?string $reason = null): void
    {
        $deliveryOrder = DeliveryOrder::find($deliveryOrderId);

        if ($deliveryOrder) {
            $previousStatus = $deliveryOrder->status;
            $deliveryOrder->cancel($reason);

            // Disparar evento de cambio de estado
            event(new \App\Events\DeliveryStatusChanged($deliveryOrder, $previousStatus));

            $this->dispatch('notification', [
                'type' => 'success',
                'title' => 'Pedido cancelado',
                'message' => "Pedido #{$deliveryOrder->order_id} ha sido cancelado"
            ]);

            $this->loadDeliveries();
        }
    }

    public function getStatusName(string $status): string
    {
        return match ($status) {
            'pending' => 'Pendiente',
            'assigned' => 'Asignado',
            'in_transit' => 'En tránsito',
            'delivered' => 'Entregado',
            'cancelled' => 'Cancelado',
            default => $status,
        };
    }

    public function getStatusColor(string $status): string
    {
        return match ($status) {
            'pending' => 'warning',
            'assigned' => 'info',
            'in_transit' => 'primary',
            'delivered' => 'success',
            'cancelled' => 'danger',
            default => 'gray',
        };
    }

    public function render()
    {
        // Verificar si el usuario tiene acceso a este componente
        $user = \Illuminate\Support\Facades\Auth::user();
        $hasDeliveryRole = $user && $user->roles->where('name', 'Delivery')->count() > 0;

        // Registrar información para depuración
        \Illuminate\Support\Facades\Log::info('DeliveryTracking::render', [
            'user_id' => $user ? $user->id : 'no_user',
            'name' => $user ? $user->name : 'no_name',
            'roles' => $user ? $user->roles->pluck('name')->toArray() : [],
            'has_delivery_role' => $hasDeliveryRole ? 'Sí' : 'No'
        ]);

        // Si el usuario tiene rol Delivery, mostrar solo sus pedidos
        if ($hasDeliveryRole) {
            // Ya se filtran los pedidos en loadDeliveries()
            return view('livewire.delivery.delivery-tracking');
        } else {
            // Para otros usuarios, mostrar todos los pedidos
            return view('livewire.delivery.delivery-tracking');
        }
    }
}
