<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashRegisterPrintController extends Controller
{
    public function print(CashRegister $cashRegister)
    {
        // Verificar si el usuario tiene permiso para ver esta página
        $user = Auth::user();
        if (!$user->hasAnyRole(['admin', 'super_admin', 'manager']) && $user->id !== $cashRegister->opened_by && $user->id !== $cashRegister->closed_by) {
            abort(403, 'No tienes permiso para ver este informe');
        }

        // Extraer detalles de denominaciones
        $denominationDetails = $this->getDenominationDetails($cashRegister);

        return view('cash-registers.print', [
            'cashRegister' => $cashRegister,
            'denominationDetails' => $denominationDetails,
            'isSupervisor' => $user->hasAnyRole(['admin', 'super_admin', 'manager']),
            'printDate' => now()->format('d/m/Y H:i:s'),
        ]);
    }

    private function getDenominationDetails(CashRegister $cashRegister)
    {
        // Extraer detalles de denominaciones de las observaciones
        $observations = $cashRegister->observations ?? '';
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
}
