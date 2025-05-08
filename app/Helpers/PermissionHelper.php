<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class PermissionHelper
{
    /**
     * Verificar si el usuario tiene un permiso específico
     * 
     * @param string $permission
     * @return bool
     */
    public static function hasPermission(string $permission): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        // Si el usuario es super_admin, siempre tiene permiso
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        // Verificar si el usuario tiene el permiso
        try {
            return $user->hasPermissionTo($permission);
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Verificar si el usuario tiene acceso a una vista personalizada
     * 
     * @param string $permission
     * @return bool
     */
    public static function hasCustomAccess(string $permission): bool
    {
        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        // Si el usuario es super_admin, siempre tiene permiso
        if ($user->hasRole('super_admin')) {
            return true;
        }
        
        // Si el usuario tiene rol delivery, no tiene acceso a ciertas vistas
        if ($user->hasRole('delivery') && !in_array($permission, ['access_tables', 'access_delivery'])) {
            return false;
        }
        
        // Verificar si el usuario tiene el permiso
        try {
            return $user->hasPermissionTo($permission);
        } catch (\Exception $e) {
            return false;
        }
    }
}
