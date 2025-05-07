<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RestrictMenuByRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Si el usuario tiene el rol "delivery", solo permitir acceso a rutas específicas
        if ($user && $user->roles->where('name', 'delivery')->count() > 0) {
            $allowedRoutes = [
                '/',
                'admin',
                'admin/login',
                'admin/logout',
                'admin/profile',
                'admin/resources/delivery-orders',
                'admin/resources/delivery-orders/create',
                'tables',
                'delivery-tracking',
                'pos', // Permitir acceso a la ruta del POS para ver detalles del pedido
                'delivery/order', // Permitir acceso a la ruta de detalles del pedido
                'delivery/my-orders', // Permitir acceso a la ruta de pedidos del repartidor
                'delivery' // Permitir acceso a todas las rutas que comiencen con delivery
            ];

            $currentPath = $request->path();

            // Permitir acceso a rutas específicas o que comiencen con rutas permitidas
            $isAllowed = false;
            foreach ($allowedRoutes as $route) {
                if ($currentPath === $route || str_starts_with($currentPath, $route . '/')) {
                    $isAllowed = true;
                    break;
                }
            }

            // Registrar información para depuración
            Log::info('RestrictMenuByRole middleware', [
                'user_id' => $user->id,
                'name' => $user->name,
                'roles' => $user->roles->pluck('name')->toArray(),
                'current_path' => $currentPath,
                'is_allowed' => $isAllowed ? 'Sí' : 'No'
            ]);

            // Si es la ruta raíz o admin, redirigir directamente a /tables
            if ($currentPath === '' || $currentPath === '/' || $currentPath === 'admin') {
                return redirect('/tables');
            }

            // Si no es una ruta permitida, redirigir directamente a /tables
            if (!$isAllowed) {
                return redirect('/tables');
            }
        }

        return $next($request);
    }
}
