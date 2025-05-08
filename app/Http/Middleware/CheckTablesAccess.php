<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTablesAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // Si el usuario no está autenticado, redirigir al login
        if (!$request->user()) {
            return redirect()->route('filament.admin.auth.login');
        }
        
        // Si el usuario es super_admin, permitir acceso
        if ($request->user()->hasRole('super_admin')) {
            return $next($request);
        }
        
        // Si el usuario tiene rol de delivery, permitir acceso (ya que necesitan ver las mesas para gestionar pedidos)
        if ($request->user()->hasRole('delivery')) {
            return $next($request);
        }
        
        // Verificar si el usuario tiene el permiso específico
        if (!$request->user()->can('access_tables')) {
            abort(403, 'No tienes permiso para acceder al mapa de mesas');
        }
        
        return $next($request);
    }
}
