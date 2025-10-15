<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CashRegisterReportSimpleController extends Controller
{
    public function download(Request $request)
    {
        try {
            // Crear Excel con datos de prueba fijos
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Datos fijos de prueba
            $sheet->setCellValue('A1', 'REPORTE DE CAJA REGISTRADORA - SIMPLE');
            $sheet->setCellValue('A2', 'Período: 2024-01-01 - 2024-01-31');
            $sheet->setCellValue('A3', 'Resumen:');
            $sheet->setCellValue('A4', 'Total Ingresos:');
            $sheet->setCellValue('B4', '1000.00');
            $sheet->setCellValue('A5', 'Total Egresos:');
            $sheet->setCellValue('B5', '500.00');
            $sheet->setCellValue('A6', 'Saldo Final:');
            $sheet->setCellValue('B6', '500.00');
            
            // Encabezados
            $sheet->setCellValue('A8', 'Fecha');
            $sheet->setCellValue('B8', 'Tipo');
            $sheet->setCellValue('C8', 'Monto');
            $sheet->setCellValue('D8', 'Descripción');
            
            // Datos de ejemplo (solo 3 filas)
            $data = [
                ['2024-01-15 10:30', 'Ingreso 📈', '100.00', 'Venta de producto'],
                ['2024-01-15 11:45', 'Egreso 📉', '50.00', 'Compra de insumos'],
                ['2024-01-15 12:20', 'Ingreso 📈', '200.00', 'Venta de menú']
            ];
            
            $row = 9;
            foreach ($data as $item) {
                $sheet->setCellValue('A' . $row, $item[0]);
                $sheet->setCellValue('B' . $row, $item[1]);
                $sheet->setCellValue('C' . $row, $item[2]);
                $sheet->setCellValue('D' . $row, $item[3]);
                $row++;
            }
            
            // Autoajustar columnas
            foreach (range('A', 'D') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            
            // Guardar archivo
            $filename = 'reporte_caja_simple_' . date('Y-m-d_H-i-s') . '.xlsx';
            $tempFile = storage_path('app/temp/' . $filename);
            
            if (!file_exists(dirname($tempFile))) {
                mkdir(dirname($tempFile), 0755, true);
            }
            
            $writer = new Xlsx($spreadsheet);
            $writer->save($tempFile);
            
            // Verificar
            if (!file_exists($tempFile)) {
                throw new \Exception('No se pudo crear el archivo');
            }
            
            // Retornar con headers específicos
            return response()
                ->download($tempFile, $filename, [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                    'Cache-Control' => 'no-cache, must-revalidate',
                    'Pragma' => 'no-cache'
                ])
                ->deleteFileAfterSend(false);
                
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
}