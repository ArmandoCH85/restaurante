<?php

namespace App\Http\Controllers\Filament;

use App\Http\Controllers\Controller;
use App\Models\CashRegister;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class PrintCashRegisterController extends Controller
{
    /**
     * Maneja la solicitud de impresión de cierre de caja.
     *
     * @param Request $request La solicitud HTTP
     * @param int|string $id ID de la caja registradora
     * @return View|RedirectResponse Vista de impresión o redirección
     */
    public function __invoke(Request $request, $id): View|RedirectResponse
    {
        if (!Auth::check()) {
            return $this->redirectToLogin();
        }

        $cashRegister = $this->getCashRegister($id);

        if (!$this->userCanViewCashRegister($cashRegister)) {
            return $this->redirectWithError();
        }

        return $this->renderPrintView($cashRegister);
    }

    /**
     * Redirige al usuario a la página de login.
     *
     * @return RedirectResponse
     */
    private function redirectToLogin(): RedirectResponse
    {
        return redirect('/admin/login');
    }

    /**
     * Obtiene la caja registradora por ID.
     *
     * @param int|string $id ID de la caja registradora
     * @return CashRegister
     */
    private function getCashRegister($id): CashRegister
    {
        return CashRegister::findOrFail($id);
    }

    /**
     * Verifica si el usuario puede ver la caja registradora.
     *
     * @param CashRegister $cashRegister Caja registradora
     * @return bool
     */
    private function userCanViewCashRegister(CashRegister $cashRegister): bool
    {
        $user = Auth::user();

        return $user->hasAnyRole(['admin', 'super_admin', 'manager']) ||
               $user->id === $cashRegister->opened_by ||
               $user->id === $cashRegister->closed_by;
    }

    /**
     * Redirige con mensaje de error.
     *
     * @return RedirectResponse
     */
    private function redirectWithError(): RedirectResponse
    {
        return redirect('/admin/operaciones-caja')
            ->with('error', 'No tienes permiso para ver este informe');
    }

    /**
     * Renderiza la vista de impresión.
     *
     * @param CashRegister $cashRegister Caja registradora
     * @return View
     */
    private function renderPrintView(CashRegister $cashRegister): View
    {
        $user = Auth::user();
        $denominationDetails = $this->getDenominationDetails($cashRegister);

        return view('cash-registers.print', [
            'cashRegister' => $cashRegister,
            'denominationDetails' => $denominationDetails,
            'isSupervisor' => $user->hasAnyRole(['admin', 'super_admin', 'manager']),
            'printDate' => now()->format('d/m/Y H:i:s'),
        ]);
    }

    /**
     * Extrae los detalles de denominaciones de las observaciones.
     *
     * @param CashRegister $cashRegister Caja registradora
     * @return array
     */
    private function getDenominationDetails(CashRegister $cashRegister): array
    {
        $observations = $cashRegister->observations ?? '';
        $details = [];

        if (!Str::contains($observations, 'Desglose de denominaciones:')) {
            return $details;
        }

        $details['billetes'] = $this->extractBillDenominations($observations);
        $details['monedas'] = $this->extractCoinDenominations($observations);

        return $details;
    }

    /**
     * Extrae las denominaciones de billetes.
     *
     * @param string $observations Observaciones
     * @return array
     */
    private function extractBillDenominations(string $observations): array
    {
        $billetes = [];
        preg_match('/Billetes: S\/10: (\d+) \| S\/20: (\d+) \| S\/50: (\d+) \| S\/100: (\d+) \| S\/200: (\d+)/', $observations, $matches);

        if (!empty($matches)) {
            $billetes = [
                '10' => (int)($matches[1] ?? 0),
                '20' => (int)($matches[2] ?? 0),
                '50' => (int)($matches[3] ?? 0),
                '100' => (int)($matches[4] ?? 0),
                '200' => (int)($matches[5] ?? 0),
            ];
        }

        return $billetes;
    }

    /**
     * Extrae las denominaciones de monedas.
     *
     * @param string $observations Observaciones
     * @return array
     */
    private function extractCoinDenominations(string $observations): array
    {
        $monedas = [];
        preg_match('/Monedas: S\/0.10: (\d+) \| S\/0.20: (\d+) \| S\/0.50: (\d+) \| S\/1: (\d+) \| S\/2: (\d+) \| S\/5: (\d+)/', $observations, $matches);

        if (!empty($matches)) {
            $monedas = [
                '0.10' => (int)($matches[1] ?? 0),
                '0.20' => (int)($matches[2] ?? 0),
                '0.50' => (int)($matches[3] ?? 0),
                '1' => (int)($matches[4] ?? 0),
                '2' => (int)($matches[5] ?? 0),
                '5' => (int)($matches[6] ?? 0),
            ];
        }

        return $monedas;
    }
}
