<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\Table;

class CleanupPendingOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:cleanup-pending 
                            {--dry-run : Solo mostrar qué se haría sin ejecutar cambios}
                            {--force : Forzar la limpieza sin confirmación}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Limpia órdenes pendientes que ya están facturadas y libera las mesas correspondientes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 Iniciando limpieza de órdenes pendientes...');

        // Buscar órdenes que están facturadas pero no completadas
        $pendingOrders = Order::whereIn('status', ['open', 'in_preparation', 'ready', 'delivered'])
            ->where('billed', true)
            ->with('table')
            ->get();

        if ($pendingOrders->isEmpty()) {
            $this->info('✅ No hay órdenes pendientes que necesiten limpieza.');
            return 0;
        }

        $this->info("📊 Encontradas {$pendingOrders->count()} órdenes pendientes ya facturadas:");
        
        // Mostrar tabla con las órdenes encontradas
        $tableData = [];
        foreach ($pendingOrders as $order) {
            $tableData[] = [
                'ID' => $order->id,
                'Mesa' => $order->table ? $order->table->number : 'N/A',
                'Estado' => $order->status,
                'Total' => 'S/ ' . number_format($order->total, 2),
                'Fecha' => $order->created_at->format('Y-m-d H:i'),
                'Facturada' => $order->billed ? 'Sí' : 'No'
            ];
        }

        $this->table(['ID', 'Mesa', 'Estado', 'Total', 'Fecha', 'Facturada'], $tableData);

        if ($this->option('dry-run')) {
            $this->warn('🔍 MODO DRY-RUN: No se realizarán cambios.');
            $this->info('Las siguientes acciones se ejecutarían:');
            $this->info("- Marcar {$pendingOrders->count()} órdenes como completadas");
            
            $tablesAffected = $pendingOrders->whereNotNull('table_id')->pluck('table.number')->unique();
            $this->info("- Liberar " . $tablesAffected->count() . " mesas: " . $tablesAffected->implode(', '));
            
            return 0;
        }

        // Confirmar antes de proceder (a menos que se use --force)
        if (!$this->option('force')) {
            if (!$this->confirm('¿Deseas proceder con la limpieza?')) {
                $this->info('❌ Operación cancelada.');
                return 0;
            }
        }

        $this->info('🚀 Procediendo con la limpieza...');

        $completedOrders = 0;
        $liberatedTables = [];

        foreach ($pendingOrders as $order) {
            try {
                // Marcar la orden como completada
                $order->status = Order::STATUS_COMPLETED;
                $order->save();
                $completedOrders++;

                $this->line("✅ Orden #{$order->id} marcada como completada");

                // Si tiene mesa asociada, liberarla
                if ($order->table_id && $order->table) {
                    $table = $order->table;
                    
                    // Verificar si hay otras órdenes pendientes en esta mesa
                    $otherPendingOrders = Order::where('table_id', $table->id)
                        ->whereIn('status', ['open', 'in_preparation', 'ready', 'delivered'])
                        ->where('id', '!=', $order->id)
                        ->count();

                    // Solo liberar la mesa si no hay otras órdenes pendientes
                    if ($otherPendingOrders === 0 && $table->status === Table::STATUS_OCCUPIED) {
                        $table->status = Table::STATUS_AVAILABLE;
                        $table->occupied_at = null;
                        $table->save();
                        
                        $liberatedTables[] = $table->number;
                        $this->line("🔓 Mesa {$table->number} liberada");
                    }
                }

            } catch (\Exception $e) {
                $this->error("❌ Error al procesar orden #{$order->id}: " . $e->getMessage());
            }
        }

        $this->info('');
        $this->info('📈 RESUMEN DE LIMPIEZA:');
        $this->info("✅ Órdenes completadas: {$completedOrders}");
        $this->info("🔓 Mesas liberadas: " . count($liberatedTables) . " (" . implode(', ', $liberatedTables) . ")");
        
        if ($completedOrders > 0) {
            $this->info('');
            $this->info('🎉 Limpieza completada exitosamente!');
            $this->info('💡 Las mesas ahora deberían aparecer en verde (disponibles) en el mapa.');
        }

        return 0;
    }
}
