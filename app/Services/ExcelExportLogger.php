<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExcelExportLogger
{
    private string $logChannel = 'excel_export';
    private array $logData = [];
    private string $sessionId;

    public function __construct()
    {
        $this->sessionId = uniqid('excel_export_');
        $this->initializeLog();
    }

    private function initializeLog(): void
    {
        $this->logData = [
            'session_id' => $this->sessionId,
            'start_time' => now()->toDateTimeString(),
            'steps' => [],
            'errors' => [],
            'warnings' => [],
            'file_info' => [],
            'validation_results' => []
        ];
    }

    public function logStep(string $step, array $data = []): void
    {
        $stepData = [
            'timestamp' => microtime(true),
            'step' => $step,
            'data' => $data,
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true)
        ];

        $this->logData['steps'][] = $stepData;
        
        Log::channel($this->logChannel)->info("Excel Export Step: {$step}", $stepData);
    }

    public function logFileInfo(string $filePath): void
    {
        if (!file_exists($filePath)) {
            $this->logError("File does not exist", ['file_path' => $filePath]);
            return;
        }

        $fileInfo = [
            'file_path' => $filePath,
            'file_size' => filesize($filePath),
            'file_size_formatted' => $this->formatBytes(filesize($filePath)),
            'file_permissions' => substr(sprintf('%o', fileperms($filePath)), -4),
            'file_modified' => date('Y-m-d H:i:s', filemtime($filePath)),
            'file_extension' => pathinfo($filePath, PATHINFO_EXTENSION),
            'mime_type' => mime_content_type($filePath)
        ];

        $this->logData['file_info'] = $fileInfo;
        $this->logStep('File Info Captured', $fileInfo);
    }

    public function validateExcelFile(string $filePath): array
    {
        $validationResults = [
            'file_path' => $filePath,
            'validation_time' => now()->toDateTimeString(),
            'tests' => []
        ];

        try {
            // Test 1: Verificar que el archivo existe
            if (!file_exists($filePath)) {
                throw new \Exception("File does not exist: {$filePath}");
            }
            $validationResults['tests']['file_exists'] = ['status' => 'passed', 'message' => 'File exists'];

            // Test 2: Identificar el tipo de archivo con IOFactory
            try {
                $fileType = IOFactory::identify($filePath);
                $validationResults['tests']['file_identification'] = [
                    'status' => 'passed', 
                    'message' => "File identified as: {$fileType}"
                ];
            } catch (\Exception $e) {
                $validationResults['tests']['file_identification'] = [
                    'status' => 'failed', 
                    'message' => "File identification failed: " . $e->getMessage()
                ];
                throw $e;
            }

            // Test 3: Intentar cargar el archivo
            try {
                $reader = IOFactory::createReader($fileType);
                $spreadsheet = $reader->load($filePath);
                $sheetCount = $spreadsheet->getSheetCount();
                $validationResults['tests']['file_loading'] = [
                    'status' => 'passed', 
                    'message' => "File loaded successfully. Sheets: {$sheetCount}"
                ];
            } catch (\Exception $e) {
                $validationResults['tests']['file_loading'] = [
                    'status' => 'failed', 
                    'message' => "File loading failed: " . $e->getMessage()
                ];
                throw $e;
            }

            // Test 4: Verificar integridad básica
            try {
                $reader = IOFactory::createReader($fileType);
                $spreadsheet = $reader->load($filePath);
                $activeSheet = $spreadsheet->getActiveSheet();
                $highestRow = $activeSheet->getHighestRow();
                $highestColumn = $activeSheet->getHighestColumn();
                
                $validationResults['tests']['file_integrity'] = [
                    'status' => 'passed', 
                    'message' => "File integrity check passed. Rows: {$highestRow}, Columns: {$highestColumn}"
                ];
            } catch (\Exception $e) {
                $validationResults['tests']['file_integrity'] = [
                    'status' => 'failed', 
                    'message' => "File integrity check failed: " . $e->getMessage()
                ];
                throw $e;
            }

            $validationResults['overall_status'] = 'passed';
            $validationResults['summary'] = 'All validation tests passed';

        } catch (\Exception $e) {
            $validationResults['overall_status'] = 'failed';
            $validationResults['summary'] = 'Validation failed: ' . $e->getMessage();
            $this->logError('File validation failed', [
                'error' => $e->getMessage(),
                'file_path' => $filePath
            ]);
        }

        $this->logData['validation_results'] = $validationResults;
        $this->logStep('File Validation Completed', $validationResults);

        return $validationResults;
    }

    public function logError(string $message, array $context = []): void
    {
        $errorData = [
            'timestamp' => microtime(true),
            'message' => $message,
            'context' => $context,
            'memory_usage' => memory_get_usage(true)
        ];

        $this->logData['errors'][] = $errorData;
        Log::channel($this->logChannel)->error("Excel Export Error: {$message}", $errorData);
    }

    public function logWarning(string $message, array $context = []): void
    {
        $warningData = [
            'timestamp' => microtime(true),
            'message' => $message,
            'context' => $context,
            'memory_usage' => memory_get_usage(true)
        ];

        $this->logData['warnings'][] = $warningData;
        Log::channel($this->logChannel)->warning("Excel Export Warning: {$message}", $warningData);
    }

    public function getLogSummary(): array
    {
        return [
            'session_id' => $this->sessionId,
            'total_steps' => count($this->logData['steps']),
            'total_errors' => count($this->logData['errors']),
            'total_warnings' => count($this->logData['warnings']),
            'start_time' => $this->logData['start_time'],
            'end_time' => now()->toDateTimeString(),
            'file_size' => $this->logData['file_info']['file_size_formatted'] ?? 'N/A',
            'validation_status' => $this->logData['validation_results']['overall_status'] ?? 'not_performed',
            'has_critical_errors' => !empty($this->logData['errors'])
        ];
    }

    public function saveDetailedLog(): string
    {
        $logFileName = "excel_export_log_{$this->sessionId}_" . date('Y-m-d_H-i-s') . '.json';
        $logPath = storage_path("logs/{$logFileName}");
        
        $this->logData['summary'] = $this->getLogSummary();
        
        file_put_contents($logPath, json_encode($this->logData, JSON_PRETTY_PRINT));
        
        Log::channel($this->logChannel)->info("Detailed log saved to: {$logPath}");
        
        return $logPath;
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    public function __destruct()
    {
        if (!empty($this->logData['steps'])) {
            $this->saveDetailedLog();
        }
    }
}