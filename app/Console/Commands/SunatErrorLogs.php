<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use Illuminate\Support\Facades\File;

class SunatErrorLogs extends Command
{
    protected $signature = 'sunat:error-logs {--invoice_id=} {--days=7} {--type=all}';
    protected $description = 'Mostrar logs de errores de SUNAT y códigos de estado';

    public function handle()
    {
        $invoiceId = $this->option('invoice_id');
        $days = $this->option('days');
        $type = $this->option('type');

        $this->info('🔍 LOGS DE ERRORES SUNAT');
        $this->line('');

        if ($invoiceId) {
            $this->showInvoiceErrors($invoiceId);
        } else {
            $this->showRecentErrors($days, $type);
        }

        $this->line('');
        $this->showLogFileLocation();
        
        return 0;
    }

    private function showInvoiceErrors($invoiceId)
    {
        $invoice = Invoice::find($invoiceId);
        
        if (!$invoice) {
            $this->error("❌ Factura #{$invoiceId} no encontrada");
            return;
        }

        $this->info("📄 DETALLES DE ERROR - Factura #{$invoiceId}");
        $this->line("📋 Comprobante: {$invoice->series}-{$invoice->number}");
        $this->line("📅 Fecha: {$invoice->issue_date}");
        $this->line("💰 Total: S/ " . number_format($invoice->total, 2));
        $this->line('');

        // Estado SUNAT
        $statusColor = match($invoice->sunat_status) {
            'ACEPTADO' => 'green',
            'RECHAZADO' => 'red',
            'ERROR' => 'red',
            'ENVIANDO' => 'yellow',
            default => 'gray'
        };

        $this->line("🏷️  Estado SUNAT: <fg={$statusColor}>{$invoice->sunat_status}</>");
        
        if ($invoice->sunat_code) {
            $this->line("🔢 Código SUNAT: <fg=cyan>{$invoice->sunat_code}</>");
        }
        
        if ($invoice->sunat_description) {
            $this->line("📝 Descripción: <fg=yellow>{$invoice->sunat_description}</>");
        }

        if ($invoice->sent_at) {
            $this->line("📤 Enviado: {$invoice->sent_at}");
        }

        // Archivos generados
        $this->line('');
        $this->info('📁 ARCHIVOS GENERADOS:');
        
        if ($invoice->xml_path && File::exists($invoice->xml_path)) {
            $this->line("✅ XML: {$invoice->xml_path}");
        } else {
            $this->line("❌ XML: No encontrado");
        }

        if ($invoice->cdr_path && File::exists($invoice->cdr_path)) {
            $this->line("✅ CDR: {$invoice->cdr_path}");
        } else {
            $this->line("❌ CDR: No encontrado");
        }
    }

    private function showRecentErrors($days, $type)
    {
        $query = Invoice::query();

        // Filtrar por tipo de error
        if ($type === 'rejected') {
            $query->where('sunat_status', 'RECHAZADO');
        } elseif ($type === 'error') {
            $query->where('sunat_status', 'ERROR');
        } elseif ($type === 'failed') {
            $query->whereIn('sunat_status', ['RECHAZADO', 'ERROR']);
        } else {
            // Todos los que tienen algún estado SUNAT
            $query->whereNotNull('sunat_status');
        }

        // Filtrar por días
        $query->where('created_at', '>=', now()->subDays($days));
        
        $invoices = $query->orderBy('created_at', 'desc')->get();

        if ($invoices->isEmpty()) {
            $this->info("✅ No se encontraron errores en los últimos {$days} días");
            return;
        }

        $this->info("📊 RESUMEN DE ERRORES (Últimos {$days} días):");
        $this->line('');

        // Estadísticas
        $stats = [
            'ACEPTADO' => $invoices->where('sunat_status', 'ACEPTADO')->count(),
            'RECHAZADO' => $invoices->where('sunat_status', 'RECHAZADO')->count(),
            'ERROR' => $invoices->where('sunat_status', 'ERROR')->count(),
            'ENVIANDO' => $invoices->where('sunat_status', 'ENVIANDO')->count(),
            'PENDIENTE' => $invoices->where('sunat_status', 'PENDIENTE')->count(),
        ];

        foreach ($stats as $status => $count) {
            if ($count > 0) {
                $color = match($status) {
                    'ACEPTADO' => 'green',
                    'RECHAZADO', 'ERROR' => 'red',
                    'ENVIANDO' => 'yellow',
                    default => 'gray'
                };
                $this->line("  <fg={$color}>{$status}: {$count}</>");
            }
        }

        $this->line('');
        $this->info('🔍 DETALLES DE ERRORES:');

        // Mostrar errores específicos
        $errors = $invoices->whereIn('sunat_status', ['RECHAZADO', 'ERROR']);
        
        foreach ($errors as $invoice) {
            $this->line('');
            $this->line("📄 {$invoice->series}-{$invoice->number} | {$invoice->issue_date} | S/ {$invoice->total}");
            $this->line("   Estado: <fg=red>{$invoice->sunat_status}</>");
            
            if ($invoice->sunat_code) {
                $this->line("   Código: <fg=cyan>{$invoice->sunat_code}</>");
            }
            
            if ($invoice->sunat_description) {
                $this->line("   Error: <fg=yellow>" . substr($invoice->sunat_description, 0, 80) . "</>");
            }
        }
    }

    private function showLogFileLocation()
    {
        $logPath = storage_path('logs/laravel.log');
        $this->info('📁 UBICACIÓN DE LOGS COMPLETOS:');
        $this->line("   {$logPath}");
        $this->line('');
        $this->info('💡 COMANDOS ÚTILES:');
        $this->line('   # Ver logs recientes de SUNAT:');
        $this->line('   tail -f storage/logs/laravel.log | grep -i sunat');
        $this->line('');
        $this->line('   # Buscar errores específicos:');
        $this->line('   grep "Error al emitir factura" storage/logs/laravel.log');
        $this->line('');
        $this->line('   # Ver logs de una factura específica:');
        $this->line('   php artisan sunat:error-logs --invoice_id=123');
    }
}
