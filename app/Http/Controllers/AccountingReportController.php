<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\Log;

class AccountingReportController extends Controller
{
    public function download(Request $request): BinaryFileResponse
    {
        Log::info('AccountingReportController - Descarga reporte de contabilidad iniciada');
        
        // Obtener parámetros
        $startDate = $request->get('startDate', now()->format('Y-m-d'));
        $endDate = $request->get('endDate', now()->format('Y-m-d'));
        $invoiceType = $request->get('invoiceType');
        
        Log::info('Parámetros recibidos', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'invoiceType' => $invoiceType
        ]);
        
        // Procesar fechas
        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();
        
        Log::info('Procesando fechas', [
            'startDateTime' => $startDateTime->format('Y-m-d H:i:s'),
            'endDateTime' => $endDateTime->format('Y-m-d H:i:s')
        ]);
        
        // Construir query
        $query = Invoice::with(['customer', 'order'])
            ->whereBetween('issue_date', [$startDateTime->format('Y-m-d'), $endDateTime->format('Y-m-d')])
            // Por defecto, excluir Notas de Venta (series NV%)
            ->where('series', 'NOT LIKE', 'NV%');
        
        // Aplicar filtro por tipo de comprobante si está presente
        if ($invoiceType) {
            Log::info('Aplicando filtro de tipo de comprobante: ' . $invoiceType);
            switch ($invoiceType) {
                case 'receipt':
                    $query->where('series', 'LIKE', 'B%');
                    break;
                case 'invoice':
                    $query->where('series', 'LIKE', 'F%');
                    break;
                default:
                    $query->where('invoice_type', $invoiceType);
                    break;
            }
        }
        
        // Ejecutar query
        $invoices = $query->orderBy('issue_date', 'desc')->orderBy('series')->orderBy('number')->get();
        
        Log::info('Query ejecutada', ['count' => $invoices->count()]);
        
        // Mostrar series encontradas para debugging
        $seriesEncontradas = $invoices->pluck('series')->unique()->values();
        Log::info('Series encontradas', ['series' => $seriesEncontradas->toArray()]);
        
        // Contar por invoice_type para debugging
        $tiposContados = [];
        $invoices->each(function ($item) use (&$tiposContados) {
            $tiposContados[$item->invoice_type] = ($tiposContados[$item->invoice_type] ?? 0) + 1;
        });
        
        Log::info('Conteo por invoice_type', ['tipos' => $tiposContados]);
        
        // Crear Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Headers
        $headers = ['Fecha Emisión', 'Tipo Comprobante', 'Número', 'Cliente', 'Total', 'Estado'];
        $column = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($column . '1', $header);
            $column++;
        }
        
        // Estilos para header
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
        ];
        $lastColumn = chr(ord('A') + count($headers) - 1);
        $sheet->getStyle('A1:' . $lastColumn . '1')->applyFromArray($headerStyle);
        
        // Datos
        $row = 2;
        foreach ($invoices as $invoice) {
            $sheet->setCellValue('A' . $row, $invoice->issue_date->format('d/m/Y'));
            $sheet->setCellValue('B' . $row, $this->getInvoiceTypeLabel($invoice));
            $sheet->setCellValue('C' . $row, $invoice->series . '-' . $invoice->number);
            $sheet->setCellValue('D' . $row, $invoice->customer ? $invoice->customer->full_name : ($invoice->client_name ?? 'Cliente no registrado'));
            $sheet->setCellValue('E' . $row, $invoice->total);
            $sheet->setCellValue('F' . $row, $this->getInvoiceStatusLabel($invoice));
            $row++;
        }
        
        // Auto-size columns
        foreach (range('A', $lastColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }
        
        // Crear archivo temporal
        $filename = 'reportes_de_contabilidad_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        $tempFile = storage_path('app/temp/' . $filename);
        
        // Asegurar que el directorio exista
        if (!is_dir(dirname($tempFile))) {
            mkdir(dirname($tempFile), 0755, true);
        }
        
        // Guardar archivo
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);
        
        Log::info('Excel creado exitosamente', ['file' => $tempFile]);
        
        // Retornar descarga
        return response()->download($tempFile, $filename)->deleteFileAfterSend(true);
    }
    
    private function getInvoiceTypeLabel($invoice): string
    {
        return match($invoice->invoice_type) {
            'invoice' => 'Factura',
            'receipt' => $invoice->sunat_status ? 'Boleta' : 'Nota de Venta',
            'sales_note' => 'Nota de Venta',
            'credit_note' => 'Nota de Crédito',
            'debit_note' => 'Nota de Débito',
            default => 'Desconocido'
        };
    }
    
    private function getInvoiceStatusLabel($invoice): string
    {
        if ($invoice->tax_authority_status === 'voided') {
            return 'Anulado';
        }
        
        return match($invoice->sunat_status) {
            null => 'Pendiente',
            'PENDIENTE' => 'Pendiente',
            'ACEPTADO' => 'Aceptado',
            'RECHAZADO' => 'Rechazado',
            'OBSERVADO' => 'Observado',
            'NO_APLICA' => 'No aplica',
            default => $invoice->tax_authority_status ?? 'Desconocido'
        };
    }
}