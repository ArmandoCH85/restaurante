<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class CashRegisterController extends Controller
{
    /**
     * Muestra el formulario para crear un nuevo cierre de caja
     */
    public function create()
    {
        // Verificar si hay una caja abierta
        $openRegister = CashRegister::where('status', 'open')->first();

        if ($openRegister) {
            return redirect()->route('admin.cash-registers.edit', $openRegister)
                ->with('warning', 'Ya existe una caja abierta. No puede abrir otra hasta cerrar la actual.');
        }

        return view('cash-registers.create');
    }

    /**
     * Almacena un nuevo cierre de caja
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'opening_amount' => 'required|numeric|min:0',
        ]);

        $cashRegister = new CashRegister();
        $cashRegister->opening_amount = $validated['opening_amount'];
        $cashRegister->opened_by = Auth::id();
        $cashRegister->status = 'open';
        $cashRegister->opened_at = now();
        $cashRegister->save();

        return redirect()->route('pos.index')
            ->with('success', 'Caja abierta correctamente.');
    }

    /**
     * Muestra el formulario para cerrar una caja
     */
    public function edit(CashRegister $cashRegister)
    {
        if ($cashRegister->status === 'closed') {
            return redirect()->route('admin.cash-registers.index')
                ->with('warning', 'Esta caja ya está cerrada.');
        }

        // Calcular los totales por método de pago
        $paymentTotals = Payment::where('cash_register_id', $cashRegister->id)
            ->select('payment_method', DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        $cashSales = $paymentTotals->get('cash', (object)['total' => 0])->total;
        $cardSales = ($paymentTotals->get('credit_card', (object)['total' => 0])->total ?? 0) +
                    ($paymentTotals->get('debit_card', (object)['total' => 0])->total ?? 0);
        $otherSales = ($paymentTotals->get('bank_transfer', (object)['total' => 0])->total ?? 0) +
                     ($paymentTotals->get('digital_wallet', (object)['total' => 0])->total ?? 0);

        $totalSales = $cashSales + $cardSales + $otherSales;
        $expectedCash = $cashRegister->opening_amount + $cashSales;

        return view('cash-registers.edit', compact(
            'cashRegister',
            'cashSales',
            'cardSales',
            'otherSales',
            'totalSales',
            'expectedCash'
        ));
    }

    /**
     * Actualiza el cierre de caja
     */
    public function update(Request $request, CashRegister $cashRegister)
    {
        if ($cashRegister->status === 'closed') {
            return redirect()->route('admin.cash-registers.index')
                ->with('warning', 'Esta caja ya está cerrada.');
        }

        $validated = $request->validate([
            'actual_cash' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Calcular los totales por método de pago nuevamente para asegurar precisión
        $paymentTotals = Payment::where('cash_register_id', $cashRegister->id)
            ->select('payment_method', DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        $cashSales = $paymentTotals->get('cash', (object)['total' => 0])->total;
        $cardSales = ($paymentTotals->get('credit_card', (object)['total' => 0])->total ?? 0) +
                    ($paymentTotals->get('debit_card', (object)['total' => 0])->total ?? 0);
        $otherSales = ($paymentTotals->get('bank_transfer', (object)['total' => 0])->total ?? 0) +
                     ($paymentTotals->get('digital_wallet', (object)['total' => 0])->total ?? 0);

        $totalSales = $cashSales + $cardSales + $otherSales;
        $expectedCash = $cashRegister->opening_amount + $cashSales;
        $actualCash = $validated['actual_cash'];
        $difference = $actualCash - $expectedCash;

        $closeData = [
            'closed_by' => Auth::id(),
            'cash_sales' => $cashSales,
            'card_sales' => $cardSales,
            'other_sales' => $otherSales,
            'total_sales' => $totalSales,
            'expected_cash' => $expectedCash,
            'actual_cash' => $actualCash,
            'difference' => $difference,
            'notes' => $validated['notes'] ?? null,
        ];

        if ($cashRegister->close($closeData)) {
            return redirect()->route('admin.cash-registers.index')
                ->with('success', 'Caja cerrada correctamente.');
        } else {
            return back()->with('error', 'Error al cerrar la caja. Inténtelo de nuevo.');
        }
    }

    /**
     * Muestra el detalle de un cierre de caja
     */
    public function show(CashRegister $cashRegister)
    {
        // Obtener los pagos relacionados con este cierre
        $payments = Payment::where('cash_register_id', $cashRegister->id)
            ->with(['order', 'receiver'])
            ->orderBy('payment_datetime', 'desc')
            ->get();

        return view('cash-registers.show', compact('cashRegister', 'payments'));
    }

    /**
     * Imprime un cierre de caja
     */
    public function print(CashRegister $cashRegister)
    {
        // Obtener los pagos relacionados con este cierre
        $payments = Payment::where('cash_register_id', $cashRegister->id)
            ->with(['order', 'receiver'])
            ->orderBy('payment_datetime', 'desc')
            ->get();

        // Agrupar pagos por método
        $paymentsByMethod = $payments->groupBy('payment_method');

        // Calcular totales por método
        $totalsByMethod = [];
        foreach ($paymentsByMethod as $method => $methodPayments) {
            $totalsByMethod[$method] = $methodPayments->sum('amount');
        }

        return view('cash-registers.print', compact('cashRegister', 'payments', 'paymentsByMethod', 'totalsByMethod'));
    }
}
