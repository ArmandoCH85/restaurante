<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductStock extends Model
{
    use HasFactory;

    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'product_stock';

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'warehouse_id',
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
     * Obtiene el producto asociado con este stock.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Obtiene el almacén asociado con este stock.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
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
     * Scope para obtener solo stock disponible.
     */
    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE)
                    ->where('quantity', '>', 0);
    }

    /**
     * Consumir stock utilizando el método FIFO.
     *
     * @param int $productId ID del producto
     * @param float $quantity Cantidad a consumir
     * @param int|null $warehouseId ID del almacén
     * @return array Detalles del consumo
     */
    public static function consumeByFIFO(int $productId, float $quantity, ?int $warehouseId = null): array
    {
        $query = self::where('product_id', $productId)
                    ->where('status', self::STATUS_AVAILABLE)
                    ->where('quantity', '>', 0)
                    ->orderBy('created_at', 'asc'); // FIFO: primero en entrar, primero en salir

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        $stocks = $query->get();
        $remainingQuantity = $quantity;
        $consumedStocks = [];
        $totalConsumed = 0;

        foreach ($stocks as $stock) {
            if ($remainingQuantity <= 0) {
                break;
            }

            $availableQuantity = $stock->quantity;
            $consumeFromThisStock = min($remainingQuantity, $availableQuantity);

            // Actualizar el stock
            $stock->quantity -= $consumeFromThisStock;
            $stock->save();

            $consumedStocks[] = [
                'stock_id' => $stock->id,
                'consumed_quantity' => $consumeFromThisStock,
                'unit_cost' => $stock->unit_cost,
                'warehouse_id' => $stock->warehouse_id
            ];

            $totalConsumed += $consumeFromThisStock;
            $remainingQuantity -= $consumeFromThisStock;
        }

        return [
            'consumed_quantity' => $totalConsumed,
            'remaining_quantity' => $remainingQuantity,
            'consumed_stocks' => $consumedStocks
        ];
    }
}