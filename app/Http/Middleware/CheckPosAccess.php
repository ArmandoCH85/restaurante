<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPosAccess
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
        
        // Verificar si el usuario tiene el permiso específico
        if (!$request->user()->can('access_pos')) {
            abort(403, 'No tienes permiso para acceder al sistema POS');
        }
        
        return $next($request);
    }
}
