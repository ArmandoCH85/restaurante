<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\CashRegister;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

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

    public function download(Request $request)
    {
        try {
            Log::info('💰 CashRegisterReportController - Descarga reporte de caja registradora iniciada');
            
            // Validar parámetros
            $startDate = $request->input('startDate', now()->format('Y-m-d'));
            $endDate = $request->input('endDate', now()->format('Y-m-d'));
            
            Log::info('💰 Parámetros recibidos', [
                'startDate' => $startDate,
                'endDate' => $endDate
            ]);
            
            // Obtener datos de caja registradora
            Log::info('📅 Procesando fechas');
            $startDateTime = Carbon::parse($startDate)->startOfDay();
            $endDateTime = Carbon::parse($endDate)->endOfDay();
            
            Log::info('🔍 Construyendo query de caja registradora');
            
            // Obtener movimientos de caja
            $cashMovements = CashRegister::whereBetween('opening_datetime', [$startDateTime, $endDateTime])
                ->with(['user'])
                ->orderBy('opening_datetime', 'desc')
                ->get();
            
            Log::info('✅ Query completada', ['count' => $cashMovements->count()]);
            
            // Calcular resúmenes basados en opening_amount
            $totalIngresos = $cashMovements->where('opening_amount', '>', 0)->sum('opening_amount');
            $totalEgresos = $cashMovements->where('opening_amount', '<', 0)->sum('opening_amount');
            $saldoFinal = $totalIngresos + $totalEgresos;
            
            // Crear el archivo Excel
            Log::info('📊 Creando Excel');
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Configurar título y resumen
            $sheet->setCellValue('A1', 'REPORTE DE CAJAS REGISTRADORAS');
            $sheet->setCellValue('A2', 'Período: ' . $startDate . ' - ' . $endDate);
            $sheet->setCellValue('A3', 'Resumen:');
            $sheet->setCellValue('A4', 'Total Cajas Abiertas:');
            $sheet->setCellValue('B4', $cashMovements->count());
            $sheet->setCellValue('A5', 'Monto Total Apertura:');
            $sheet->setCellValue('B5', number_format($cashMovements->sum('opening_amount'), 2));
            $sheet->setCellValue('A6', 'Promedio por Caja:');
            $sheet->setCellValue('B6', number_format($cashMovements->avg('opening_amount'), 2));
            
            // Encabezados de detalle
            $sheet->setCellValue('A8', 'Fecha');
            $sheet->setCellValue('B8', 'Monto Apertura');
            $sheet->setCellValue('C8', 'Monto Esperado');
            $sheet->setCellValue('D8', 'Monto Real');
            $sheet->setCellValue('E8', 'Diferencia');
            $sheet->setCellValue('F8', 'Usuario');
            $sheet->setCellValue('G8', 'Estado');
            
            // Aplicar estilos
            $sheet->mergeCells('A1:G1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A3')->getFont()->setBold(true);
            $sheet->getStyle('A4:A6')->getFont()->setBold(true);
            $sheet->getStyle('A8:G8')->getFont()->setBold(true);
            $sheet->getStyle('A8:G8')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E0E0E0');
            
            // Colorear saldo final según sea positivo o negativo
            if ($saldoFinal >= 0) {
                $sheet->getStyle('B6')->getFont()->getColor()->setRGB('008000'); // Verde
            } else {
                $sheet->getStyle('B6')->getFont()->getColor()->setRGB('FF0000'); // Rojo
            }
            
            // Llenar datos de cajas registradoras
            $row = 9;
            foreach ($cashMovements as $movement) {
                // Fecha
                $sheet->setCellValue('A' . $row, $movement->opening_datetime->format('d/m/Y H:i'));
                
                // Monto Apertura
                $sheet->setCellValue('B' . $row, number_format($movement->opening_amount, 2));
                
                // Monto Esperado
                $expectedAmount = $movement->expected_amount ?? 0;
                $sheet->setCellValue('C' . $row, number_format($expectedAmount, 2));
                
                // Monto Real
                $actualAmount = $movement->actual_amount ?? 0;
                $sheet->setCellValue('D' . $row, number_format($actualAmount, 2));
                
                // Diferencia (con formato de color)
                $difference = $movement->difference ?? 0;
                $sheet->setCellValue('E' . $row, number_format($difference, 2));
                if ($difference >= 0) {
                    $sheet->getStyle('E' . $row)->getFont()->getColor()->setRGB('008000'); // Verde
                } else {
                    $sheet->getStyle('E' . $row)->getFont()->getColor()->setRGB('FF0000'); // Rojo
                }
                
                // Usuario
                $usuario = $movement->user ? $movement->user->name : 'Sin usuario';
                $sheet->setCellValue('F' . $row, $usuario);
                
                // Estado
                $estado = $this->getStatusLabel($movement->status);
                $sheet->setCellValue('G' . $row, $estado);
                
                $row++;
            }
            
            // Autoajustar columnas
            foreach (range('A', 'G') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            
            // Crear archivo temporal
            $filename = 'reporte_caja_registradora_' . date('Y-m-d_H-i-s') . '.xlsx';
            $tempFile = storage_path('app/temp/' . $filename);
            
            // Crear directorio si no existe
            if (!file_exists(dirname($tempFile))) {
                mkdir(dirname($tempFile), 0755, true);
            }
            
            Log::info('💾 Guardando archivo Excel', ['file' => $tempFile]);
            $writer = new Xlsx($spreadsheet);
            $writer->save($tempFile);
            
            // Verificar que el archivo se haya creado correctamente
            if (!file_exists($tempFile)) {
                throw new \Exception('El archivo Excel no se pudo crear en: ' . $tempFile);
            }
            
            $filesize = filesize($tempFile);
            Log::info('✅ Excel generado exitosamente', [
                'file' => $tempFile,
                'size' => $filesize . ' bytes'
            ]);
            
            // Retornar la respuesta de descarga
            Log::info('📤 Retornando respuesta de descarga');
            // No eliminar el archivo inmediatamente para debugging
            $response = response()->download($tempFile, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'no-cache, must-revalidate',
                'Pragma' => 'no-cache'
            ]); // ->deleteFileAfterSend();
            Log::info('✅ Descarga Excel completada exitosamente');
            return $response;
            
        } catch (\Exception $e) {
            Log::error('❌ Error en CashRegisterReportController: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Error al generar el archivo Excel: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener etiqueta del tipo de movimiento
     */
    private function getTypeLabel($type)
    {
        return match($type) {
            'income' => 'Ingreso 📈',
            'expense' => 'Egreso 📉',
            'opening' => 'Apertura 🏁',
            'closing' => 'Cierre 🏁',
            'adjustment' => 'Ajuste ⚙️',
            default => ucfirst(str_replace('_', ' ', $type))
        };
    }
    
    /**
     * Obtener etiqueta del estado
     */
    private function getStatusLabel($status)
    {
        return match($status) {
            'active' => 'Activo ✅',
            'closed' => 'Cerrado 🔒',
            'cancelled' => 'Cancelado ❌',
            'pending' => 'Pendiente ⏳',
            'confirmed' => 'Confirmado ✔️',
            default => ucfirst(str_replace('_', ' ', $status))
        };
    }
}
