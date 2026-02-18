<?php

namespace App\Console\Commands;

use App\Models\CashRegister;
use Illuminate\Console\Command;

class FixCashRegisterDifferences extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cash-registers:fix-differences {--dry-run : Mostrar los cambios sin aplicarlos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige las diferencias de las cajas registradoras cerradas usando la fórmula de cierre: (Contado + Inicial) - Esperado';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔧 Iniciando corrección de diferencias en cajas registradoras...');

        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('⚠️  MODO DRY-RUN: Los cambios no se aplicarán realmente');
        }

        // Obtener todas las cajas cerradas
        $closedCashRegisters = CashRegister::where('is_active', false)
            ->whereNotNull('actual_amount')
            ->whereNotNull('expected_amount')
            ->get();

        if ($closedCashRegisters->isEmpty()) {
            $this->info('ℹ️  No se encontraron cajas cerradas para corregir.');

            return Command::SUCCESS;
        }

        $this->info("📊 Encontradas {$closedCashRegisters->count()} cajas cerradas para revisar");
        $this->newLine();

        $correctedCount = 0;
        $unchangedCount = 0;

        foreach ($closedCashRegisters as $cashRegister) {
            $oldDifference = $cashRegister->difference;

            // Fórmula de cierre: (Contado + Inicial) - Esperado (positivo = sobrante, negativo = faltante)
            $newDifference = ($cashRegister->actual_amount + $cashRegister->opening_amount) - $cashRegister->expected_amount;

            // Verificar si hay cambio
            if (abs($oldDifference - $newDifference) < 0.01) {
                $unchangedCount++;

                continue;
            }

            $this->displayCashRegisterChange($cashRegister, $oldDifference, $newDifference);

            if (! $isDryRun) {
                // Aplicar el cambio
                $cashRegister->difference = $newDifference;
                $cashRegister->save();

                // Actualizar las observaciones si es necesario
                $this->updateObservations($cashRegister, $oldDifference, $newDifference);
            }

            $correctedCount++;
        }

        $this->newLine();
        $this->displaySummary($correctedCount, $unchangedCount, $isDryRun);

        return Command::SUCCESS;
    }

    /**
     * Muestra los cambios de una caja registradora.
     */
    private function displayCashRegisterChange(CashRegister $cashRegister, float $oldDifference, float $newDifference): void
    {
        $this->info("🏦 Caja ID: {$cashRegister->id}");
        $this->line("   📅 Fecha: {$cashRegister->closing_datetime->format('d/m/Y H:i')}");
        $this->line('   💰 Esperado: S/ '.number_format($cashRegister->expected_amount, 2));
        $this->line('   💵 Contado:  S/ '.number_format($cashRegister->actual_amount, 2));

        // Mostrar diferencia anterior
        $oldLabel = $oldDifference > 0 ? 'FALTANTE' : ($oldDifference < 0 ? 'SOBRANTE' : 'SIN DIFERENCIA');
        $this->line('   ❌ Anterior: S/ '.number_format($oldDifference, 2)." ({$oldLabel})");

        // Mostrar diferencia nueva
        $newLabel = $newDifference > 0 ? 'SOBRANTE' : ($newDifference < 0 ? 'FALTANTE' : 'SIN DIFERENCIA');
        $this->line('   ✅ Corregida: S/ '.number_format($newDifference, 2)." ({$newLabel})");

        $this->newLine();
    }

    /**
     * Actualiza las observaciones de la caja con la corrección.
     */
    private function updateObservations(CashRegister $cashRegister, float $oldDifference, float $newDifference): void
    {
        $correctionNote = "\n\n=== CORRECCIÓN AUTOMÁTICA ===\n";
        $correctionNote .= 'Fecha: '.now()->format('d/m/Y H:i')."\n";
        $correctionNote .= "Motivo: Aplicación de fórmula de cierre consistente\n";
        $correctionNote .= 'Diferencia anterior: S/ '.number_format($oldDifference, 2)."\n";
        $correctionNote .= 'Diferencia corregida: S/ '.number_format($newDifference, 2)."\n";
        $correctionNote .= "Nueva fórmula: (Total Contado + Monto Inicial) - Monto Esperado\n";

        if ($cashRegister->observations) {
            $cashRegister->observations .= $correctionNote;
        } else {
            $cashRegister->observations = trim($correctionNote);
        }

        $cashRegister->save();
    }

    /**
     * Muestra el resumen final.
     */
    private function displaySummary(int $correctedCount, int $unchangedCount, bool $isDryRun): void
    {
        if ($isDryRun) {
            $this->warn('⚠️  RESUMEN (DRY-RUN):');
        } else {
            $this->info('✅ RESUMEN FINAL:');
        }

        $this->line("   📊 Cajas corregidas: {$correctedCount}");
        $this->line("   📊 Cajas sin cambios: {$unchangedCount}");
        $this->line('   📊 Total revisadas: '.($correctedCount + $unchangedCount));

        if ($correctedCount > 0) {
            if ($isDryRun) {
                $this->newLine();
                $this->warn('Para aplicar los cambios, ejecute el comando sin --dry-run:');
                $this->line('php artisan cash-registers:fix-differences');
            } else {
                $this->newLine();
                $this->info('🎉 Corrección completada exitosamente!');
                $this->info('✅ Todas las diferencias han sido recalculadas con la nueva fórmula.');
            }
        } else {
            $this->info('ℹ️  No se encontraron cajas que requieran corrección.');
        }
    }
}
