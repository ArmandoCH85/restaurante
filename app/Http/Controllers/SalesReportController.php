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
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class SalesReportController extends Controller
{
    public function download(Request $request)
    {
        try {
            Log::info('SalesReportController - Descarga reporte general iniciada');
            
            // Validar parámetros
            $startDate = $request->input('startDate', now()->format('Y-m-d'));
            $endDate = $request->input('endDate', now()->format('Y-m-d'));
            $invoiceType = $request->input('invoiceType');
            $channelFilter = $request->input('channelFilter');
            
            Log::info('Parámetros recibidos', [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'invoiceType' => $invoiceType,
                'channelFilter' => $channelFilter
            ]);
            
            // Obtener datos de ventas
            Log::info('Procesando fechas');
            $startDateTime = Carbon::parse($startDate)->startOfDay();
            $endDateTime = Carbon::parse($endDate)->endOfDay();
            
            Log::info('Construyendo query');
            $query = Order::whereBetween('order_datetime', [$startDateTime, $endDateTime])
                ->where('billed', true)
                ->with(['customer', 'user', 'table', 'cashRegister', 'invoices.customer'])
                ->orderBy('order_datetime', 'desc');
            
            // Aplicar filtros adicionales si existen
            if ($invoiceType && $invoiceType !== 'all') {
                Log::info('Filtro invoiceType aplicado', ['invoiceType' => $invoiceType]);
                $query->whereHas('invoices', function($q) use ($invoiceType) {
                    if ($invoiceType === 'sales_note') {
                        $q->where('invoice_type', 'receipt')->whereNull('sunat_status');
                    } elseif ($invoiceType === 'receipt') {
                        $q->where('invoice_type', 'receipt')->whereNotNull('sunat_status');
                    } elseif ($invoiceType === 'invoice') {
                        $q->where('invoice_type', 'invoice');
                    }
                });
            }
            
            if ($channelFilter) {
                Log::info('Filtro channelFilter aplicado', ['channelFilter' => $channelFilter]);
                $query->where('service_type', $channelFilter);
            }
            
            Log::info('Ejecutando query');
            $orders = $query->get();
            Log::info('Query completada', ['count' => $orders->count()]);
            
            // Crear el archivo Excel
            Log::info('Creando Excel');
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Configurar título y encabezados
            $sheet->setCellValue('A1', 'REPORTE DE VENTAS');
            $sheet->setCellValue('A2', 'Período: ' . $startDate . ' - ' . $endDate);
            $sheet->setCellValue('A4', 'Fecha');
            $sheet->setCellValue('B4', 'Hora');
            $sheet->setCellValue('C4', 'Cliente');
            $sheet->setCellValue('D4', 'Mesa');
            $sheet->setCellValue('E4', 'Tipo');
            $sheet->setCellValue('F4', 'Estado SUNAT');
            $sheet->setCellValue('G4', 'Canal de Venta');
            $sheet->setCellValue('H4', 'Total');
            $sheet->setCellValue('I4', 'Cajero');
            
            // Aplicar estilos
            $sheet->mergeCells('A1:I1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A4:I4')->getFont()->setBold(true);
            $sheet->getStyle('A4:I4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E0E0E0');
            
            // Llenar datos
            $row = 5;
            foreach ($orders as $order) {
                // Fecha como valor de fecha real para Excel
                $sheet->setCellValue('A' . $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($order->order_datetime));
                $sheet->getStyle('A' . $row)->getNumberFormat()->setFormatCode('dd/mm/yyyy');
                
                // Hora como valor de fecha/hora real
                $sheet->setCellValue('B' . $row, \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($order->order_datetime));
                $sheet->getStyle('B' . $row)->getNumberFormat()->setFormatCode('hh:mm');
                
                // Cliente
                $cliente = 'Cliente General';
                if ($order->customer) {
                    $cliente = $order->customer->name;
                } elseif ($order->invoices->first() && $order->invoices->first()->customer) {
                    $cliente = $order->invoices->first()->customer->name;
                } elseif ($order->table) {
                    $cliente = 'Mesa ' . $order->table->name;
                }
                $sheet->setCellValue('C' . $row, $cliente);
                
                // Mesa
                $mesa = $order->table ? $order->table->name : '-';
                $sheet->setCellValue('D' . $row, $mesa);
                
                // Tipo de comprobante
                $tipo = 'Nota de Venta';
                if ($order->invoices->where('invoice_type', 'invoice')->isNotEmpty()) {
                    $tipo = 'Factura';
                } elseif ($order->invoices->where('invoice_type', 'receipt')->whereNotNull('sunat_status')->isNotEmpty()) {
                    $tipo = 'Boleta';
                }
                $sheet->setCellValue('E' . $row, $tipo);
                
                // Estado SUNAT
                $estado = 'No aplica';
                $invoice = $order->invoices->first();
                if ($invoice) {
                    if ($invoice->sunat_status === 'ACEPTADO') {
                        $estado = 'Aceptado';
                    } elseif ($invoice->sunat_status === 'RECHAZADO') {
                        $estado = 'Rechazado';
                    } elseif ($invoice->sunat_status === 'PENDIENTE') {
                        $estado = 'Pendiente';
                    } elseif ($invoice->sunat_status === 'NO_APLICA') {
                        $estado = 'No aplica';
                    }
                }
                $sheet->setCellValue('F' . $row, $estado);
                
                // Canal de Venta - Traducciones al español
                $canalVenta = match($order->service_type) {
                    'dine_in' => 'Comer en el restaurante',
                    'delivery' => 'Delivery a domicilio',
                    'takeout' => 'Para llevar',
                    'drive_thru' => 'Auto servicio',
                    default => ucfirst(str_replace('_', ' ', $order->service_type))
                };
                $sheet->setCellValue('G' . $row, $canalVenta);
                
                // Total como número real, no string formateado
                $sheet->setCellValue('H' . $row, $order->total);
                $sheet->getStyle('H' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
                
                // Cajero
                $cajero = $order->user ? $order->user->name : '-';
                $sheet->setCellValue('I' . $row, $cajero);
                
                $row++;
            }
            
            // Autoajustar columnas
            foreach (range('A', 'I') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            
            // Crear archivo temporal
            $filename = 'reporte_ventas_' . date('Y-m-d_H-i-s') . '.xlsx';
            $tempFile = storage_path('app/temp/' . $filename);
            
            // Crear directorio si no existe
            if (!file_exists(dirname($tempFile))) {
                mkdir(dirname($tempFile), 0755, true);
            }
            
            $writer = new Xlsx($spreadsheet);
            $writer->save($tempFile);
            
            Log::info('Excel generado exitosamente', ['file' => $tempFile]);
            
            // Retornar la respuesta de descarga
            Log::info('Retornando respuesta de descarga');
            $response = response()->download($tempFile, $filename)->deleteFileAfterSend();
            Log::info('Descarga Excel completada exitosamente');
            return $response;
            
        } catch (\Exception $e) {
            Log::error('Error en SalesReportController: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Error al generar el archivo Excel: ' . $e->getMessage()
            ], 500);
        }
    }
}