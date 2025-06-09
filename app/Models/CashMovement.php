<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CashMovement extends Model
{
    use HasFactory;

    /**
     * Los tipos de movimientos disponibles.
     */
    const TYPE_INCOME = 'income';
    const TYPE_EXPENSE = 'expense';

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cash_register_id',
        'movement_type',
        'amount',
        'reason',
        'approved_by',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Obtiene la caja registradora asociada con este movimiento.
     */
    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class, 'cash_register_id');
    }

    /**
     * Obtiene el usuario que aprobó este movimiento.
     */
    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Obtiene la referencia asociada con este movimiento.
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Crear un movimiento de ingreso.
     *
     * @param int $cashRegisterId ID de la caja registradora
     * @param float $amount Monto del ingreso
     * @param string $reason Motivo del ingreso
     * @param int $approvedBy ID del usuario que aprueba
     * @return self
     */
    public static function createIncome(int $cashRegisterId, float $amount, string $reason, int $approvedBy): self
    {
        return self::create([
            'cash_register_id' => $cashRegisterId,
            'movement_type' => self::TYPE_INCOME,
            'amount' => $amount,
            'reason' => $reason,
            'approved_by' => $approvedBy,
        ]);
    }

    /**
     * Crear un movimiento de egreso.
     *
     * @param int $cashRegisterId ID de la caja registradora
     * @param float $amount Monto del egreso
     * @param string $reason Motivo del egreso
     * @param int $approvedBy ID del usuario que aprueba
     * @return self
     */
    public static function createExpense(int $cashRegisterId, float $amount, string $reason, int $approvedBy): self
    {
        return self::create([
            'cash_register_id' => $cashRegisterId,
            'movement_type' => self::TYPE_EXPENSE,
            'amount' => $amount,
            'reason' => $reason,
            'approved_by' => $approvedBy,
        ]);
    }

    /**
     * Devuelve el nombre legible del tipo de movimiento.
     */
    public function getMovementTypeNameAttribute(): string
    {
        return match($this->movement_type) {
            self::TYPE_INCOME => 'Ingreso',
            self::TYPE_EXPENSE => 'Egreso',
            default => $this->movement_type,
        };
    }

    /**
     * Scope para filtrar movimientos de ingreso.
     */
    public function scopeIncomes($query)
    {
        return $query->where('movement_type', self::TYPE_INCOME);
    }

    /**
     * Scope para filtrar movimientos de egreso.
     */
    public function scopeExpenses($query)
    {
        return $query->where('movement_type', self::TYPE_EXPENSE);
    }
}
