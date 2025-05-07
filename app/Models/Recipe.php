<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recipe extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'preparation_instructions',
        'expected_cost',
        'preparation_time'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expected_cost' => 'decimal:2',
        'preparation_time' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Obtiene el producto asociado con esta receta.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Obtiene los detalles (ingredientes) de esta receta.
     */
    public function details(): HasMany
    {
        return $this->hasMany(RecipeDetail::class);
    }

    /**
     * Calcula el costo esperado de la receta basado en sus ingredientes.
     *
     * @param int|null $warehouseId ID del almacén para calcular el costo (opcional)
     * @return float El costo esperado de la receta
     */
    public function calculateExpectedCost(?int $warehouseId = null): float
    {
        $cost = 0;

        foreach ($this->details as $detail) {
            if ($detail->ingredient) {
                // Si se especifica un almacén, usar el costo promedio de ese almacén
                if ($warehouseId) {
                    $ingredientCost = $detail->ingredient->calculateAverageCost($warehouseId);
                } else {
                    $ingredientCost = $detail->ingredient->current_cost;
                }

                $cost += $detail->quantity * $ingredientCost;
            }
        }

        return $cost;
    }

    /**
     * Actualiza el costo esperado de la receta.
     *
     * @param int|null $warehouseId ID del almacén para calcular el costo (opcional)
     * @return bool
     */
    public function updateExpectedCost(?int $warehouseId = null): bool
    {
        $this->expected_cost = $this->calculateExpectedCost($warehouseId);
        return $this->save();
    }

    /**
     * Verifica si hay suficiente stock de todos los ingredientes para esta receta.
     *
     * @param int $quantity Cantidad de recetas a preparar
     * @param int|null $warehouseId ID del almacén para verificar el stock (opcional)
     * @return bool
     */
    public function hasEnoughIngredients(int $quantity = 1, ?int $warehouseId = null): bool
    {
        foreach ($this->details as $detail) {
            if (!$detail->ingredient) {
                continue;
            }

            $requiredQuantity = $detail->quantity * $quantity;

            // Si se especifica un almacén, verificar el stock en ese almacén
            if ($warehouseId) {
                $availableStock = $detail->ingredient->stocks()
                    ->where('warehouse_id', $warehouseId)
                    ->where('status', IngredientStock::STATUS_AVAILABLE)
                    ->sum('quantity');

                if ($availableStock < $requiredQuantity) {
                    return false;
                }
            } else {
                // Verificar el stock total del ingrediente
                if ($detail->ingredient->current_stock < $requiredQuantity) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Consume los ingredientes necesarios para preparar esta receta utilizando el método FIFO.
     *
     * @param int $quantity Cantidad de recetas a preparar
     * @param int|null $warehouseId ID del almacén para consumir los ingredientes (opcional)
     * @param int|null $orderId ID de la orden relacionada (opcional)
     * @param int|null $userId ID del usuario que realiza la acción (opcional)
     * @return array Detalles del consumo [ingredientes_consumidos, costo_total]
     */
    public function consumeIngredients(int $quantity = 1, ?int $warehouseId = null, ?int $orderId = null, ?int $userId = null): array
    {
        // Verificar si hay suficiente stock
        if (!$this->hasEnoughIngredients($quantity, $warehouseId)) {
            throw new \Exception('No hay suficiente stock para preparar esta receta');
        }

        $consumedIngredients = [];
        $totalCost = 0;

        // Consumir cada ingrediente
        foreach ($this->details as $detail) {
            if (!$detail->ingredient) {
                continue;
            }

            $requiredQuantity = $detail->quantity * $quantity;

            // Consumir el ingrediente utilizando FIFO
            $result = $detail->ingredient->consumeStock($requiredQuantity, $warehouseId);

            // Registrar el movimiento de inventario
            if ($result['consumed_quantity'] > 0) {
                $movement = InventoryMovement::create([
                    'product_id' => $detail->ingredient_id,
                    'warehouse_id' => $warehouseId,
                    'movement_type' => InventoryMovement::TYPE_SALE,
                    'quantity' => -1 * $result['consumed_quantity'],
                    'unit_cost' => $result['average_unit_cost'],
                    'reference_id' => $orderId,
                    'reference_type' => $orderId ? Order::class : null,
                    'created_by' => $userId,
                    'notes' => 'Consumo para receta: ' . $this->product->name . ' (x' . $quantity . ')'
                ]);

                $consumedIngredients[] = [
                    'ingredient_id' => $detail->ingredient_id,
                    'ingredient_name' => $detail->ingredient->name,
                    'required_quantity' => $requiredQuantity,
                    'consumed_quantity' => $result['consumed_quantity'],
                    'unit_cost' => $result['average_unit_cost'],
                    'total_cost' => $result['total_cost'],
                    'movement_id' => $movement->id
                ];

                $totalCost += $result['total_cost'];
            }
        }

        return [
            'ingredients' => $consumedIngredients,
            'total_cost' => $totalCost,
            'recipe_quantity' => $quantity,
            'recipe_name' => $this->product->name,
            'recipe_id' => $this->id
        ];
    }
}
