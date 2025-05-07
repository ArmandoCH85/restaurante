<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /**
     * Estados disponibles para las órdenes.
     */
    const STATUS_OPEN = 'open';
    const STATUS_IN_PREPARATION = 'in_preparation';
    const STATUS_READY = 'ready';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array
     */
    protected $fillable = [
        'service_type',
        'table_id',
        'customer_id',
        'employee_id',
        'order_datetime',
        'status',
        'subtotal',
        'tax',
        'discount',
        'total',
        'notes',
        'billed'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'order_datetime' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'billed' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Obtiene la mesa asociada a la orden.
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    /**
     * Obtiene el cliente asociado a la orden.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Obtiene el empleado que registró la orden.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Obtiene los detalles de la orden.
     */
    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    /**
     * Devuelve si la orden está abierta.
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Devuelve si la orden está facturada.
     */
    public function isBilled(): bool
    {
        return $this->billed;
    }

    /**
     * Procesa las recetas de los productos en la orden y registra los movimientos de inventario.
     *
     * @return bool Si el procesamiento fue exitoso
     */
    public function processRecipes(): bool
    {
        // Solo procesar si la orden está en preparación
        if ($this->status !== self::STATUS_IN_PREPARATION) {
            return false;
        }

        // Recorrer todos los detalles de la orden
        foreach ($this->orderDetails as $detail) {
            // Obtener el producto
            $product = Product::find($detail->product_id);

            // Si el producto no existe o no es un artículo de venta, continuar
            if (!$product || !$product->isSaleItem()) {
                continue;
            }

            // Si el producto tiene receta, procesar los ingredientes
            if ($product->has_recipe && $product->recipe) {
                $recipe = $product->recipe;

                // Recorrer todos los detalles de la receta
                foreach ($recipe->recipeDetails as $recipeDetail) {
                    // Calcular la cantidad necesaria del ingrediente
                    $ingredientQuantity = $recipeDetail->quantity * $detail->quantity;

                    // Registrar el movimiento de inventario (salida)
                    InventoryMovement::createSaleMovement(
                        $recipeDetail->ingredient_id,
                        $ingredientQuantity,
                        $this->id,
                        'Orden #' . $this->id,
                        null, // No hay usuario autenticado en este contexto
                        'Consumo por orden #' . $this->id . ' - Producto: ' . $product->name
                    );
                }
            }
        }

        return true;
    }

    /**
     * Eventos del modelo.
     */
    protected static function booted()
    {
        // Cuando se actualiza una orden
        static::updating(function ($order) {
            // Si el estado cambió a 'in_preparation', procesar las recetas
            if ($order->isDirty('status') && $order->status === self::STATUS_IN_PREPARATION) {
                $order->processRecipes();
            }
        });
    }
}
