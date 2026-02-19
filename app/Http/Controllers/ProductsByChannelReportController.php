<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\OrderDetail;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ProductsByChannelReportController extends Controller
{
    public function download(Request $request)
    {
        try {
            Log::info('🛒 ProductsByChannelReportController - Descarga reporte productos por canal iniciada');
            
            // Validar parámetros
            $startDate = $request->input('startDate', now()->format('Y-m-d'));
            $endDate = $request->input('endDate', now()->format('Y-m-d'));
            $channelFilter = $request->input('channelFilter');
            $invoiceType = $request->input('invoiceType');
            
            Log::info('🛒 Parámetros recibidos', [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'channelFilter' => $channelFilter,
                'invoiceType' => $invoiceType
            ]);
            
            // Obtener datos de productos por canal
            Log::info('📅 Procesando fechas');
            $startDateTime = Carbon::parse($startDate)->startOfDay();
            $endDateTime = Carbon::parse($endDate)->endOfDay();
            
            Log::info('🔍 Construyendo query de productos por canal');
            $query = OrderDetail::join('orders', 'order_details.order_id', '=', 'orders.id')
                ->join('products', 'order_details.product_id', '=', 'products.id')
                ->whereBetween('orders.order_datetime', [$startDateTime, $endDateTime])
                ->where('orders.billed', true);
            
            // Aplicar filtro por canal de venta si existe
            if ($channelFilter) {
                Log::info('🔍 Filtro channelFilter aplicado', ['channelFilter' => $channelFilter]);
                $query->where('orders.service_type', $channelFilter);
            }
            
            // Aplicar filtro por tipo de comprobante si existe
            if ($invoiceType) {
                Log::info('🔍 Filtro invoiceType aplicado', ['invoiceType' => $invoiceType]);
                $query->whereHas('order.invoices', function ($invoiceQuery) use ($invoiceType) {
                    switch ($invoiceType) {
                        case 'sales_note':
                            $invoiceQuery->where('series', 'LIKE', 'NV%');
                            break;
                        case 'receipt':
                            $invoiceQuery->where('series', 'LIKE', 'B%');
                            break;
                        case 'invoice':
                            $invoiceQuery->where('series', 'LIKE', 'F%');
                            break;
                    }
                });
            }
            
            Log::info('📊 Ejecutando query');
            $productsData = $query->select(
                    'orders.service_type',
                    \DB::raw('SUM(order_details.quantity) as total_quantity'),
                    \DB::raw('SUM(order_details.subtotal) as total_sales')
                )
                ->groupBy('orders.service_type')
                ->orderBy('orders.service_type')
                ->get();
            
            Log::info('✅ Query completada', ['count' => $productsData->count()]);
            
            // Crear el archivo Excel
            Log::info('📊 Creando Excel');
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Configurar título y encabezados
            $sheet->setCellValue('A1', 'REPORTE DE GANANCIA POR CANAL DE VENTA');
            $sheet->setCellValue('A2', 'Período: ' . $startDate . ' - ' . $endDate);
            
            if ($channelFilter) {
                $channelLabel = $this->getChannelLabel($channelFilter);
                $sheet->setCellValue('A3', 'Canal: ' . $channelLabel);
            }
            
            $sheet->setCellValue('A5', 'Canal de Venta');
            $sheet->setCellValue('B5', 'Cantidad');
            $sheet->setCellValue('C5', 'Total Ventas (S/)');
            
            // Aplicar estilos
            $sheet->mergeCells('A1:C1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A5:C5')->getFont()->setBold(true);
            $sheet->getStyle('A5:C5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E0E0E0');
            
            // Llenar datos
            $row = 6;
            foreach ($productsData as $channelData) {
                // Canal de Venta - Traducciones al español con emojis
                $channelLabel = $this->getChannelLabel($channelData->service_type);
                $sheet->setCellValue('A' . $row, $channelLabel);
                
                // Cantidad
                $sheet->setCellValue('B' . $row, number_format($channelData->total_quantity, 2));
                
                // Total Ventas
                $sheet->setCellValue('C' . $row, number_format($channelData->total_sales, 2));
                
                $row++;
            }
            
            // AGREGAR FILA DE TOTALES
            $totalQuantity = $productsData->sum('total_quantity');
            $totalSales = $productsData->sum('total_sales');
            
            $sheet->setCellValue('A' . $row, 'TOTAL GENERAL');
            $sheet->setCellValue('B' . $row, number_format($totalQuantity, 2));
            $sheet->setCellValue('C' . $row, number_format($totalSales, 2));
            
            // Aplicar estilos a la fila de totales
            $sheet->getStyle('A' . $row . ':C' . $row)->getFont()->setBold(true);
            $sheet->getStyle('A' . $row . ':C' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E7E6E6');
            $sheet->getStyle('A' . $row . ':C' . $row)->getBorders()->getTop()->setBorderStyle(Border::BORDER_THICK);
            
            Log::info('✅ Fila de totales agregada', [
                'total_quantity' => $totalQuantity,
                'total_sales' => $totalSales,
                'row' => $row
            ]);
            
            // Autoajustar columnas
            foreach (range('A', 'C') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            
            // Crear archivo temporal
            $filename = 'productos_por_canal_' . date('Y-m-d_H-i-s') . '.xlsx';
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
            Log::error('❌ Error en ProductsByChannelReportController: ' . $e->getMessage(), [
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
     * Obtener etiqueta traducida del canal de venta
     */
    private function getChannelLabel($serviceType)
    {
        return match($serviceType) {
            'dine_in' => '🍽️ En Mesa',
            'delivery' => '🚚 Delivery',
            'takeout' => '📦 Para Llevar',
            'drive_thru' => '🚗 Auto Servicio',
            default => ucfirst(str_replace('_', ' ', $serviceType))
        };
    }
}
