<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ingredient extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'unit_of_measure',
        'min_stock',
        'current_stock',
        'current_cost',
        'supplier_id',
        'active',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'min_stock' => 'decimal:3',
        'current_stock' => 'decimal:3',
        'current_cost' => 'decimal:2',
        'active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Obtiene el proveedor del ingrediente.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Obtiene las recetas que utilizan este ingrediente.
     */
    public function recipeDetails(): HasMany
    {
        return $this->hasMany(RecipeDetail::class);
    }

    /**
     * Obtiene los movimientos de inventario de este ingrediente.
     */
    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'product_id');
    }

    /**
     * Obtiene el stock disponible del ingrediente.
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(IngredientStock::class);
    }

    /**
     * Verificar si el stock del ingrediente está por debajo del mínimo.
     */
    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->min_stock;
    }

    /**
     * Actualizar el stock y costo del ingrediente.
     */
    public function updateStock(float $quantity, ?float $cost = null, ?int $warehouseId = null): bool
    {
        // $warehouseId is kept for compatibility with inventory movement callers.
        unset($warehouseId);

        // Actualizar stock
        $this->current_stock += $quantity;

        // Si se proporciona un costo, actualizar el costo promedio
        if ($cost !== null && $quantity > 0) {
            $totalCost = ($this->current_stock - $quantity) * $this->current_cost + $quantity * $cost;
            $this->current_cost = $this->current_stock > 0 ? $totalCost / $this->current_stock : $cost;
        }

        return $this->save();
    }

    /**
     * Agregar stock utilizando el método FIFO.
     *
     * @param  float  $quantity  Cantidad a agregar
     * @param  float  $unitCost  Costo unitario
     * @param  int|null  $warehouseId  ID del almacén
     * @param  string|null  $expiryDate  Fecha de vencimiento (formato Y-m-d)
     * @param  int|null  $purchaseId  ID de la compra relacionada
     * @return IngredientStock El stock creado
     */
    public function addStock(float $quantity, float $unitCost, ?int $warehouseId = null, ?string $expiryDate = null, ?int $purchaseId = null): IngredientStock
    {
        // Crear un nuevo registro de stock
        $stock = IngredientStock::create([
            'ingredient_id' => $this->id,
            'warehouse_id' => $warehouseId ?? Warehouse::where('is_default', true)->first()?->id,
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'expiry_date' => $expiryDate,
            'status' => IngredientStock::STATUS_AVAILABLE,
            'purchase_id' => $purchaseId,
        ]);

        // Actualizar el stock total y el costo promedio del ingrediente
        $this->updateStock($quantity, $unitCost);

        return $stock;
    }

    /**
     * Consumir stock utilizando el método FIFO.
     *
     * @param  float  $quantity  Cantidad a consumir
     * @param  int|null  $warehouseId  ID del almacén
     * @return array Detalles del consumo
     */
    public function consumeStock(float $quantity, ?int $warehouseId = null): array
    {
        // Consumir stock utilizando FIFO
        $result = IngredientStock::consumeByFIFO($this->id, $quantity, $warehouseId);

        // Actualizar el stock total del ingrediente
        $this->current_stock -= $result['consumed_quantity'];
        $this->save();

        return $result;
    }

    /**
     * Calcular el costo promedio ponderado del stock disponible.
     *
     * @param  int|null  $warehouseId  ID del almacén
     * @return float Costo promedio ponderado
     */
    public function calculateAverageCost(?int $warehouseId = null): float
    {
        $query = $this->stocks()
            ->where('status', IngredientStock::STATUS_AVAILABLE)
            ->where('quantity', '>', 0);

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        $stocks = $query->get();

        $totalQuantity = 0;
        $totalCost = 0;

        foreach ($stocks as $stock) {
            $totalQuantity += $stock->quantity;
            $totalCost += $stock->quantity * $stock->unit_cost;
        }

        return $totalQuantity > 0 ? $totalCost / $totalQuantity : $this->current_cost;
    }

    /**
     * Actualizar el costo promedio del ingrediente basado en el stock disponible.
     *
     * @param  int|null  $warehouseId  ID del almacén
     */
    public function updateAverageCost(?int $warehouseId = null): bool
    {
        $this->current_cost = $this->calculateAverageCost($warehouseId);

        return $this->save();
    }
}
