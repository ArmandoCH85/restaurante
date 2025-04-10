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
     */
    public function calculateExpectedCost(): float
    {
        $cost = 0;

        foreach ($this->details as $detail) {
            if ($detail->ingredient) {
                $cost += $detail->quantity * $detail->ingredient->current_cost;
            }
        }

        return $cost;
    }

    /**
     * Actualiza el costo esperado de la receta.
     */
    public function updateExpectedCost(): bool
    {
        $this->expected_cost = $this->calculateExpectedCost();
        return $this->save();
    }

    /**
     * Verifica si hay suficiente stock de todos los ingredientes para esta receta.
     */
    public function hasEnoughIngredients(int $quantity = 1): bool
    {
        foreach ($this->details as $detail) {
            if (!$detail->ingredient) {
                continue;
            }

            // Verificar si el producto tiene la propiedad current_stock
            if (property_exists($detail->ingredient, 'current_stock') && 
                $detail->ingredient->current_stock < ($detail->quantity * $quantity)) {
                return false;
            }
        }

        return true;
    }
}
