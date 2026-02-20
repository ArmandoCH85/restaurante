<?php

namespace App\Models;

use App\Services\PurchaseStockRegistrationService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Purchase extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            // Keep created_by when it is already set (tests/seeders).
            $model->created_by = $model->created_by ?: Auth::id();
            $model->total = (float) $model->subtotal + (float) $model->tax;
        });

        static::updating(function (self $model): void {
            $model->total = (float) $model->subtotal + (float) $model->tax;
        });

        static::created(function (self $model): void {
            static::registerStockIfCompleted($model);
        });

        static::updated(function (self $model): void {
            if ($model->wasChanged('status')) {
                static::registerStockIfCompleted($model);
            }
        });
    }

    private static function registerStockIfCompleted(self $model): void
    {
        if ($model->status !== self::STATUS_COMPLETED) {
            return;
        }

        try {
            $result = app(PurchaseStockRegistrationService::class)->register($model, $model->created_by);

            Log::info('Procesamiento de compra completada', [
                'purchase_id' => $model->id,
                'result' => $result,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en procesamiento automatico de compra completada', [
                'purchase_id' => $model->id,
                'error' => $e->getMessage(),
            ]);
        }
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
        'notes',
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
        'updated_at' => 'datetime',
    ];

    /**
     * Obtiene el proveedor asociado con esta compra.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Obtiene el almacen asociado con esta compra.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Obtiene el usuario que creo esta compra.
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
     * Verifica si la compra esta en estado pendiente.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Verifica si la compra esta completada.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Verifica si la compra esta cancelada.
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Procesa la compra y actualiza el inventario utilizando el metodo FIFO.
     *
     * @return array Detalles del procesamiento
     */
    public function processOrder(): array
    {
        if (! $this->isCompleted()) {
            return [
                'success' => false,
                'message' => 'La compra no esta en estado completado',
                'details' => [],
            ];
        }

        return app(PurchaseStockRegistrationService::class)->register($this, $this->created_by);
    }
}
