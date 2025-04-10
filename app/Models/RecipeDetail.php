<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeDetail extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'recipe_id',
        'ingredient_id',
        'quantity',
        'unit_of_measure'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'decimal:3',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Obtiene la receta a la que pertenece este detalle.
     */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /**
     * Obtiene el ingrediente asociado con este detalle de receta.
     */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'ingredient_id');
    }

    /**
     * Calcula el costo de este ingrediente en la receta.
     */
    public function getIngredientCost(): float
    {
        if (!$this->ingredient) {
            return 0;
        }

        return $this->quantity * $this->ingredient->current_cost;
    }

    /**
     * Verifica si hay suficiente stock para este ingrediente.
     */
    public function hasEnoughStock(int $quantity = 1): bool
    {
        if (!$this->ingredient) {
            return false;
        }
        
        // Asumimos que los productos tienen una propiedad current_stock similar a los ingredientes
        // Si no existe esta propiedad en Product, se deberá ajustar esta lógica
        return $this->ingredient->current_stock >= ($this->quantity * $quantity);
    }
}
