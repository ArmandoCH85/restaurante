<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashRegister extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'opened_by',
        'closed_by',
        'opening_amount',
        'expected_amount',
        'actual_amount',
        'difference',
        'opening_datetime',
        'closing_datetime',
        'observations',
        'is_active',
        'cash_sales',
        'card_sales',
        'other_sales',
        'total_sales',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'opening_amount' => 'decimal:2',
        'expected_amount' => 'decimal:2',
        'actual_amount' => 'decimal:2',
        'difference' => 'decimal:2',
        'cash_sales' => 'decimal:2',
        'card_sales' => 'decimal:2',
        'other_sales' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'opening_datetime' => 'datetime',
        'closing_datetime' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Estados de la caja
     */
    const STATUS_OPEN = 1; // is_active = true
    const STATUS_CLOSED = 0; // is_active = false

    /**
     * Obtiene el usuario que abrió la caja.
     */
    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    /**
     * Obtiene el usuario que cerró la caja.
     */
    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /**
     * Obtiene los pagos asociados a este cierre de caja.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Verifica si la caja está abierta.
     */
    public function isOpen(): bool
    {
        return $this->is_active === self::STATUS_OPEN;
    }

    /**
     * Verifica si la caja está cerrada.
     */
    public function isClosed(): bool
    {
        return $this->is_active === self::STATUS_CLOSED;
    }

    /**
     * Cierra la caja registradora.
     */
    public function close(array $data): bool
    {
        $this->closed_by = $data['closed_by'];
        $this->actual_amount = $data['actual_cash'];
        $this->expected_amount = $data['expected_cash'];
        $this->difference = $data['difference'];
        $this->closing_datetime = now();
        $this->observations = $data['notes'] ?? null;
        $this->is_active = self::STATUS_CLOSED;

        // Agregar nuevos campos de ventas
        $this->cash_sales = $data['cash_sales'] ?? 0;
        $this->card_sales = $data['card_sales'] ?? 0;
        $this->other_sales = $data['other_sales'] ?? 0;
        $this->total_sales = $data['total_sales'] ?? 0;

        return $this->save();
    }
}
