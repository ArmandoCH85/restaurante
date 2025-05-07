<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DeliveryRedirectController extends Controller
{
    /**
     * Redirige a los usuarios según su rol
     */
    public function redirectBasedOnRole()
    {
        $user = Auth::user();

        // Registrar información para depuración
        Log::info('DeliveryRedirectController::redirectBasedOnRole', [
            'user_id' => $user ? $user->id : 'no_user',
            'name' => $user ? $user->name : 'no_name',
            'roles' => $user ? $user->roles->pluck('name')->toArray() : [],
            'is_authenticated' => Auth::check() ? 'Sí' : 'No'
        ]);

        // Si el usuario tiene rol Delivery, redirigir a la página de delivery
        if ($user && $user->roles->where('name', 'Delivery')->count() > 0) {
            // Registrar la URL a la que se redirige
            Log::info('Redirigiendo a usuario Delivery', [
                'url' => '/tables',
                'user_id' => $user->id,
                'name' => $user->name
            ]);

            // Redirigir a la página de pedidos de delivery
            return redirect('/tables');
        }

        // Para otros usuarios, redirigir al dashboard
        return redirect('/admin');
    }
}
