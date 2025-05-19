<?php

namespace App\Policies;

use App\Models\Quotation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class QuotationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('view_any_quotation') || 
               $user->hasRole(['super_admin', 'admin', 'manager', 'cashier']);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Quotation $quotation): bool
    {
        return $user->hasPermissionTo('view_quotation') || 
               $user->hasRole(['super_admin', 'admin', 'manager', 'cashier']) ||
               $user->id === $quotation->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('create_quotation') || 
               $user->hasRole(['super_admin', 'admin', 'manager', 'cashier']);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Quotation $quotation): bool
    {
        // No permitir editar cotizaciones convertidas
        if ($quotation->isConverted()) {
            return false;
        }
        
        return $user->hasPermissionTo('update_quotation') || 
               $user->hasRole(['super_admin', 'admin', 'manager']) ||
               ($user->hasRole('cashier') && $user->id === $quotation->user_id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Quotation $quotation): bool
    {
        // No permitir eliminar cotizaciones convertidas
        if ($quotation->isConverted()) {
            return false;
        }
        
        return $user->hasPermissionTo('delete_quotation') || 
               $user->hasRole(['super_admin', 'admin']);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Quotation $quotation): bool
    {
        return $user->hasPermissionTo('restore_quotation') || 
               $user->hasRole(['super_admin', 'admin']);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Quotation $quotation): bool
    {
        return $user->hasPermissionTo('force_delete_quotation') || 
               $user->hasRole(['super_admin']);
    }
    
    /**
     * Determine whether the user can approve the quotation.
     */
    public function approve(User $user, Quotation $quotation): bool
    {
        // Solo se pueden aprobar cotizaciones enviadas y no convertidas
        if (!$quotation->isSent() || $quotation->isConverted()) {
            return false;
        }
        
        return $user->hasPermissionTo('approve_quotation') || 
               $user->hasRole(['super_admin', 'admin', 'manager']);
    }
    
    /**
     * Determine whether the user can reject the quotation.
     */
    public function reject(User $user, Quotation $quotation): bool
    {
        // Solo se pueden rechazar cotizaciones enviadas y no convertidas
        if (!$quotation->isSent() || $quotation->isConverted()) {
            return false;
        }
        
        return $user->hasPermissionTo('reject_quotation') || 
               $user->hasRole(['super_admin', 'admin', 'manager']);
    }
    
    /**
     * Determine whether the user can send the quotation.
     */
    public function send(User $user, Quotation $quotation): bool
    {
        // Solo se pueden enviar cotizaciones en borrador y no convertidas
        if (!$quotation->isDraft() || $quotation->isConverted()) {
            return false;
        }
        
        return $user->hasPermissionTo('send_quotation') || 
               $user->hasRole(['super_admin', 'admin', 'manager', 'cashier']);
    }
    
    /**
     * Determine whether the user can convert the quotation to an order.
     */
    public function convertToOrder(User $user, Quotation $quotation): bool
    {
        // Solo se pueden convertir cotizaciones aprobadas y no convertidas
        if (!$quotation->isApproved() || $quotation->isConverted()) {
            return false;
        }
        
        return $user->hasPermissionTo('convert_quotation_to_order') || 
               $user->hasRole(['super_admin', 'admin', 'manager', 'cashier']);
    }
}
