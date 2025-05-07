<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Purchase extends Model
{
    use HasFactory;

    // Variable estática para rastrear las compras que deben ser procesadas
    protected static $purchasesToProcess = [];

    protected static function booted()
    {
        static::creating(function ($model) {
            $model->created_by = Auth::id();
            $model->total = $model->subtotal * (1 + ($model->tax/100));
        });

        static::updating(function ($model) {
            $model->total = $model->subtotal * (1 + ($model->tax/100));

            // Guardar si el estado cambió a 'completed' para procesarlo después
            if ($model->isDirty('status') && $model->status === self::STATUS_COMPLETED) {
                // Marcamos que esta compra debe ser procesada usando la variable estática
                static::$purchasesToProcess[$model->id] = true;
            }
        });

        // Usar el evento saved para procesar la compra después de guardar
        static::saved(function ($model) {
            // Verificar si la compra debe ser procesada
            if (isset(static::$purchasesToProcess[$model->id])) {
                $result = $model->processOrder();

                // Registrar el resultado del procesamiento
                \Illuminate\Support\Facades\Log::info('Procesamiento de compra #' . $model->id, $result);

                // Limpiar la marca
                unset(static::$purchasesToProcess[$model->id]);
            }
        });
    }

    /**
     * Los estados disponibles para las compras.
     */
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Los tipos de documentos disponibles para las compras.
     */
    const DOCUMENT_TYPE_INVOICE = 'invoice';
    const DOCUMENT_TYPE_RECEIPT = 'receipt';
    const DOCUMENT_TYPE_TICKET = 'ticket';
    const DOCUMENT_TYPE_DISPATCH_GUIDE = 'dispatch_guide';
    const DOCUMENT_TYPE_OTHER = 'other';

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'supplier_id',
        'warehouse_id',
        'purchase_date',
        'document_number',
        'document_type',
        'subtotal',
        'tax',
        'total',
        'status',
        'created_by',
        'notes'
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'purchase_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Obtiene el proveedor asociado con esta compra.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Obtiene el almacén asociado con esta compra.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Obtiene el usuario que creó esta compra.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Obtiene los detalles de esta compra.
     */
    public function details(): HasMany
    {
        return $this->hasMany(PurchaseDetail::class);
    }

    /**
     * Obtiene los stocks de ingredientes asociados a esta compra.
     */
    public function ingredientStocks(): HasMany
    {
        return $this->hasMany(IngredientStock::class);
    }

    /**
     * Verifica si la compra está en estado pendiente.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Verifica si la compra está completada.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Verifica si la compra está cancelada.
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Procesa la compra y actualiza el inventario utilizando el método FIFO.
     *
     * @return array Detalles del procesamiento
     */
    public function processOrder(): array
    {
        // Solo procesar si la compra está completada
        if (!$this->isCompleted()) {
            return [
                'success' => false,
                'message' => 'La compra no está en estado completado',
                'details' => []
            ];
        }

        $processedItems = [];
        $errors = [];

        // Recorrer todos los detalles de la compra
        foreach ($this->details as $detail) {
            // Obtener el producto
            $product = Product::find($detail->product_id);

            if (!$product) {
                $errors[] = [
                    'product_id' => $detail->product_id,
                    'message' => 'Producto no encontrado'
                ];
                continue;
            }

            try {
                // Si es un ingrediente, registrar en ingredient_stock
                if ($product->isIngredient()) {
                    // Crear un nuevo registro de stock utilizando FIFO
                    $stock = $product->addStock(
                        $detail->quantity,
                        $detail->unit_cost,
                        $this->warehouse_id,
                        $detail->expiry_date, // Fecha de vencimiento (opcional)
                        $this->id // ID de la compra
                    );

                    $processedItems[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => $detail->quantity,
                        'unit_cost' => $detail->unit_cost,
                        'stock_id' => $stock->id
                    ];
                } else {
                    // Si es un producto normal, actualizar el stock
                    $product->current_stock += $detail->quantity;
                    $product->save();

                    // Registrar el movimiento de inventario
                    $movement = InventoryMovement::create([
                        'product_id' => $product->id,
                        'warehouse_id' => $this->warehouse_id,
                        'movement_type' => InventoryMovement::TYPE_PURCHASE,
                        'quantity' => $detail->quantity,
                        'unit_cost' => $detail->unit_cost,
                        'reference_id' => $this->id,
                        'reference_type' => Purchase::class,
                        'created_by' => $this->created_by,
                        'notes' => 'Compra #' . $this->id . ' - ' . $this->document_number
                    ]);

                    $processedItems[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => $detail->quantity,
                        'unit_cost' => $detail->unit_cost,
                        'movement_id' => $movement->id
                    ];
                }
            } catch (\Exception $e) {
                $errors[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'message' => $e->getMessage()
                ];
            }
        }

        return [
            'success' => count($errors) === 0,
            'processed_items' => $processedItems,
            'errors' => $errors,
            'purchase_id' => $this->id,
            'purchase_status' => $this->status,
            'warehouse_id' => $this->warehouse_id
        ];
    }
}
