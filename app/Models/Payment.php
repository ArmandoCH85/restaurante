<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'cash_register_id',
        'payment_method',
        'amount',
        'reference_number',
        'payment_datetime',
        'received_by',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'payment_datetime' => 'datetime',
    ];

    /**
     * Métodos de pago disponibles
     */
    const METHOD_CASH = 'cash';
    const METHOD_CREDIT_CARD = 'credit_card';
    const METHOD_DEBIT_CARD = 'debit_card';
    const METHOD_BANK_TRANSFER = 'bank_transfer';
    const METHOD_DIGITAL_WALLET = 'digital_wallet';

    /**
     * Obtiene la orden asociada al pago.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Obtiene el usuario que recibió el pago.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Devuelve el nombre legible del método de pago.
     */
    public function getPaymentMethodNameAttribute(): string
    {
        return match($this->payment_method) {
            self::METHOD_CASH => 'Efectivo',
            self::METHOD_CREDIT_CARD => 'Tarjeta de Crédito',
            self::METHOD_DEBIT_CARD => 'Tarjeta de Débito',
            self::METHOD_BANK_TRANSFER => 'Transferencia Bancaria',
            self::METHOD_DIGITAL_WALLET => 'Billetera Digital',
            default => $this->payment_method,
        };
    }

    /**
     * Obtiene la caja registradora asociada al pago.
     */
    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }
}
