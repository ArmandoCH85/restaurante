<?php

namespace App\Console\Commands;

use App\Models\CashRegister;
use App\Models\Payment;
use Illuminate\Console\Command;

class DebugCashRegisterPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:cash-register-payments {cash_register_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debuggea los pagos de una caja registradora específica';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cashRegisterId = $this->argument('cash_register_id');
        
        $cashRegister = CashRegister::find($cashRegisterId);
        
        if (!$cashRegister) {
            $this->error("❌ No se encontró la caja registradora con ID: {$cashRegisterId}");
            return Command::FAILURE;
        }
        
        $this->info("🏦 Debuggeando caja registradora ID: {$cashRegisterId}");
        $this->info("📅 Fecha apertura: {$cashRegister->opening_datetime}");
        $this->info("📅 Fecha cierre: " . ($cashRegister->closing_datetime ?: 'En curso'));
        $this->newLine();
        
        // Obtener todos los pagos de esta caja
        $payments = Payment::where('cash_register_id', $cashRegisterId)->get();
        
        $this->info("💰 Total de pagos encontrados: {$payments->count()}");
        $this->newLine();
        
        if ($payments->isEmpty()) {
            $this->warn("⚠️  No se encontraron pagos para esta caja registradora.");
            
            // Buscar pagos en el rango de fechas de la caja
            $this->info("🔍 Buscando pagos en el rango de fechas de la caja...");
            
            $paymentsInRange = Payment::whereBetween('payment_datetime', [
                $cashRegister->opening_datetime,
                $cashRegister->closing_datetime ?: now()
            ])->get();
            
            $this->info("📊 Pagos en el rango de fechas: {$paymentsInRange->count()}");
            
            if ($paymentsInRange->isNotEmpty()) {
                $this->warn("⚠️  Hay pagos en el rango de fechas pero sin cash_register_id correcto:");
                foreach ($paymentsInRange as $payment) {
                    $this->line("   - ID: {$payment->id}, Método: {$payment->payment_method}, Monto: S/{$payment->amount}, Cash Register ID: {$payment->cash_register_id}");
                }
            }
            
            return Command::SUCCESS;
        }
        
        // Agrupar pagos por método
        $paymentsByMethod = $payments->groupBy('payment_method');
        
        $this->info("📊 RESUMEN POR MÉTODO DE PAGO:");
        $this->table(
            ['Método de Pago', 'Cantidad', 'Monto Total'],
            $paymentsByMethod->map(function ($methodPayments, $method) {
                return [
                    $method,
                    $methodPayments->count(),
                    'S/ ' . number_format($methodPayments->sum('amount'), 2)
                ];
            })->toArray()
        );
        
        $this->newLine();
        
        // Detalles de billeteras digitales
        $digitalWalletPayments = $payments->where('payment_method', Payment::METHOD_DIGITAL_WALLET);
        
        if ($digitalWalletPayments->isNotEmpty()) {
            $this->info("📱 DETALLE DE BILLETERAS DIGITALES:");
            
            $yapePayments = $digitalWalletPayments->filter(function ($payment) {
                return strpos($payment->reference_number, 'Tipo: yape') !== false;
            });
            
            $plinPayments = $digitalWalletPayments->filter(function ($payment) {
                return strpos($payment->reference_number, 'Tipo: plin') !== false;
            });
            
            $otherDigitalPayments = $digitalWalletPayments->filter(function ($payment) {
                return strpos($payment->reference_number, 'Tipo: yape') === false &&
                       strpos($payment->reference_number, 'Tipo: plin') === false;
            });
            
            $this->line("💚 Yape: {$yapePayments->count()} pagos - S/ " . number_format($yapePayments->sum('amount'), 2));
            $this->line("💜 Plin: {$plinPayments->count()} pagos - S/ " . number_format($plinPayments->sum('amount'), 2));
            $this->line("🔵 Otros digitales: {$otherDigitalPayments->count()} pagos - S/ " . number_format($otherDigitalPayments->sum('amount'), 2));
            
            $this->newLine();
        }
        
        // Verificar métodos del modelo
        $this->info("🔧 VERIFICANDO MÉTODOS DEL MODELO:");
        $this->line("💚 getSystemYapeSales(): S/ " . number_format($cashRegister->getSystemYapeSales(), 2));
        $this->line("💜 getSystemPlinSales(): S/ " . number_format($cashRegister->getSystemPlinSales(), 2));
        $this->line("💰 getSystemCashSales(): S/ " . number_format($cashRegister->getSystemCashSales(), 2));
        $this->line("💳 getSystemCardSales(): S/ " . number_format($cashRegister->getSystemCardSales(), 2));
        $this->newLine();
        
        // Mostrar algunos pagos de ejemplo
        if ($payments->count() > 0) {
            $this->info("📝 EJEMPLOS DE PAGOS (primeros 5):");
            $this->table(
                ['ID', 'Método', 'Reference Number', 'Monto', 'Fecha'],
                $payments->take(5)->map(function ($payment) {
                    return [
                        $payment->id,
                        $payment->payment_method,
                        $payment->reference_number ?: 'N/A',
                        'S/ ' . number_format($payment->amount, 2),
                        $payment->payment_datetime->format('d/m/Y H:i')
                    ];
                })->toArray()
            );
        }
        
        return Command::SUCCESS;
    }
}