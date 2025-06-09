<?php

namespace App\Enums;

enum InvoiceStatusEnum: string
{
    case PAID = 'paid';
    case PENDING = 'pending';
    case CANCELLED = 'cancelled';
    case DRAFT = 'draft';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PAID => 'Pagado',
            self::PENDING => 'Pendiente',
            self::CANCELLED => 'Anulado',
            self::DRAFT => 'Borrador',
        };
    }
}
