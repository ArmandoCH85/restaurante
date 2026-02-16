<?php

namespace App\Enums;

enum PaymentMethodEnum: string
{
    case CASH = 'cash';
    case CARD = 'card';
    case CREDIT_CARD = 'credit_card';
    case DEBIT_CARD = 'debit_card';
    case BANK_TRANSFER = 'bank_transfer';
    case DIGITAL_WALLET = 'digital_wallet';
    case YAPE = 'yape';
    case PLIN = 'plin';
    case RAPPI = 'rappi';
    case DIDI_FOOD = 'didi_food';
    case PEDIDOS_YA = 'pedidos_ya';
    case BITA_EXPRESS = 'bita_express';

    public function getLabel(): string
    {
        return match ($this) {
            self::CASH => 'Efectivo',
            self::CARD => 'Tarjeta',
            self::CREDIT_CARD => 'Tarjeta de Crédito',
            self::DEBIT_CARD => 'Tarjeta de Débito',
            self::BANK_TRANSFER => 'Transferencia Bancaria',
            self::DIGITAL_WALLET => 'Billetera Digital',
            self::YAPE => 'Yape',
            self::PLIN => 'Plin',
            self::RAPPI => 'Rappi',
            self::DIDI_FOOD => 'Didi Food',
            self::PEDIDOS_YA => 'PedidosYa',
            self::BITA_EXPRESS => 'Bita Express',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::CASH => '💵',
            self::CARD, self::CREDIT_CARD, self::DEBIT_CARD => '💳',
            self::BANK_TRANSFER => '🏦',
            self::DIGITAL_WALLET => '📱',
            self::YAPE => '💜',
            self::PLIN => '🟢',
            self::RAPPI => '🟠',
            self::DIDI_FOOD => '🟧',
            self::PEDIDOS_YA => '🔴',
            self::BITA_EXPRESS => '🔵',
        };
    }

    public function isCash(): bool
    {
        return $this === self::CASH;
    }

    public function isCard(): bool
    {
        return in_array($this, [self::CARD, self::CREDIT_CARD, self::DEBIT_CARD], true);
    }

    public function isDigitalWallet(): bool
    {
        return in_array($this, [self::DIGITAL_WALLET, self::YAPE, self::PLIN], true);
    }

    public function isDeliveryApp(): bool
    {
        return in_array($this, [self::RAPPI, self::DIDI_FOOD, self::PEDIDOS_YA, self::BITA_EXPRESS], true);
    }

    public static function forCashRegisterComparison(): array
    {
        return [
            self::CASH,
            self::YAPE,
            self::PLIN,
            self::CARD,
            self::DIDI_FOOD,
            self::PEDIDOS_YA,
            self::BITA_EXPRESS,
        ];
    }

    public function getManualFieldName(): string
    {
        return match ($this) {
            self::CASH => 'manual_cash',
            self::YAPE => 'manual_yape',
            self::PLIN => 'manual_plin',
            self::CARD => 'manual_card',
            self::DIDI_FOOD => 'manual_didi',
            self::PEDIDOS_YA => 'manual_pedidos_ya',
            self::BITA_EXPRESS => 'manual_bita_express',
            default => 'manual_' . $this->value,
        };
    }
}
