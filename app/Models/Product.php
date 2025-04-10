<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use SoftDeletes;

    /**
     * Los tipos de productos disponibles.
     */
    const TYPE_INGREDIENT = 'ingredient';
    const TYPE_SALE_ITEM = 'sale_item';
    const TYPE_BOTH = 'both';

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'name',
        'description',
        'sale_price',
        'current_cost',
        'product_type',
        'category_id',
        'active',
        'has_recipe',
        'image_path',
        'available'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'sale_price' => 'decimal:2',
        'current_cost' => 'decimal:2',
        'active' => 'boolean',
        'has_recipe' => 'boolean',
        'available' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    /**
     * Obtiene la categoría del producto.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    /**
     * Obtiene la receta asociada a este producto.
     */
    public function recipe()
    {
        return $this->hasOne(Recipe::class);
    }

    /**
     * Verifica si el producto es un ingrediente.
     */
    public function isIngredient(): bool
    {
        return in_array($this->product_type, [self::TYPE_INGREDIENT, self::TYPE_BOTH]);
    }

    /**
     * Verifica si el producto es un artículo de venta.
     */
    public function isSaleItem(): bool
    {
        return in_array($this->product_type, [self::TYPE_SALE_ITEM, self::TYPE_BOTH]);
    }
}
