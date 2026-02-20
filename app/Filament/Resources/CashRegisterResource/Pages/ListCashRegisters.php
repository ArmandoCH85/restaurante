<?php

namespace App\Filament\Resources\CashRegisterResource\Pages;

use App\Filament\Resources\CashRegisterResource;
use App\Models\CashRegister;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class ListCashRegisters extends ListRecords
{
    protected static string $resource = CashRegisterResource::class;

    protected function getHeaderActions(): array
    {
        // PRINCIPIO KISS: Eliminar duplicaciones
        // El Resource ya maneja todas las acciones necesarias
        // Una sola fuente de verdad para mejor UX
        return [];
    }

    public function getHeading(): string
    {
        $openRegister = CashRegister::getOpenRegister();

        if ($openRegister) {
            return 'Operaciones de Caja - Caja abierta #' . $openRegister->id;
        }

        return 'Operaciones de Caja';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ListCashRegisters\Widgets\ActiveCashRegisterStats::class,
        ];
    }

    public function getFooter(): ?View
    {
        return view('filament.cash-register.approve-script');
    }

    public function approveCashRegister($recordId)
    {
        try {
            /** @var \App\Models\User|null $user */
            $user = Auth::user();
            if (! $user || ! $user->hasAnyRole(['admin', 'super_admin', 'manager'])) {
                throw new \Exception('No tiene permisos para aprobar cajas.');
            }

            $record = CashRegister::findOrFail($recordId);
            $record->reconcile(true, 'Aprobado desde lista', $user->id);
            
            $this->dispatchBrowserEvent('notify', [
                'type' => 'success',
                'message' => 'Caja aprobada correctamente'
            ]);
            
            // Refrescar la página
            $this->redirect(request()->header('Referer'));
            
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('notify', [
                'type' => 'error',
                'message' => 'Error al aprobar: ' . $e->getMessage()
            ]);
        }
    }
}
