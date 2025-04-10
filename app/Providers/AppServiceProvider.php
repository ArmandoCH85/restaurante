<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Definir el gate para permisos de administrador
        Gate::define('admin', function (User $user) {
            // Aquí definimos quién es administrador
            // Por ejemplo, usuarios con el rol admin o el ID 1 (primer usuario)
            return $user->hasRole('admin') || $user->id === 1;
        });
    }
}
