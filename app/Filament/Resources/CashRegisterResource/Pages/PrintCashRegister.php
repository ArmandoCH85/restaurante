<?php

namespace App\Filament\Resources\CashRegisterResource\Pages;

use App\Filament\Resources\CashRegisterResource;
use App\Models\CashRegister;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;

class PrintCashRegister extends Page
{
    protected static string $resource = CashRegisterResource::class;

    protected static string $view = 'filament.resources.cash-register-resource.pages.print-cash-register';

    // Configuración para la página de impresión
    protected static bool $shouldRegisterNavigation = false;

    public CashRegister $record;

    public function mount(CashRegister $record): void
    {
        $this->record = $record;

        // Verificar si el usuario tiene permiso para ver esta página
        $user = Auth::user();
        if (!$user->hasAnyRole(['admin', 'super_admin', 'manager']) && $user->id !== $record->opened_by && $user->id !== $record->closed_by) {
            $this->redirect($this->getResource()::getUrl('index'));
        }
    }

    public function getDenominationDetails()
    {
        // Extraer detalles de denominaciones de las observaciones
        $observations = $this->record->observations ?? '';
        $details = [];

        // Buscar la sección de desglose de denominaciones
        if (strpos($observations, 'Desglose de denominaciones:') !== false) {
            // Extraer billetes
            preg_match('/Billetes: S\/10: (\d+) \| S\/20: (\d+) \| S\/50: (\d+) \| S\/100: (\d+) \| S\/200: (\d+)/', $observations, $billetes);
            if (!empty($billetes)) {
                $details['billetes'] = [
                    '10' => (int)($billetes[1] ?? 0),
                    '20' => (int)($billetes[2] ?? 0),
                    '50' => (int)($billetes[3] ?? 0),
                    '100' => (int)($billetes[4] ?? 0),
                    '200' => (int)($billetes[5] ?? 0),
                ];
            }

            // Extraer monedas
            preg_match('/Monedas: S\/0.10: (\d+) \| S\/0.20: (\d+) \| S\/0.50: (\d+) \| S\/1: (\d+) \| S\/2: (\d+) \| S\/5: (\d+)/', $observations, $monedas);
            if (!empty($monedas)) {
                $details['monedas'] = [
                    '0.10' => (int)($monedas[1] ?? 0),
                    '0.20' => (int)($monedas[2] ?? 0),
                    '0.50' => (int)($monedas[3] ?? 0),
                    '1' => (int)($monedas[4] ?? 0),
                    '2' => (int)($monedas[5] ?? 0),
                    '5' => (int)($monedas[6] ?? 0),
                ];
            }
        }

        return $details;
    }

    public function getViewData(): array
    {
        return [
            'cashRegister' => $this->record,
            'denominationDetails' => $this->getDenominationDetails(),
            'isSupervisor' => Auth::user()->hasAnyRole(['admin', 'super_admin', 'manager']),
            'printDate' => now()->format('d/m/Y H:i:s'),
        ];
    }
}
