<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IngredientStock extends Model
{
    use HasFactory;

    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'ingredient_stock';

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ingredient_id',
        'quantity',
        'unit_cost',
        'expiry_date',
        'status',
        'purchase_id'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'expiry_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Estados de stock
     */
    const STATUS_AVAILABLE = 'available';
    const STATUS_RESERVED = 'reserved';
    const STATUS_EXPIRED = 'expired';

    /**
     * Obtiene el ingrediente asociado con este stock.
     */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    /**
     * Obtiene la compra relacionada con este stock.
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Verificar si el stock ha expirado.
     */
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Actualizar el estado del stock según la fecha de vencimiento.
     */
    public function updateStatusBasedOnExpiry(): bool
    {
        if ($this->isExpired() && $this->status !== self::STATUS_EXPIRED) {
            $this->status = self::STATUS_EXPIRED;
            return $this->save();
        }

        return false;
    }
}
