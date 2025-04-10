<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'active'
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
        'deleted_at' => 'datetime'
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
    public function updateStock(float $quantity, ?float $cost = null): bool
    {
        // Actualizar stock
        $this->current_stock += $quantity;

        // Si se proporciona un costo, actualizar el costo promedio
        if ($cost !== null && $quantity > 0) {
            $totalCost = ($this->current_stock - $quantity) * $this->current_cost + $quantity * $cost;
            $this->current_cost = $this->current_stock > 0 ? $totalCost / $this->current_stock : $cost;
        }

        return $this->save();
    }
}
