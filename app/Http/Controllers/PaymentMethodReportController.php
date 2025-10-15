<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PaymentMethodReportController extends Controller
{
    public function download(Request $request)
    {
        try {
            Log::info('💳 PaymentMethodReportController - Descarga reporte de métodos de pago iniciada');
            
            // Validar parámetros
            $startDate = $request->input('startDate', now()->format('Y-m-d'));
            $endDate = $request->input('endDate', now()->format('Y-m-d'));
            
            Log::info('💳 Parámetros recibidos', [
                'startDate' => $startDate,
                'endDate' => $endDate
            ]);
            
            // Obtener datos de métodos de pago
            Log::info('📅 Procesando fechas');
            $startDateTime = Carbon::parse($startDate)->startOfDay();
            $endDateTime = Carbon::parse($endDate)->endOfDay();
            
            Log::info('🔍 Construyendo query de métodos de pago');
            
            // Obtener órdenes agrupadas por método de pago
            $paymentMethods = Order::whereBetween('created_at', [$startDateTime, $endDateTime])
                ->where('status', 'completed')
                ->selectRaw('payment_method, COUNT(*) as total_orders, SUM(total) as total_amount')
                ->groupBy('payment_method')
                ->orderBy('total_amount', 'desc')
                ->get();
            
            Log::info('✅ Query completada', ['count' => $paymentMethods->count()]);
            
            // Crear el archivo Excel
            Log::info('📊 Creando Excel');
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Configurar título y encabezados
            $sheet->setCellValue('A1', 'REPORTE DE MÉTODOS DE PAGO');
            $sheet->setCellValue('A2', 'Período: ' . $startDate . ' - ' . $endDate);
            $sheet->setCellValue('A4', 'Método de Pago');
            $sheet->setCellValue('B4', 'N° de Órdenes');
            $sheet->setCellValue('C4', 'Monto Total');
            $sheet->setCellValue('D4', 'Porcentaje');
            
            // Aplicar estilos
            $sheet->mergeCells('A1:D1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A4:D4')->getFont()->setBold(true);
            $sheet->getStyle('A4:D4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E0E0E0');
            
            // Calcular total general
            $totalGeneral = $paymentMethods->sum('total_amount');
            
            // Llenar datos
            $row = 5;
            foreach ($paymentMethods as $paymentMethod) {
                // Método de pago traducido
                $methodLabel = $this->getPaymentMethodLabel($paymentMethod->payment_method);
                $sheet->setCellValue('A' . $row, $methodLabel);
                
                // Número de órdenes
                $sheet->setCellValue('B' . $row, $paymentMethod->total_orders);
                
                // Monto total
                $sheet->setCellValue('C' . $row, number_format($paymentMethod->total_amount, 2));
                
                // Porcentaje
                $percentage = $totalGeneral > 0 ? ($paymentMethod->total_amount / $totalGeneral) * 100 : 0;
                $sheet->setCellValue('D' . $row, number_format($percentage, 2) . '%');
                
                $row++;
            }
            
            // Agregar fila de totales
            $sheet->setCellValue('A' . $row, 'TOTAL GENERAL');
            $sheet->setCellValue('B' . $row, $paymentMethods->sum('total_orders'));
            $sheet->setCellValue('C' . $row, number_format($totalGeneral, 2));
            $sheet->setCellValue('D' . $row, '100.00%');
            
            // Aplicar estilo a la fila de totales
            $sheet->getStyle('A' . $row . ':D' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row . ':D' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F0F0F0');
            
            // Autoajustar columnas
            foreach (range('A', 'D') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            
            // Crear archivo temporal
            $filename = 'reporte_metodos_pago_' . date('Y-m-d_H-i-s') . '.xlsx';
            $tempFile = storage_path('app/temp/' . $filename);
            
            // Crear directorio si no existe
            if (!file_exists(dirname($tempFile))) {
                mkdir(dirname($tempFile), 0755, true);
            }
            
            $writer = new Xlsx($spreadsheet);
            $writer->save($tempFile);
            
            Log::info('Excel generado exitosamente', ['file' => $tempFile]);
            
            // Retornar la respuesta de descarga
            Log::info('📤 Retornando respuesta de descarga');
            $response = response()->download($tempFile, $filename)->deleteFileAfterSend();
            Log::info('✅ Descarga Excel completada exitosamente');
            return $response;
            
        } catch (\Exception $e) {
            Log::error('❌ Error en PaymentMethodReportController: ' . $e->getMessage(), [
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
     * Obtener etiqueta del método de pago
     */
    private function getPaymentMethodLabel($paymentMethod)
    {
        return match($paymentMethod) {
            'cash' => 'Efectivo 💵',
            'credit_card' => 'Tarjeta de Crédito 💳',
            'debit_card' => 'Tarjeta de Débito 💳',
            'bank_transfer' => 'Transferencia Bancaria 🏦',
            'digital_wallet' => 'Billetera Digital 📱',
            'check' => 'Cheque 📄',
            'other' => 'Otro 📝',
            default => ucfirst(str_replace('_', ' ', $paymentMethod))
        };
    }
}