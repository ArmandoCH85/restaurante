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
     *
     * @param int $productId ID del producto
     * @param float $quantity Cantidad (positiva para ingresos)
     * @param float $unitCost Costo unitario
     * @param int $purchaseId ID de la compra
     * @param string|null $referenceDocument Número de documento de referencia
     * @param int|null $createdBy ID del usuario que crea el movimiento
     * @param string|null $notes Notas adicionales
     * @return self
     */
    public static function createPurchaseMovement(
        int $productId,
        float $quantity,
        float $unitCost,
        int $purchaseId,
        ?string $referenceDocument = null,
        ?int $createdBy = null,
        ?string $notes = null
    ): self {
        $movement = self::create([
            'product_id' => $productId,
            'movement_type' => self::TYPE_PURCHASE,
            'quantity' => abs($quantity), // Asegurar que sea positivo para ingresos
            'unit_cost' => $unitCost,
            'reference_document' => $referenceDocument,
            'reference_id' => $purchaseId,
            'reference_type' => Purchase::class,
            'created_by' => $createdBy,
            'notes' => $notes
        ]);

        // Actualizar stock del producto (puede ser ingrediente u otro tipo)
        $product = Product::find($productId);
        if ($product) {
            // Si es un ingrediente, usar su método específico
            if ($product instanceof Ingredient) {
                $product->updateStock(abs($quantity), $unitCost);
            } else {
                // Para otros productos, actualizar directamente el stock
                $product->current_stock = ($product->current_stock ?? 0) + abs($quantity);
                $product->current_cost = $unitCost;
                $product->save();
            }
        }

        return $movement;
    }

    /**
     * Crear un movimiento de salida por venta y actualizar el stock.
     *
     * @param int $productId ID del producto
     * @param float $quantity Cantidad (positiva, se convertirá a negativa para salidas)
     * @param int $orderId ID de la orden
     * @param string|null $referenceDocument Número de documento de referencia
     * @param int|null $createdBy ID del usuario que crea el movimiento
     * @param string|null $notes Notas adicionales
     * @return self
     */
    public static function createSaleMovement(
        int $productId,
        float $quantity,
        int $orderId,
        ?string $referenceDocument = null,
        ?int $createdBy = null,
        ?string $notes = null
    ): self {
        // Obtener el costo actual del producto
        $product = Product::find($productId);
        $unitCost = $product ? ($product->current_cost ?? 0) : 0;

        $movement = self::create([
            'product_id' => $productId,
            'movement_type' => self::TYPE_SALE,
            'quantity' => -1 * abs($quantity), // Siempre negativo para salidas
            'unit_cost' => $unitCost,
            'reference_document' => $referenceDocument,
            'reference_id' => $orderId,
            'reference_type' => Order::class,
            'created_by' => $createdBy,
            'notes' => $notes
        ]);

        // Actualizar stock del producto
        if ($product) {
            // Si es un ingrediente, usar su método específico
            if ($product instanceof Ingredient) {
                $product->updateStock(-1 * abs($quantity));
            } else {
                // Para otros productos, actualizar directamente el stock
                $product->current_stock = ($product->current_stock ?? 0) - abs($quantity);
                $product->save();
            }
        }

        return $movement;
    }

    /**
     * Crear un movimiento de ajuste de inventario.
     *
     * @param int $productId ID del producto
     * @param float $quantity Cantidad (positiva para ingresos, negativa para salidas)
     * @param float|null $unitCost Costo unitario (opcional)
     * @param string|null $referenceDocument Documento de referencia
     * @param int|null $createdBy ID del usuario que crea el movimiento
     * @param string|null $notes Notas adicionales
     * @return self
     */
    public static function createAdjustmentMovement(
        int $productId,
        float $quantity,
        ?float $unitCost = null,
        ?string $referenceDocument = null,
        ?int $createdBy = null,
        ?string $notes = null
    ): self {
        // Obtener el producto
        $product = Product::find($productId);

        // Si no se proporciona costo unitario, usar el actual
        if ($unitCost === null && $product) {
            $unitCost = $product->current_cost ?? 0;
        }

        $movement = self::create([
            'product_id' => $productId,
            'movement_type' => self::TYPE_ADJUSTMENT,
            'quantity' => $quantity, // Puede ser positivo o negativo
            'unit_cost' => $unitCost,
            'reference_document' => $referenceDocument,
            'reference_id' => null,
            'reference_type' => null,
            'created_by' => $createdBy,
            'notes' => $notes ?? 'Ajuste de inventario'
        ]);

        // Actualizar stock del producto
        if ($product) {
            // Si es un ingrediente, usar su método específico
            if ($product instanceof Ingredient) {
                $product->updateStock($quantity, $quantity > 0 ? $unitCost : null);
            } else {
                // Para otros productos, actualizar directamente el stock
                $product->current_stock = ($product->current_stock ?? 0) + $quantity;
                if ($quantity > 0 && $unitCost > 0) {
                    $product->current_cost = $unitCost;
                }
                $product->save();
            }
        }

        return $movement;
    }

    /**
     * Crear un movimiento de desperdicio (merma).
     *
     * @param int $productId ID del producto
     * @param float $quantity Cantidad (positiva, se convertirá a negativa)
     * @param string|null $reason Motivo del desperdicio
     * @param int|null $createdBy ID del usuario que crea el movimiento
     * @return self
     */
    public static function createWasteMovement(
        int $productId,
        float $quantity,
        ?string $reason = null,
        ?int $createdBy = null
    ): self {
        // Obtener el producto
        $product = Product::find($productId);
        $unitCost = $product ? ($product->current_cost ?? 0) : 0;

        $notes = 'Desperdicio' . ($reason ? ': ' . $reason : '');

        $movement = self::create([
            'product_id' => $productId,
            'movement_type' => self::TYPE_WASTE,
            'quantity' => -1 * abs($quantity), // Siempre negativo para desperdicios
            'unit_cost' => $unitCost,
            'reference_document' => null,
            'reference_id' => null,
            'reference_type' => null,
            'created_by' => $createdBy,
            'notes' => $notes
        ]);

        // Actualizar stock del producto
        if ($product) {
            // Si es un ingrediente, usar su método específico
            if ($product instanceof Ingredient) {
                $product->updateStock(-1 * abs($quantity));
            } else {
                // Para otros productos, actualizar directamente el stock
                $product->current_stock = ($product->current_stock ?? 0) - abs($quantity);
                $product->save();
            }
        }

        return $movement;
    }
}
