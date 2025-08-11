<?php

namespace App\Http\Responses;

use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\LogoutResponse as BaseLogoutResponse;
use Illuminate\Http\RedirectResponse;

class LogoutResponse extends BaseLogoutResponse
{
    public function toResponse($request): RedirectResponse
    {
        // Detectar el panel desde el nombre de la ruta (filament.{panel}.auth.logout)
        $routeName = (string) ($request->route()?->getName() ?? '');
        if (preg_match('/^filament\.([^.]+)\./', $routeName, $m)) {
            $panelId = $m[1];
            if (! empty($panelId) && $panelId !== 'admin') {
                return redirect()->to("/{$panelId}");
            }
        }

        // Fallback al comportamiento por defecto (redirige al login del admin)
        return parent::toResponse($request);
    }
}
