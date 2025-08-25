<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\Table;

class FixCompletedOrdersWithInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:completed-orders-with-invoices {--dry-run : Ejecutar sin hacer cambios}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige órdenes que tienen comprobantes pero no están marcadas como completadas';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('🧪 Modo de prueba (dry-run) activado - no se realizarán cambios');
        } else {
            $this->info('🔄 Corrigiendo órdenes con comprobantes pero sin estado completado...');
        }

        // Encontrar órdenes que tienen comprobantes pero no están completadas
        $orders = Order::whereHas('invoices')
            ->where('status', '!=', 'completed')
            ->with(['table', 'invoices'])
            ->get();

        if ($orders->isEmpty()) {
            $this->info('✅ No se encontraron órdenes con problemas');
            return 0;
        }

        $this->info("🔍 Se encontraron {$orders->count()} órdenes con comprobantes pero sin estado completado");

        $progressBar = $this->output->createProgressBar($orders->count());
        $progressBar->start();

        $fixedOrders = 0;
        $fixedTables = 0;

        foreach ($orders as $order) {
            $progressBar->advance();
            
            // Verificar si la orden tiene comprobantes
            if ($order->invoices->count() > 0) {
                if (!$dryRun) {
                    // Marcar la orden como completada y facturada
                    $order->update([
                        'status' => 'completed',
                        'billed' => true,
                    ]);
                    
                    // Liberar la mesa si existe
                    if ($order->table) {
                        $order->table->update([
                            'status' => 'available',
                            'occupied_at' => null,
                        ]);
                        $fixedTables++;
                    }
                    
                    $fixedOrders++;
                } else {
                    // En modo dry-run, solo mostrar información
                    $tableId = $order->table ? $order->table->id : 'N/A';
                    $this->line("\n📝 Orden #{$order->id} con {$order->invoices->count()} comprobante(s) - mesa #{$tableId}");
                }
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        if ($dryRun) {
            $this->info("📊 Se encontraron {$orders->count()} órdenes que necesitan corrección");
            $this->info('💡 Para aplicar los cambios, ejecuta el comando sin la opción --dry-run');
        } else {
            $this->info("✅ Se corrigieron {$fixedOrders} órdenes y se liberaron {$fixedTables} mesas");
        }

        return 0;
    }
}