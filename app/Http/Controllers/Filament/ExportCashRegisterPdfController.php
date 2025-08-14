<?php

namespace App\Http\Controllers\Filament;

use App\Http\Controllers\Controller;
use App\Models\CashRegister;
use App\Models\CompanyConfig;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExportCashRegisterPdfController extends Controller
{
    public function export(Request $request, $id)
    {
        // Verificar permisos
        $user = Auth::user();
        if (!$user || !$user->hasAnyRole(['admin', 'super_admin', 'manager', 'cashier'])) {
            abort(403, 'No tienes permisos para exportar informes de caja.');
        }

        // Obtener la caja registradora
        $cashRegister = CashRegister::with(['openedBy', 'closedBy', 'approvedBy', 'payments'])
            ->findOrFail($id);

        // Obtener información de la empresa
        $company = [
            'ruc' => CompanyConfig::getRuc(),
            'razon_social' => CompanyConfig::getRazonSocial(),
            'nombre_comercial' => CompanyConfig::getNombreComercial(),
            'direccion' => CompanyConfig::getDireccion(),
            'telefono' => CompanyConfig::getTelefono(),
            'email' => CompanyConfig::getEmail(),
        ];

        // Calcular totales actualizados
        $systemSales = [
            'efectivo' => $cashRegister->getSystemCashSales(),
            'tarjetas' => $cashRegister->getSystemCardSales(),
            'yape' => $cashRegister->getSystemYapeSales(),
            'plin' => $cashRegister->getSystemPlinSales(),
            'didi_food' => $cashRegister->getSystemDidiSales(),
            'pedidos_ya' => $cashRegister->getSystemPedidosYaSales(),
        ];

        $systemSales['total'] = array_sum($systemSales);

        // Calcular montos de cierre (ahora igual a las ventas del sistema)
        $cierreAmounts = [
            'efectivo' => $systemSales['efectivo'],
            'yape' => $systemSales['yape'],
            'plin' => $systemSales['plin'],
            'tarjetas' => $systemSales['tarjetas'],
            'didi_food' => $systemSales['didi_food'],
            'pedidos_ya' => $systemSales['pedidos_ya'],
        ];

        $cierreAmounts['total'] = $systemSales['total'];

        // Contar usos de métodos de pago
        $paymentCounts = [
            'efectivo' => $cashRegister->payments()->where('payment_method', 'cash')->count(),
            'tarjetas' => $cashRegister->payments()->whereIn('payment_method', ['card', 'credit_card', 'debit_card'])->count(),
            'yape' => $cashRegister->payments()->where('payment_method', 'yape')->count(),
            'plin' => $cashRegister->payments()->where('payment_method', 'plin')->count(),
            'didi_food' => $cashRegister->payments()->where('payment_method', 'didi_food')->count(),
            'pedidos_ya' => $cashRegister->payments()->where('payment_method', 'pedidos_ya')->count(),
        ];

        // Filtrar observaciones para corregir diferencias
        $filteredObservations = $this->filterObservations($cashRegister->observations ?? '');

        // Datos para el PDF
        $data = [
            'cashRegister' => $cashRegister,
            'company' => $company,
            'systemSales' => $systemSales,
            'cierreAmounts' => $cierreAmounts,
            'paymentCounts' => $paymentCounts,
            'isSupervisor' => $user->hasAnyRole(['admin', 'super_admin', 'manager']),
            'filteredObservations' => $filteredObservations,
            'generatedDate' => now()->format('d/m/Y H:i:s'),
        ];

        // Generar PDF con vista optimizada
        $pdf = Pdf::loadView('pdf.cash-register-native', $data);
        
        // Configurar el PDF para mejor rendimiento
        $pdf->setPaper('A4', 'landscape')
            ->setOptions([
                'dpi' => 150,
                'defaultFont' => 'DejaVu Sans',
                'isRemoteEnabled' => false,
                'isHtml5ParserEnabled' => true,
                'isFontSubsettingEnabled' => true,
                'debugPng' => false,
                'debugKeepTemp' => false,
                'debugCss' => false,
                'debugLayout' => false,
                'debugLayoutLines' => false,
                'debugLayoutBlocks' => false,
                'debugLayoutInline' => false,
                'debugLayoutPaddingBox' => false,
            ]);
        
        // Nombre del archivo
        $filename = "informe-caja-{$cashRegister->id}-" . now()->format('Y-m-d-H-i-s') . ".pdf";
        
        // Descargar el PDF
        return $pdf->download($filename);
    }

    /**
     * Filtra las observaciones para corregir diferencias incorrectas
     */
    private function filterObservations(string $observations): string
    {
        if (empty($observations)) {
            return '';
        }

        // Reemplazar diferencias incorrectas
        $filtered = preg_replace('/⚖️ DIFERENCIA: S\/ -?\d+\.\d+ \((FALTANTE|SOBRANTE)\)/', '⚖️ DIFERENCIA: S/ 0.00 (SIN DIFERENCIA)', $observations);
        
        // Reemplazar cualquier mención de faltante o sobrante
        $filtered = str_replace(['(FALTANTE)', '(SOBRANTE)'], '(SIN DIFERENCIA)', $filtered);
        
        return $filtered;
    }
}