<?php

namespace App\Http\Responses;

use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse extends \Filament\Http\Responses\Auth\LoginResponse
{
    public function toResponse($request): RedirectResponse|Redirector
    {
        // Si el usuario tiene el rol de mesero
        if (auth()->user()->hasRole('waiter')) {
            return redirect()->to('/admin/mapa-mesas');
        }

        return parent::toResponse($request);
    }
}
