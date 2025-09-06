<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\ForceUtf8Encoding::class,
            \App\Http\Middleware\RestrictMenuByRole::class,
        ]);

        // Registrar los middlewares de Spatie Laravel Permission
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'pos.access' => \App\Http\Middleware\CheckPosAccess::class,
            'tables.access' => \App\Http\Middleware\CheckTablesAccess::class,
            'delivery.access' => \App\Http\Middleware\CheckDeliveryAccess::class,
            'tables.maintenance.access' => \App\Http\Middleware\CheckTablesMaintenanceAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
