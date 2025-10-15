<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Purchase;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PurchaseReportController extends Controller
{
    public function downloadAll(Request $request)
    {
        try {
            Log::info('💰 PurchaseReportController - Descarga reporte de compras iniciada');
            
            // Validar parámetros
            $startDate = $request->input('startDate', now()->format('Y-m-d'));
            $endDate = $request->input('endDate', now()->format('Y-m-d'));
            
            Log::info('💰 Parámetros recibidos', [
                'startDate' => $startDate,
                'endDate' => $endDate
            ]);
            
            // Obtener datos de compras
            Log::info('📅 Procesando fechas');
            $startDateTime = Carbon::parse($startDate)->startOfDay();
            $endDateTime = Carbon::parse($endDate)->endOfDay();
            
            Log::info('🔍 Construyendo query de compras');
            $purchases = Purchase::whereBetween('purchase_date', [$startDateTime, $endDateTime])
                ->with(['supplier', 'creator'])
                ->orderBy('purchase_date', 'desc')
                ->get();
            
            Log::info('✅ Query completada', ['count' => $purchases->count()]);
            
            // Crear el archivo Excel
            Log::info('📊 Creando Excel');
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Configurar título y encabezados
            $sheet->setCellValue('A1', 'REPORTE DE COMPRAS');
            $sheet->setCellValue('A2', 'Período: ' . $startDate . ' - ' . $endDate);
            $sheet->setCellValue('A4', 'Fecha');
            $sheet->setCellValue('B4', 'Proveedor');
            $sheet->setCellValue('C4', 'Usuario');
            $sheet->setCellValue('D4', 'Documento');
            $sheet->setCellValue('E4', 'Tipo Documento');
            $sheet->setCellValue('F4', 'Subtotal');
            $sheet->setCellValue('G4', 'Impuesto');
            $sheet->setCellValue('H4', 'Total');
            $sheet->setCellValue('I4', 'Estado');
            $sheet->setCellValue('J4', 'Notas');
            
            // Aplicar estilos
            $sheet->mergeCells('A1:J1');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A4:J4')->getFont()->setBold(true);
            $sheet->getStyle('A4:J4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E0E0E0');
            
            // Llenar datos
            $row = 5;
            foreach ($purchases as $purchase) {
                $sheet->setCellValue('A' . $row, $purchase->purchase_date->format('d/m/Y'));
                
                // Proveedor
                $proveedor = $purchase->supplier ? $purchase->supplier->business_name : 'Sin proveedor';
                $sheet->setCellValue('B' . $row, $proveedor);
                
                // Usuario
                $usuario = $purchase->creator ? $purchase->creator->name : 'Sin usuario';
                $sheet->setCellValue('C' . $row, $usuario);
                
                // Documento
                $sheet->setCellValue('D' . $row, $purchase->document_number);
                
                // Tipo Documento
                $tipoDocumento = $this->getDocumentTypeLabel($purchase->document_type);
                $sheet->setCellValue('E' . $row, $tipoDocumento);
                
                // Subtotal
                $sheet->setCellValue('F' . $row, number_format($purchase->subtotal, 2));
                
                // Impuesto
                $sheet->setCellValue('G' . $row, number_format($purchase->tax, 2));
                
                // Total
                $sheet->setCellValue('H' . $row, number_format($purchase->total, 2));
                
                // Estado
                $estado = $this->getStatusLabel($purchase->status);
                $sheet->setCellValue('I' . $row, $estado);
                
                // Notas
                $notas = $purchase->notes ?? '-';
                $sheet->setCellValue('J' . $row, $notas);
                
                $row++;
            }
            
            // Autoajustar columnas
            foreach (range('A', 'J') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            
            // Crear archivo temporal
            $filename = 'reporte_compras_' . date('Y-m-d_H-i-s') . '.xlsx';
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
            Log::error('❌ Error en PurchaseReportController: ' . $e->getMessage(), [
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
     * Obtener etiqueta del tipo de documento
     */
    private function getDocumentTypeLabel($documentType)
    {
        return match($documentType) {
            'invoice' => 'Factura',
            'receipt' => 'Boleta',
            'credit_note' => 'Nota de Crédito',
            'debit_note' => 'Nota de Débito',
            'purchase_order' => 'Orden de Compra',
            default => ucfirst(str_replace('_', ' ', $documentType))
        };
    }
    
    /**
     * Obtener etiqueta del estado
     */
    private function getStatusLabel($status)
    {
        return match($status) {
            'pending' => 'Pendiente',
            'completed' => 'Completado',
            'cancelled' => 'Cancelado',
            'partial' => 'Parcial',
            default => ucfirst(str_replace('_', ' ', $status))
        };
    }
}