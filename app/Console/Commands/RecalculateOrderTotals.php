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
    protected $signature = 'orders:recalculate-totals
                            {--all : Recalcular todas las órdenes}
                            {--zero-tax : Solo órdenes con IGV en 0}
                            {--dry-run : Solo mostrar qué se haría sin ejecutar cambios}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalcula los totales de las órdenes usando IGV incluido (normativa peruana)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $this->info('🧮 Iniciando recálculo de totales con IGV incluido...');

        if ($dryRun) {
            $this->warn('⚠️  MODO DRY-RUN: No se realizarán cambios reales');
        }

        $query = Order::query();

        if ($this->option('zero-tax')) {
            // Solo órdenes con IGV en 0
            $query->where('tax', 0);
            $this->info('📊 Recalculando solo órdenes con IGV en 0...');
        } elseif ($this->option('all')) {
            // Todas las órdenes
            $this->info('📊 Recalculando todas las órdenes...');
        } else {
            // Por defecto, solo órdenes con IGV en 0
            $query->where('tax', 0);
            $this->info('📊 Recalculando órdenes con IGV en 0 (usar --all para todas las órdenes)...');
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
            $oldSubtotal = $order->subtotal;
            $oldTax = $order->tax;
            $oldTotal = $order->total;

            if (!$dryRun) {
                // Recalcular totales usando IGV incluido
                $order->recalculateTotals();
            } else {
                // Simular cálculo para dry-run
                $totalWithIgv = $order->orderDetails->sum('subtotal');
                $totalWithIgvAfterDiscount = $totalWithIgv - ($order->discount ?? 0);
                $order->subtotal = round($totalWithIgvAfterDiscount / 1.18, 2);
                $order->tax = round($totalWithIgvAfterDiscount / 1.18 * 0.18, 2);
                $order->total = $totalWithIgvAfterDiscount;
            }

            if (abs($order->subtotal - $oldSubtotal) > 0.01 ||
                abs($order->tax - $oldTax) > 0.01 ||
                abs($order->total - $oldTotal) > 0.01) {

                $updated++;
                $this->line('');
                $this->info("📝 Orden #{$order->id}:");
                $this->line("   Subtotal: S/ {$oldSubtotal} → S/ {$order->subtotal}");
                $this->line("   IGV:      S/ {$oldTax} → S/ {$order->tax}");
                $this->line("   Total:    S/ {$oldTotal} → S/ {$order->total}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->line('');

        if ($dryRun) {
            $this->warn("⚠️  Para aplicar los cambios, ejecuta el comando sin --dry-run");
        } else {
            $this->info("✅ Proceso completado. {$updated} órdenes fueron actualizadas.");
        }
    }
}
