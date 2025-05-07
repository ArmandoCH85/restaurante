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
     * Obtiene el ingrediente asociado con este stock.
     */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
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

    /**
     * Obtiene los stocks disponibles de un ingrediente ordenados por FIFO (primero en entrar, primero en salir).
     *
     * @param int $ingredientId ID del ingrediente
     * @param int|null $warehouseId ID del almacén (opcional)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getAvailableStocksByFIFO(int $ingredientId, ?int $warehouseId = null)
    {
        $query = self::where('ingredient_id', $ingredientId)
            ->where('status', self::STATUS_AVAILABLE)
            ->where('quantity', '>', 0);

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        // Ordenar por fecha de creación (FIFO) y luego por fecha de vencimiento
        return $query->orderBy('created_at', 'asc')
            ->orderBy('expiry_date', 'asc')
            ->get();
    }

    /**
     * Consume una cantidad de stock utilizando el método FIFO.
     *
     * @param int $ingredientId ID del ingrediente
     * @param float $quantity Cantidad a consumir (positiva)
     * @param int|null $warehouseId ID del almacén (opcional)
     * @return array Detalles del consumo [cantidad_consumida, costo_total]
     */
    public static function consumeByFIFO(int $ingredientId, float $quantity, ?int $warehouseId = null): array
    {
        // Asegurar que la cantidad sea positiva
        $quantityToConsume = abs($quantity);
        $totalConsumed = 0;
        $totalCost = 0;

        // Obtener los stocks disponibles ordenados por FIFO
        $availableStocks = self::getAvailableStocksByFIFO($ingredientId, $warehouseId);

        foreach ($availableStocks as $stock) {
            // Si ya consumimos todo lo necesario, salir del bucle
            if ($quantityToConsume <= 0) {
                break;
            }

            // Determinar cuánto podemos consumir de este stock
            $consumeFromThisStock = min($stock->quantity, $quantityToConsume);

            // Actualizar el stock
            $stock->quantity -= $consumeFromThisStock;

            // Si el stock queda en cero, marcarlo como reservado
            if ($stock->quantity <= 0) {
                $stock->status = self::STATUS_RESERVED;
            }

            $stock->save();

            // Actualizar contadores
            $totalConsumed += $consumeFromThisStock;
            $totalCost += $consumeFromThisStock * $stock->unit_cost;
            $quantityToConsume -= $consumeFromThisStock;
        }

        return [
            'consumed_quantity' => $totalConsumed,
            'total_cost' => $totalCost,
            'average_unit_cost' => $totalConsumed > 0 ? $totalCost / $totalConsumed : 0,
            'remaining_quantity' => $quantityToConsume // Si es > 0, no había suficiente stock
        ];
    }
}
