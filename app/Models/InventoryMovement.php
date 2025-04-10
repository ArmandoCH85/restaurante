<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventoryMovement extends Model
{
    use HasFactory;

    /**
     * Los tipos de movimientos disponibles.
     */
    const TYPE_PURCHASE = 'purchase';
    const TYPE_SALE = 'sale';
    const TYPE_ADJUSTMENT = 'adjustment';
    const TYPE_WASTE = 'waste';

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'movement_type',
        'quantity',
        'unit_cost',
        'reference_document',
        'reference_id',
        'reference_type',
        'created_by',
        'notes'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_cost' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Obtiene el producto asociado con este movimiento.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Obtiene el ingredient asociado con este movimiento.
     */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class, 'product_id');
    }

    /**
     * Obtiene el usuario que creó este movimiento.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Obtiene la entidad de referencia (orden, compra, etc.).
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Crear un movimiento de ingreso por compra y actualizar el stock.
     */
    public static function createPurchaseMovement(
        int $ingredientId,
        float $quantity,
        float $unitCost,
        int $purchaseId,
        string $referenceDocument = null,
        int $createdBy = null,
        string $notes = null
    ): self {
        $movement = self::create([
            'product_id' => $ingredientId,
            'movement_type' => self::TYPE_PURCHASE,
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'reference_document' => $referenceDocument,
            'reference_id' => $purchaseId,
            'reference_type' => 'App\\Models\\Purchase',
            'created_by' => $createdBy,
            'notes' => $notes
        ]);

        // Actualizar stock del ingrediente
        $ingredient = Ingredient::find($ingredientId);
        if ($ingredient) {
            $ingredient->updateStock($quantity, $unitCost);
        }

        return $movement;
    }

    /**
     * Crear un movimiento de salida por venta y actualizar el stock.
     */
    public static function createSaleMovement(
        int $ingredientId,
        float $quantity,
        int $orderId,
        string $referenceDocument = null,
        int $createdBy = null,
        string $notes = null
    ): self {
        // Obtener el costo actual del ingrediente
        $ingredient = Ingredient::find($ingredientId);
        $unitCost = $ingredient ? $ingredient->current_cost : 0;

        $movement = self::create([
            'product_id' => $ingredientId,
            'movement_type' => self::TYPE_SALE,
            'quantity' => -1 * abs($quantity), // Siempre negativo para salidas
            'unit_cost' => $unitCost,
            'reference_document' => $referenceDocument,
            'reference_id' => $orderId,
            'reference_type' => 'App\\Models\\Order',
            'created_by' => $createdBy,
            'notes' => $notes
        ]);

        // Actualizar stock del ingrediente
        if ($ingredient) {
            $ingredient->updateStock(-1 * abs($quantity));
        }

        return $movement;
    }
}
