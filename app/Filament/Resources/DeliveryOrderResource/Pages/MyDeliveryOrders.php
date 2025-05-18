<?php

namespace App\Filament\Resources\DeliveryOrderResource\Pages;

use App\Filament\Resources\DeliveryOrderResource;
use App\Models\DeliveryOrder;
use App\Models\Employee;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MyDeliveryOrders extends ListRecords
{
    protected static string $resource = DeliveryOrderResource::class;

    protected static string $view = 'filament.resources.delivery-order-resource.pages.my-delivery-orders';

    public $cancelDeliveryId = null;
    public $cancellationReason = '';
    public $isCancelModalOpen = false;

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        // Obtener el usuario actual
        $user = Auth::user();

        // Buscar el empleado asociado al usuario
        $employee = Employee::where('user_id', $user->id)->first();

        if ($employee) {
            // Filtrar para mostrar solo los pedidos asignados a este repartidor
            $query->where('delivery_person_id', $employee->id);

            // Registrar para depuración
            Log::info('MyDeliveryOrders: Filtrando pedidos por repartidor', [
                'user_id' => $user->id,
                'employee_id' => $employee->id
            ]);
        }

        return $query;
    }

    public function getAssignedCount(): int
    {
        return $this->getFilteredTableQuery()
            ->where('status', 'assigned')
            ->count();
    }

    public function getInTransitCount(): int
    {
        return $this->getFilteredTableQuery()
            ->where('status', 'in_transit')
            ->count();
    }

    public function getDeliveredCount(): int
    {
        return $this->getFilteredTableQuery()
            ->where('status', 'delivered')
            ->count();
    }

    public function updateDeliveryStatus($deliveryId, $newStatus): void
    {
        $delivery = DeliveryOrder::find($deliveryId);

        if (!$delivery) {
            $this->notification()->danger('Pedido no encontrado');
            return;
        }

        $user = Auth::user();

        // Verificar permisos según el rol y el estado solicitado
        if ($newStatus === 'delivered') {
            // Solo Admin, Super Admin o Cashier pueden marcar como entregado
            if (!($user->hasRole('admin') || $user->hasRole('super_admin') || $user->hasRole('cashier'))) {
                $this->notification()->danger('Solo administradores y cajeros pueden finalizar pedidos de delivery');
                return;
            }
        }

        $previousStatus = $delivery->status;

        // Actualizar el estado según la acción
        if ($newStatus === 'in_transit') {
            $delivery->markAsInTransit();
            $this->notification()->success('Pedido marcado como En Tránsito');
        } elseif ($newStatus === 'delivered') {
            $delivery->markAsDelivered();
            $this->notification()->success('Pedido marcado como Entregado');
        }

        // Disparar evento de cambio de estado si es necesario
        if (class_exists('\App\Events\DeliveryStatusChanged')) {
            event(new \App\Events\DeliveryStatusChanged($delivery, $previousStatus));
        }
    }

    public function openCancelModal($deliveryId): void
    {
        $this->cancelDeliveryId = $deliveryId;
        $this->cancellationReason = '';
        $this->isCancelModalOpen = true;
    }

    public function closeCancelModal(): void
    {
        $this->isCancelModalOpen = false;
        $this->cancelDeliveryId = null;
        $this->cancellationReason = '';
    }

    public function confirmCancel(): void
    {
        // Validar que haya un motivo
        if (empty($this->cancellationReason)) {
            $this->notification()->danger('Debes ingresar un motivo para cancelar el pedido');
            return;
        }

        $delivery = DeliveryOrder::find($this->cancelDeliveryId);

        if (!$delivery) {
            $this->notification()->danger('Pedido no encontrado');
            $this->closeCancelModal();
            return;
        }

        $previousStatus = $delivery->status;

        // Cancelar el pedido
        $delivery->cancel($this->cancellationReason);

        // Disparar evento de cambio de estado si es necesario
        if (class_exists('\App\Events\DeliveryStatusChanged')) {
            event(new \App\Events\DeliveryStatusChanged($delivery, $previousStatus));
        }

        $this->notification()->success('Pedido cancelado correctamente');
        $this->closeCancelModal();
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
