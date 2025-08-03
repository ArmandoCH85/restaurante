<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixInvoicePaymentMethods extends Command
{
    protected $signature = 'fix:invoice-payment-methods {--dry-run : Solo mostrar qué se corregiría sin hacer cambios}';
    protected $description = 'Corrige los métodos de pago de las facturas basándose en los pagos de las órdenes';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('🔍 MODO DRY-RUN: Solo mostrando qué se corregiría...');
        } else {
            $this->info('🔧 Corrigiendo métodos de pago de facturas...');
        }

        // Obtener facturas que tienen payment_method como 'cash' o null
        $invoices = Invoice::with(['order.payments'])
            ->where(function ($query) {
                $query->where('payment_method', 'cash')
                      ->orWhereNull('payment_method');
            })
            ->get();

        $this->info("📊 Encontradas {$invoices->count()} facturas para revisar");

        $corrected = 0;
        $errors = 0;

        DB::beginTransaction();

        try {
            foreach ($invoices as $invoice) {
                $order = $invoice->order;
                
                if (!$order) {
                    $this->warn("⚠️  Factura {$invoice->series}-{$invoice->number}: No se encontró la orden asociada");
                    $errors++;
                    continue;
                }

                // Obtener el método de pago correcto
                $correctPaymentMethod = $this->getCorrectPaymentMethod($order);
                $correctPaymentAmount = $this->getCorrectPaymentAmount($order);

                // Solo actualizar si es diferente
                if ($invoice->payment_method !== $correctPaymentMethod || 
                    $invoice->payment_amount != $correctPaymentAmount) {
                    
                    $this->line("📝 Factura {$invoice->series}-{$invoice->number}:");
                    $this->line("   Método actual: " . ($invoice->payment_method ?? 'null') . " → Nuevo: {$correctPaymentMethod}");
                    $this->line("   Monto actual: " . ($invoice->payment_amount ?? 'null') . " → Nuevo: {$correctPaymentAmount}");

                    if (!$isDryRun) {
                        $invoice->update([
                            'payment_method' => $correctPaymentMethod,
                            'payment_amount' => $correctPaymentAmount,
                        ]);
                    }
                    
                    $corrected++;
                }
            }

            if (!$isDryRun) {
                DB::commit();
                $this->info("✅ Se corrigieron {$corrected} facturas exitosamente");
            } else {
                DB::rollBack();
                $this->info("✅ Se corregirían {$corrected} facturas");
            }

            if ($errors > 0) {
                $this->warn("⚠️  Se encontraron {$errors} errores");
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ Error durante la corrección: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function getCorrectPaymentMethod(Order $order): string
    {
        // 1. Verificar si la orden tiene payment_method directamente
        if ($order->payment_method && $order->payment_method !== 'cash') {
            return $this->normalizePaymentMethod($order->payment_method);
        }

        // 2. Verificar los pagos asociados a la orden
        $payments = $order->payments;
        
        if ($payments->count() === 1) {
            // Un solo pago - verificar si es billetera digital con tipo específico
            $payment = $payments->first();
            return $this->getPaymentMethodFromPayment($payment);
        } elseif ($payments->count() > 1) {
            // Múltiples pagos - verificar si todos son del mismo tipo
            $uniqueMethods = $payments->map(function($payment) {
                return $this->getPaymentMethodFromPayment($payment);
            })->unique();
            
            if ($uniqueMethods->count() === 1) {
                return $uniqueMethods->first();
            }
            
            // Múltiples métodos diferentes
            return 'multiple';
        }

        // 3. Por defecto, efectivo
        return 'cash';
    }

    private function getPaymentMethodFromPayment($payment): string
    {
        // Verificar si es billetera digital con tipo específico
        if ($payment->payment_method === 'digital_wallet' && $payment->reference_number) {
            if (strpos($payment->reference_number, 'Tipo: yape') !== false) {
                return 'yape';
            } elseif (strpos($payment->reference_number, 'Tipo: plin') !== false) {
                return 'plin';
            }
        }

        return $this->normalizePaymentMethod($payment->payment_method);
    }

    private function normalizePaymentMethod(string $method): string
    {
        // Normalizar métodos de pago a los valores esperados en las facturas
        return match($method) {
            'credit_card', 'debit_card' => 'card',
            'bank_transfer' => 'transfer',
            'digital_wallet' => 'digital_wallet',
            default => $method,
        };
    }

    private function getCorrectPaymentAmount(Order $order): float
    {
        // 1. Verificar si la orden tiene payment_amount directamente
        if ($order->payment_amount && $order->payment_amount > 0) {
            return $order->payment_amount;
        }

        // 2. Sumar los pagos asociados a la orden
        $totalPaid = $order->payments->sum('amount');
        
        if ($totalPaid > 0) {
            return $totalPaid;
        }

        // 3. Por defecto, el total de la orden
        return $order->total;
    }
}