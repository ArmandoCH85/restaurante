<?php

namespace App\Console\Commands;

use App\Models\CashRegister;
use Illuminate\Console\Command;

class RecalculateAllCashRegisterDifferences extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cash-registers:recalculate-differences';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalcula todas las diferencias de cajas registradoras usando la fórmula correcta';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Recalculando diferencias de cajas registradoras...');

        // Obtener todas las cajas cerradas
        $cashRegisters = CashRegister::where('is_active', false)
            ->whereNotNull('actual_amount')
            ->whereNotNull('expected_amount')
            ->get();

        if ($cashRegisters->isEmpty()) {
            $this->info('ℹ️  No hay cajas cerradas para recalcular.');

            return Command::SUCCESS;
        }

        $updated = 0;

        foreach ($cashRegisters as $cashRegister) {
            // Fórmula de cierre: (Contado + Inicial) - Esperado
            $newDifference = ($cashRegister->actual_amount + $cashRegister->opening_amount) - $cashRegister->expected_amount;

            if ($cashRegister->difference != $newDifference) {
                $cashRegister->update(['difference' => $newDifference]);
                $updated++;
            }
        }

        $this->info("✅ Procesadas {$cashRegisters->count()} cajas registradoras");
        $this->info("🔄 Actualizadas {$updated} cajas con diferencias corregidas");

        return Command::SUCCESS;
    }
}
