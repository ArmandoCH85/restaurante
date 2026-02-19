<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\QpsService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class SendValidInvoicesToSunat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sunat:send-valid-invoices
                            {--date= : Fecha de emisión a procesar (YYYY-MM-DD)}
                            {--limit=0 : Máximo de comprobantes a procesar (0 = sin límite)}
                            {--dry-run : Simula el proceso sin enviar a SUNAT}
                            {--force : Ejecuta sin confirmación interactiva}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía diariamente a SUNAT vía QPSE los comprobantes válidos (boletas y facturas no anuladas).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dateOption = $this->option('date');
        $limit = max(0, (int) $this->option('limit'));
        $isDryRun = (bool) $this->option('dry-run');

        $dateFilter = $this->parseDateOption($dateOption);
        if ($dateOption && ! $dateFilter) {
            return Command::FAILURE;
        }

        $query = $this->buildValidInvoicesQuery($dateFilter);
        if ($limit > 0) {
            $query->limit($limit);
        }

        $invoices = $query->orderBy('id')->get();
        $total = $invoices->count();

        $this->info('📤 Envío automático de comprobantes a SUNAT vía QPSE');
        if ($dateFilter) {
            $this->line('📅 Fecha filtrada: '.$dateFilter->toDateString());
        }
        if ($limit > 0) {
            $this->line('🔢 Límite configurado: '.$limit);
        }
        if ($isDryRun) {
            $this->warn('🧪 MODO DRY-RUN activado (no se enviará a SUNAT)');
        }

        if ($total === 0) {
            $this->info('✅ No hay comprobantes válidos pendientes para enviar.');

            Log::channel('qps')->info('Tarea diaria SUNAT/QPSE: sin comprobantes pendientes', [
                'date_filter' => $dateFilter?->toDateString(),
                'limit' => $limit,
                'dry_run' => $isDryRun,
            ]);

            return Command::SUCCESS;
        }

        $this->info("📋 Comprobantes a procesar: {$total}");

        if ($isDryRun) {
            $this->table(
                ['ID', 'Comprobante', 'Tipo', 'Estado SUNAT', 'Estado Tributario', 'Total'],
                $invoices->take(20)->map(function (Invoice $invoice) {
                    return [
                        $invoice->id,
                        "{$invoice->series}-{$invoice->number}",
                        $invoice->invoice_type === 'invoice' ? 'Factura' : 'Boleta',
                        $invoice->sunat_status ?? 'NULL',
                        $invoice->tax_authority_status ?? 'NULL',
                        'S/ '.number_format((float) $invoice->total, 2),
                    ];
                })->toArray()
            );

            if ($total > 20) {
                $this->line('... y '.($total - 20).' comprobantes más');
            }

            return Command::SUCCESS;
        }

        if (
            $this->input->isInteractive() &&
            ! $this->option('force') &&
            ! $this->confirm("¿Desea enviar {$total} comprobantes a SUNAT vía QPSE?")
        ) {
            $this->warn('Operación cancelada por el usuario.');

            return Command::SUCCESS;
        }

        $qpsService = app(QpsService::class);

        $processed = 0;
        $successful = 0;
        $failed = 0;
        $errors = [];

        foreach ($invoices as $invoice) {
            $processed++;
            $documentNumber = "{$invoice->series}-{$invoice->number}";
            $this->line("[{$processed}/{$total}] Enviando {$documentNumber}...");

            try {
                $result = $qpsService->sendInvoiceViaQps($invoice);

                if (($result['success'] ?? false) === true) {
                    $successful++;
                    $this->line("   ✅ {$documentNumber} enviado correctamente.");

                    continue;
                }

                $failed++;
                $errorMessage = (string) ($result['message'] ?? 'Error desconocido');
                $errors[] = "[{$documentNumber}] {$errorMessage}";
                $this->line("   ❌ {$documentNumber} con error: {$errorMessage}");
            } catch (\Throwable $e) {
                $failed++;
                $errors[] = "[{$documentNumber}] {$e->getMessage()}";
                $this->line("   ❌ {$documentNumber} excepción: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("✅ Envíos exitosos: {$successful}");
        $this->line("❌ Envíos fallidos: {$failed}");
        $this->line("📊 Total procesados: {$processed}");

        Log::channel('qps')->info('Tarea diaria SUNAT/QPSE finalizada', [
            'date_filter' => $dateFilter?->toDateString(),
            'limit' => $limit,
            'processed' => $processed,
            'successful' => $successful,
            'failed' => $failed,
        ]);

        if ($failed > 0) {
            Log::channel('qps')->warning('Errores en tarea diaria SUNAT/QPSE', [
                'errors' => $errors,
            ]);

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function parseDateOption(?string $dateOption): ?Carbon
    {
        if (! $dateOption) {
            return null;
        }

        try {
            return Carbon::parse($dateOption)->startOfDay();
        } catch (\Throwable) {
            $this->error('❌ Fecha inválida. Use formato YYYY-MM-DD.');

            return null;
        }
    }

    private function buildValidInvoicesQuery(?Carbon $dateFilter): Builder
    {
        return Invoice::query()
            ->whereIn('invoice_type', ['invoice', 'receipt'])
            ->whereNotNull('series')
            ->whereNotNull('number')
            ->where('total', '>', 0)
            ->whereNull('voided_date')
            ->where(function (Builder $query) {
                $query->whereNull('tax_authority_status')
                    ->orWhereRaw('LOWER(tax_authority_status) <> ?', ['voided']);
            })
            ->where(function (Builder $query) {
                $query->whereNull('sunat_status')
                    ->orWhereIn('sunat_status', ['PENDIENTE', 'ERROR', 'RECHAZADO']);
            })
            ->whereRaw('UPPER(series) NOT LIKE ?', ['NV%'])
            ->when($dateFilter, fn (Builder $query): Builder => $query->whereDate('issue_date', $dateFilter->toDateString()));
    }
}
