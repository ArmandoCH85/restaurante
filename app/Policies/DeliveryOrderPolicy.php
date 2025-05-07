<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DeliveryOrder;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Log;

class DeliveryOrderPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Permitir acceso a usuarios con permiso específico o rol Delivery
        $hasPermission = $user->can('view_any_delivery::order');
        $hasDeliveryRole = $user->roles->where('name', 'Delivery')->count() > 0;
        
        Log::info('DeliveryOrderPolicy::viewAny', [
            'user_id' => $user->id,
            'name' => $user->name,
            'roles' => $user->roles->pluck('name')->toArray(),
            'has_permission' => $hasPermission ? 'Sí' : 'No',
            'has_delivery_role' => $hasDeliveryRole ? 'Sí' : 'No',
            'result' => ($hasPermission || $hasDeliveryRole) ? 'Permitido' : 'Denegado'
        ]);
        
        return $hasPermission || $hasDeliveryRole;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, DeliveryOrder $deliveryOrder): bool
    {
        // Permitir acceso a usuarios con permiso específico
        $hasPermission = $user->can('view_delivery::order');
        
        // Permitir acceso a repartidores solo para sus propios pedidos
        $hasDeliveryRole = $user->roles->where('name', 'Delivery')->count() > 0;
        $isOwnOrder = false;
        
        if ($hasDeliveryRole) {
            $employee = \App\Models\Employee::where('user_id', $user->id)->first();
            $isOwnOrder = $employee && $deliveryOrder->delivery_person_id === $employee->id;
        }
        
        Log::info('DeliveryOrderPolicy::view', [
            'user_id' => $user->id,
            'delivery_order_id' => $deliveryOrder->id,
            'has_permission' => $hasPermission ? 'Sí' : 'No',
            'has_delivery_role' => $hasDeliveryRole ? 'Sí' : 'No',
            'is_own_order' => $isOwnOrder ? 'Sí' : 'No',
            'result' => ($hasPermission || ($hasDeliveryRole && $isOwnOrder)) ? 'Permitido' : 'Denegado'
        ]);
        
        return $hasPermission || ($hasDeliveryRole && $isOwnOrder);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Solo permitir a usuarios con permiso específico (no a repartidores)
        $hasPermission = $user->can('create_delivery::order');
        $hasDeliveryRole = $user->roles->where('name', 'Delivery')->count() > 0;
        
        Log::info('DeliveryOrderPolicy::create', [
            'user_id' => $user->id,
            'has_permission' => $hasPermission ? 'Sí' : 'No',
            'has_delivery_role' => $hasDeliveryRole ? 'Sí' : 'No',
            'result' => ($hasPermission && !$hasDeliveryRole) ? 'Permitido' : 'Denegado'
        ]);
        
        return $hasPermission && !$hasDeliveryRole;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, DeliveryOrder $deliveryOrder): bool
    {
        // Permitir acceso a usuarios con permiso específico
        $hasPermission = $user->can('update_delivery::order');
        
        // Permitir acceso a repartidores solo para sus propios pedidos
        $hasDeliveryRole = $user->roles->where('name', 'Delivery')->count() > 0;
        $isOwnOrder = false;
        
        if ($hasDeliveryRole) {
            $employee = \App\Models\Employee::where('user_id', $user->id)->first();
            $isOwnOrder = $employee && $deliveryOrder->delivery_person_id === $employee->id;
        }
        
        Log::info('DeliveryOrderPolicy::update', [
            'user_id' => $user->id,
            'delivery_order_id' => $deliveryOrder->id,
            'has_permission' => $hasPermission ? 'Sí' : 'No',
            'has_delivery_role' => $hasDeliveryRole ? 'Sí' : 'No',
            'is_own_order' => $isOwnOrder ? 'Sí' : 'No',
            'result' => ($hasPermission || ($hasDeliveryRole && $isOwnOrder)) ? 'Permitido' : 'Denegado'
        ]);
        
        return $hasPermission || ($hasDeliveryRole && $isOwnOrder);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, DeliveryOrder $deliveryOrder): bool
    {
        // Solo permitir a usuarios con permiso específico (no a repartidores)
        $hasPermission = $user->can('delete_delivery::order');
        $hasDeliveryRole = $user->roles->where('name', 'Delivery')->count() > 0;
        
        Log::info('DeliveryOrderPolicy::delete', [
            'user_id' => $user->id,
            'delivery_order_id' => $deliveryOrder->id,
            'has_permission' => $hasPermission ? 'Sí' : 'No',
            'has_delivery_role' => $hasDeliveryRole ? 'Sí' : 'No',
            'result' => ($hasPermission && !$hasDeliveryRole) ? 'Permitido' : 'Denegado'
        ]);
        
        return $hasPermission && !$hasDeliveryRole;
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        // Solo permitir a usuarios con permiso específico (no a repartidores)
        $hasPermission = $user->can('delete_any_delivery::order');
        $hasDeliveryRole = $user->roles->where('name', 'Delivery')->count() > 0;
        
        Log::info('DeliveryOrderPolicy::deleteAny', [
            'user_id' => $user->id,
            'has_permission' => $hasPermission ? 'Sí' : 'No',
            'has_delivery_role' => $hasDeliveryRole ? 'Sí' : 'No',
            'result' => ($hasPermission && !$hasDeliveryRole) ? 'Permitido' : 'Denegado'
        ]);
        
        return $hasPermission && !$hasDeliveryRole;
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, DeliveryOrder $deliveryOrder): bool
    {
        // Solo permitir a usuarios con permiso específico (no a repartidores)
        $hasPermission = $user->can('force_delete_delivery::order');
        $hasDeliveryRole = $user->roles->where('name', 'Delivery')->count() > 0;
        
        Log::info('DeliveryOrderPolicy::forceDelete', [
            'user_id' => $user->id,
            'delivery_order_id' => $deliveryOrder->id,
            'has_permission' => $hasPermission ? 'Sí' : 'No',
            'has_delivery_role' => $hasDeliveryRole ? 'Sí' : 'No',
            'result' => ($hasPermission && !$hasDeliveryRole) ? 'Permitido' : 'Denegado'
        ]);
        
        return $hasPermission && !$hasDeliveryRole;
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        // Solo permitir a usuarios con permiso específico (no a repartidores)
        $hasPermission = $user->can('force_delete_any_delivery::order');
        $hasDeliveryRole = $user->roles->where('name', 'Delivery')->count() > 0;
        
        Log::info('DeliveryOrderPolicy::forceDeleteAny', [
            'user_id' => $user->id,
            'has_permission' => $hasPermission ? 'Sí' : 'No',
            'has_delivery_role' => $hasDeliveryRole ? 'Sí' : 'No',
            'result' => ($hasPermission && !$hasDeliveryRole) ? 'Permitido' : 'Denegado'
        ]);
        
        return $hasPermission && !$hasDeliveryRole;
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, DeliveryOrder $deliveryOrder): bool
    {
        // Solo permitir a usuarios con permiso específico (no a repartidores)
        $hasPermission = $user->can('restore_delivery::order');
        $hasDeliveryRole = $user->roles->where('name', 'Delivery')->count() > 0;
        
        Log::info('DeliveryOrderPolicy::restore', [
            'user_id' => $user->id,
            'delivery_order_id' => $deliveryOrder->id,
            'has_permission' => $hasPermission ? 'Sí' : 'No',
            'has_delivery_role' => $hasDeliveryRole ? 'Sí' : 'No',
            'result' => ($hasPermission && !$hasDeliveryRole) ? 'Permitido' : 'Denegado'
        ]);
        
        return $hasPermission && !$hasDeliveryRole;
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        // Solo permitir a usuarios con permiso específico (no a repartidores)
        $hasPermission = $user->can('restore_any_delivery::order');
        $hasDeliveryRole = $user->roles->where('name', 'Delivery')->count() > 0;
        
        Log::info('DeliveryOrderPolicy::restoreAny', [
            'user_id' => $user->id,
            'has_permission' => $hasPermission ? 'Sí' : 'No',
            'has_delivery_role' => $hasDeliveryRole ? 'Sí' : 'No',
            'result' => ($hasPermission && !$hasDeliveryRole) ? 'Permitido' : 'Denegado'
        ]);
        
        return $hasPermission && !$hasDeliveryRole;
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, DeliveryOrder $deliveryOrder): bool
    {
        // Solo permitir a usuarios con permiso específico (no a repartidores)
        $hasPermission = $user->can('replicate_delivery::order');
        $hasDeliveryRole = $user->roles->where('name', 'Delivery')->count() > 0;
        
        Log::info('DeliveryOrderPolicy::replicate', [
            'user_id' => $user->id,
            'delivery_order_id' => $deliveryOrder->id,
            'has_permission' => $hasPermission ? 'Sí' : 'No',
            'has_delivery_role' => $hasDeliveryRole ? 'Sí' : 'No',
            'result' => ($hasPermission && !$hasDeliveryRole) ? 'Permitido' : 'Denegado'
        ]);
        
        return $hasPermission && !$hasDeliveryRole;
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        // Solo permitir a usuarios con permiso específico (no a repartidores)
        $hasPermission = $user->can('reorder_delivery::order');
        $hasDeliveryRole = $user->roles->where('name', 'Delivery')->count() > 0;
        
        Log::info('DeliveryOrderPolicy::reorder', [
            'user_id' => $user->id,
            'has_permission' => $hasPermission ? 'Sí' : 'No',
            'has_delivery_role' => $hasDeliveryRole ? 'Sí' : 'No',
            'result' => ($hasPermission && !$hasDeliveryRole) ? 'Permitido' : 'Denegado'
        ]);
        
        return $hasPermission && !$hasDeliveryRole;
    }
}
