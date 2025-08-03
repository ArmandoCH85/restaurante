<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckPaymentMethods extends Command
{
    protected $signature = 'check:payment-methods';
    protected $description = 'Verifica los métodos de pago en las órdenes';

    public function handle()
    {
        $this->info('🔍 Verificando métodos de pago en órdenes...');
        
        // Verificar métodos de pago en orders
        $orderPayments = DB::table('orders')
            ->select('payment_method', DB::raw('COUNT(*) as count'))
            ->whereNotNull('payment_method')
            ->groupBy('payment_method')
            ->get();
            
        $this->info('📊 Métodos de pago en tabla orders:');
        foreach ($orderPayments as $payment) {
            $this->line("   {$payment->payment_method}: {$payment->count} órdenes");
        }
        
        // Verificar si hay registros en la tabla payments
        $paymentsCount = DB::table('payments')->count();
        $this->info("💳 Total de registros en tabla payments: {$paymentsCount}");
        
        if ($paymentsCount > 0) {
            $paymentMethods = DB::table('payments')
                ->select('payment_method', DB::raw('COUNT(*) as count'))
                ->groupBy('payment_method')
                ->get();
                
            $this->info('📊 Métodos de pago en tabla payments:');
            foreach ($paymentMethods as $payment) {
                $this->line("   {$payment->payment_method}: {$payment->count} pagos");
            }
        }
        
        // Verificar órdenes con yape o plin
        $yapeOrders = DB::table('orders')->where('payment_method', 'yape')->count();
        $plinOrders = DB::table('orders')->where('payment_method', 'plin')->count();
        
        $this->info("📱 Órdenes con Yape: {$yapeOrders}");
        $this->info("💙 Órdenes con Plin: {$plinOrders}");
        
        return 0;
    }
}