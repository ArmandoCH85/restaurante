<?php

namespace App\Http\Controllers;

use App\Models\CashRegister;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class CashRegisterReportController extends Controller
{
    public function exportDetailPdf(CashRegister $cashRegister)
    {
        // Cargar las relaciones necesarias que se usan en la vista del PDF
        $cashRegister->load(['user', 'cashMovements.approvedByUser', 'orders.user', 'orders.payments']);

        // Lógica para generar el PDF
        $pdf = Pdf::loadView('pdf.cash_register_detail', [
            'record' => $cashRegister,
            'movements' => $cashRegister->cashMovements,
            'orders' => $cashRegister->orders,
        ]);

        return $pdf->download("reporte_caja_{$cashRegister->id}.pdf");
    }
}
