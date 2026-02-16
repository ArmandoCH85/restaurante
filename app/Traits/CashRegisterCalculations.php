<?php

namespace App\Traits;

use App\Enums\PaymentMethodEnum;
use Illuminate\Support\Facades\Cache;

trait CashRegisterCalculations
{
    protected array $cachedSales = [];

    protected function getSalesByMethod(PaymentMethodEnum $method): float
    {
        $cacheKey = "sales_{$method->value}_{$this->id}";

        if (isset($this->cachedSales[$cacheKey])) {
            return $this->cachedSales[$cacheKey];
        }

        $query = $this->payments()
            ->whereNull('void_reason');

        match ($method) {
            PaymentMethodEnum::CASH => $query->where('payment_method', 'cash'),
            PaymentMethodEnum::CARD => $query->whereIn('payment_method', ['card', 'credit_card', 'debit_card']),
            PaymentMethodEnum::YAPE => $query->where('payment_method', 'yape'),
            PaymentMethodEnum::PLIN => $query->where('payment_method', 'plin'),
            PaymentMethodEnum::DIDI_FOOD => $query->where('payment_method', 'didi_food'),
            PaymentMethodEnum::PEDIDOS_YA => $query->where('payment_method', 'pedidos_ya'),
            PaymentMethodEnum::BITA_EXPRESS => $query->where('payment_method', 'bita_express'),
            PaymentMethodEnum::BANK_TRANSFER => $query->where('payment_method', 'bank_transfer'),
            PaymentMethodEnum::DIGITAL_WALLET => $query->where('payment_method', 'digital_wallet')
                ->where(function ($q) {
                    $q->where('reference_number', 'NOT LIKE', '%Tipo: yape%')
                        ->where('reference_number', 'NOT LIKE', '%Tipo: plin%');
                }),
            default => $query->where('payment_method', $method->value),
        };

        $this->cachedSales[$cacheKey] = (float) $query->sum('amount');

        return $this->cachedSales[$cacheKey];
    }

    public function getCachedExpenses(): float
    {
        $cacheKey = "expenses_{$this->id}";

        if (isset($this->cachedSales[$cacheKey])) {
            return $this->cachedSales[$cacheKey];
        }

        $this->cachedSales[$cacheKey] = (float) $this->cashRegisterExpenses()->sum('amount');

        return $this->cachedSales[$cacheKey];
    }

    public function getAllSalesByMethod(): array
    {
        $sales = [];
        foreach (PaymentMethodEnum::forCashRegisterComparison() as $method) {
            $sales[$method->value] = $this->getSalesByMethod($method);
        }

        $sales['bank_transfer'] = $this->getSalesByMethod(PaymentMethodEnum::BANK_TRANSFER);
        $sales['other_digital_wallet'] = $this->getSalesByMethod(PaymentMethodEnum::DIGITAL_WALLET);

        return $sales;
    }

    public function getTotalSystemSales(): float
    {
        return array_sum($this->getAllSalesByMethod());
    }

    public function calculateExpectedCash(): float
    {
        return ($this->opening_amount + $this->getTotalSystemSales()) - $this->getCachedExpenses();
    }

    public function calculateDifference(float $manualTotal): float
    {
        return $manualTotal - $this->getTotalSystemSales();
    }

    public function calculateTotalManual(array $manualData): float
    {
        $total = 0;
        foreach (PaymentMethodEnum::forCashRegisterComparison() as $method) {
            $field = $method->getManualFieldName();
            $total += (float) ($manualData[$field] ?? 0);
        }

        return $total;
    }
}
