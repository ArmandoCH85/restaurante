<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanAbandonedOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'orders:clean-abandoned {--hours=24 : Hours after which orders are considered abandoned}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean abandoned orders that are open and unbilled after specified hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $hours = (int) $this->option('hours');
        
        $this->info("🧹 Iniciando limpieza de órdenes abandonadas (>{$hours} horas)...");
        
        try {
            // Contar órdenes a limpiar
            $toClean = Order::where('status', Order::STATUS_OPEN)
                ->where('billed', false)
                ->where('created_at', '<', now()->subHours($hours))
                ->count();
                
            if ($toClean === 0) {
                $this->info('✅ No hay órdenes abandonadas para limpiar');
                return 0;
            }
            
            $this->warn("⚠️  Se encontraron {$toClean} órdenes abandonadas");
            
            // Obtener detalles para logging
            $ordersToDelete = Order::where('status', Order::STATUS_OPEN)
                ->where('billed', false)
                ->where('created_at', '<', now()->subHours($hours))
                ->select('id', 'employee_id', 'table_id', 'total', 'created_at')
                ->get();
            
            // Log detallado antes de eliminar
            Log::info('🧹 Limpieza programada de órdenes abandonadas', [
                'hours_threshold' => $hours,
                'orders_to_clean' => $toClean,
                'orders_details' => $ordersToDelete->map(function($order) {
                    return [
                        'id' => $order->id,
                        'employee_id' => $order->employee_id,
                        'table_id' => $order->table_id,
                        'total' => $order->total,
                        'age_hours' => $order->created_at->diffInHours(now())
                    ];
                })->toArray()
            ]);
            
            // Eliminar órdenes abandonadas
            $cleaned = Order::where('status', Order::STATUS_OPEN)
                ->where('billed', false)
                ->where('created_at', '<', now()->subHours($hours))
                ->delete();
            
            $this->info("✅ Órdenes abandonadas limpiadas: {$cleaned}");
            
            // Log de resultado
            Log::info('✅ Limpieza programada completada', [
                'cleaned_count' => $cleaned,
                'hours_threshold' => $hours,
                'execution_time' => now()
            ]);
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Error durante la limpieza: {$e->getMessage()}");
            
            Log::error('❌ Error en limpieza programada de órdenes', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'hours_threshold' => $hours
            ]);
            
            return 1;
        }
    }
}