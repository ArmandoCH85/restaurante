<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseDetail extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->subtotal = $model->quantity * $model->unit_cost;
        });

        static::updating(function ($model) {
            $model->subtotal = $model->quantity * $model->unit_cost;
        });
    }

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'purchase_id',
        'product_id',
        'quantity',
        'unit_cost',
        'subtotal',
        'expiry_date'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'expiry_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Obtiene la compra a la que pertenece este detalle.
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Obtiene el producto asociado con este detalle.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Calcula el subtotal de este detalle de compra.
     */
    public function calculateSubtotal(): float
    {
        return $this->quantity * $this->unit_cost;
    }
}
