<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;

class RecalculateOrderTotals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:recalculate-totals {--all : Recalcular todas las órdenes} {--zero-tax : Solo órdenes con IGV en 0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalcula los totales de las órdenes (subtotal, IGV y total)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando recálculo de totales de órdenes...');

        $query = Order::query();

        if ($this->option('zero-tax')) {
            // Solo órdenes con IGV en 0
            $query->where('tax', 0);
            $this->info('Recalculando solo órdenes con IGV en 0...');
        } elseif ($this->option('all')) {
            // Todas las órdenes
            $this->info('Recalculando todas las órdenes...');
        } else {
            // Por defecto, solo órdenes con IGV en 0
            $query->where('tax', 0);
            $this->info('Recalculando órdenes con IGV en 0 (usar --all para todas las órdenes)...');
        }

        $orders = $query->with('orderDetails')->get();
        $count = $orders->count();

        if ($count === 0) {
            $this->info('No se encontraron órdenes para recalcular.');
            return;
        }

        $this->info("Se encontraron {$count} órdenes para recalcular.");

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $updated = 0;

        foreach ($orders as $order) {
            $oldTax = $order->tax;
            $oldTotal = $order->total;

            // Recalcular totales
            $order->recalculateTotals();

            if ($order->tax != $oldTax || $order->total != $oldTotal) {
                $updated++;
                $this->line('');
                $this->info("Orden #{$order->id}: IGV {$oldTax} → {$order->tax}, Total {$oldTotal} → {$order->total}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->line('');
        $this->info("Proceso completado. {$updated} órdenes fueron actualizadas.");
    }
}
