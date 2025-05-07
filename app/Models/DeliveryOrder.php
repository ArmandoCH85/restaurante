<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;
use App\Models\User;

class DeliveryOrder extends Model
{
    use HasFactory;

    /**
     * The "booted" method of the model.
     * Este método se ejecuta cuando el modelo se inicia y permite registrar eventos.
     */
    protected static function boot()
    {
        parent::boot();

        // Evento antes de guardar
        static::saving(function ($deliveryOrder) {
            \Illuminate\Support\Facades\Log::info('Guardando pedido de delivery', [
                'delivery_id' => $deliveryOrder->id,
                'order_id' => $deliveryOrder->order_id,
                'status' => $deliveryOrder->status,
                'address' => $deliveryOrder->delivery_address,
                'is_new' => $deliveryOrder->wasRecentlyCreated
            ]);

            $logPath = storage_path('logs/delivery_model.log');
            $logContent = '[' . now()->format('Y-m-d H:i:s') . '] SAVING DeliveryOrder: ' .
                json_encode([
                    'delivery_id' => $deliveryOrder->id,
                    'order_id' => $deliveryOrder->order_id,
                    'status' => $deliveryOrder->status,
                    'address' => $deliveryOrder->delivery_address,
                    'is_new' => $deliveryOrder->wasRecentlyCreated,
                    'changes' => $deliveryOrder->getDirty()
                ], JSON_PRETTY_PRINT);

            // Asegurarse de que el directorio existe
            if (!file_exists(dirname($logPath))) {
                mkdir(dirname($logPath), 0755, true);
            }

            file_put_contents($logPath, $logContent . PHP_EOL, FILE_APPEND);
        });

        // Evento después de guardar
        static::saved(function ($deliveryOrder) {
            \Illuminate\Support\Facades\Log::info('Pedido de delivery guardado', [
                'delivery_id' => $deliveryOrder->id,
                'order_id' => $deliveryOrder->order_id,
                'status' => $deliveryOrder->status,
                'was_created' => $deliveryOrder->wasRecentlyCreated
            ]);

            $logPath = storage_path('logs/delivery_model.log');
            $logContent = '[' . now()->format('Y-m-d H:i:s') . '] SAVED DeliveryOrder: ' .
                json_encode([
                    'delivery_id' => $deliveryOrder->id,
                    'order_id' => $deliveryOrder->order_id,
                    'status' => $deliveryOrder->status,
                    'was_created' => $deliveryOrder->wasRecentlyCreated
                ], JSON_PRETTY_PRINT);

            file_put_contents($logPath, $logContent . PHP_EOL, FILE_APPEND);
        });

        // Evento después de crear
        static::created(function ($deliveryOrder) {
            \Illuminate\Support\Facades\Log::info('Pedido de delivery creado', [
                'delivery_id' => $deliveryOrder->id,
                'order_id' => $deliveryOrder->order_id,
                'status' => $deliveryOrder->status
            ]);

            $logPath = storage_path('logs/delivery_model.log');
            $logContent = '[' . now()->format('Y-m-d H:i:s') . '] CREATED DeliveryOrder: ' .
                json_encode([
                    'delivery_id' => $deliveryOrder->id,
                    'order_id' => $deliveryOrder->order_id,
                    'status' => $deliveryOrder->status,
                    'address' => $deliveryOrder->delivery_address
                ], JSON_PRETTY_PRINT);

            file_put_contents($logPath, $logContent . PHP_EOL, FILE_APPEND);
        });

        // No existe un evento saveException en Laravel, así que usamos un try-catch en el método save
        // Añadimos un método personalizado para registrar errores
        try {
            // Registramos que estamos configurando los eventos
            $logPath = storage_path('logs/delivery_model.log');
            $logContent = '[' . now()->format('Y-m-d H:i:s') . '] Eventos de modelo DeliveryOrder configurados correctamente';

            // Asegurarse de que el directorio existe
            if (!file_exists(dirname($logPath))) {
                mkdir(dirname($logPath), 0755, true);
            }

            file_put_contents($logPath, $logContent . PHP_EOL, FILE_APPEND);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al configurar eventos del modelo DeliveryOrder', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Estados disponibles para los pedidos de delivery.
     */
    const STATUS_PENDING = 'pending';
    const STATUS_ASSIGNED = 'assigned';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'order_id',
        'delivery_address',
        'delivery_references',
        'delivery_person_id',
        'delivery_user_id',
        'customer_name',
        'customer_phone',
        'customer_document_type',
        'customer_document',
        'status',
        'estimated_delivery_time',
        'actual_delivery_time',
        'assigned_at',
        'picked_up_at',
        'delivered_at',
        'cancellation_reason',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'estimated_delivery_time' => 'datetime',
        'actual_delivery_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Obtiene la orden asociada a este delivery.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Obtiene el repartidor asignado a este delivery (empleado).
     */
    public function deliveryPerson(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'delivery_person_id');
    }

    /**
     * Obtiene el usuario repartidor asignado a este delivery.
     */
    public function deliveryUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivery_user_id');
    }

    /**
     * Verifica si el pedido está pendiente.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Verifica si el pedido está asignado.
     */
    public function isAssigned(): bool
    {
        return $this->status === self::STATUS_ASSIGNED;
    }

    /**
     * Verifica si el pedido está en tránsito.
     */
    public function isInTransit(): bool
    {
        return $this->status === self::STATUS_IN_TRANSIT;
    }

    /**
     * Verifica si el pedido ha sido entregado.
     */
    public function isDelivered(): bool
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    /**
     * Verifica si el pedido ha sido cancelado.
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Asigna un repartidor al pedido.
     */
    public function assignDeliveryPerson(Employee $employee): bool
    {
        // Registrar en log
        \Illuminate\Support\Facades\Log::info('Intentando asignar repartidor a pedido', [
            'delivery_id' => $this->id,
            'order_id' => $this->order_id,
            'employee_id' => $employee->id,
            'current_status' => $this->status
        ]);

        // Guardar en archivo de log específico
        $logPath = storage_path('logs/delivery_operations.log');
        $logContent = '[' . now()->format('Y-m-d H:i:s') . '] Intentando asignar repartidor: ' .
            json_encode([
                'delivery_id' => $this->id,
                'order_id' => $this->order_id,
                'employee_id' => $employee->id,
                'employee_name' => $employee->full_name ?? $employee->name ?? 'Sin nombre',
                'current_status' => $this->status
            ], JSON_PRETTY_PRINT);

        // Asegurarse de que el directorio existe
        if (!file_exists(dirname($logPath))) {
            mkdir(dirname($logPath), 0755, true);
        }

        file_put_contents($logPath, $logContent . PHP_EOL, FILE_APPEND);

        if (!$this->isPending()) {
            \Illuminate\Support\Facades\Log::warning('No se puede asignar repartidor: pedido no está pendiente', [
                'delivery_id' => $this->id,
                'current_status' => $this->status
            ]);

            $logPath = storage_path('logs/delivery_operations.log');
            $logContent = '[' . now()->format('Y-m-d H:i:s') . '] ERROR: No se puede asignar repartidor, pedido no está pendiente. Estado actual: ' . $this->status;

            file_put_contents($logPath, $logContent . PHP_EOL, FILE_APPEND);

            return false;
        }

        $this->delivery_person_id = $employee->id;
        $this->status = self::STATUS_ASSIGNED;
        $this->estimated_delivery_time = Carbon::now()->addMinutes(30); // Tiempo estimado por defecto: 30 minutos

        try {
            $result = $this->save();

            \Illuminate\Support\Facades\Log::info('Resultado de asignar repartidor', [
                'success' => $result,
                'delivery_id' => $this->id,
                'employee_id' => $employee->id,
                'new_status' => $this->status
            ]);

            $logPath = storage_path('logs/delivery_operations.log');
            $logContent = '[' . now()->format('Y-m-d H:i:s') . '] Resultado de asignar repartidor: ' .
                ($result ? 'ÉXITO' : 'FALLO') . ' - ID: ' . $this->id;

            file_put_contents($logPath, $logContent . PHP_EOL, FILE_APPEND);

            return $result;

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al asignar repartidor', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'delivery_id' => $this->id,
                'employee_id' => $employee->id
            ]);

            $logPath = storage_path('logs/delivery_errors.log');
            $logContent = '[' . now()->format('Y-m-d H:i:s') . '] ERROR al asignar repartidor: ' .
                $e->getMessage() . "\nDelivery ID: " . $this->id .
                "\nEmployee ID: " . $employee->id . "\nTrace: " . $e->getTraceAsString();

            // Asegurarse de que el directorio existe
            if (!file_exists(dirname($logPath))) {
                mkdir(dirname($logPath), 0755, true);
            }

            file_put_contents($logPath, $logContent . PHP_EOL, FILE_APPEND);

            throw $e; // Re-lanzar la excepción para que sea manejada por el código que llama
        }
    }

    /**
     * Marca el pedido como en tránsito.
     */
    public function markAsInTransit(): bool
    {
        if (!$this->isAssigned()) {
            return false;
        }

        $this->status = self::STATUS_IN_TRANSIT;
        return $this->save();
    }

    /**
     * Marca el pedido como entregado.
     */
    public function markAsDelivered(): bool
    {
        if (!$this->isInTransit()) {
            return false;
        }

        $this->status = self::STATUS_DELIVERED;
        $this->actual_delivery_time = Carbon::now();

        // Actualizar el estado de la orden principal
        if ($this->order) {
            $this->order->status = Order::STATUS_COMPLETED;
            $this->order->save();
        }

        return $this->save();
    }

    /**
     * Cancela el pedido.
     */
    public function cancel(?string $reason = null): bool
    {
        if ($this->isDelivered()) {
            return false;
        }

        $this->status = self::STATUS_CANCELLED;
        $this->cancellation_reason = $reason;

        // Actualizar el estado de la orden principal
        if ($this->order) {
            $this->order->status = Order::STATUS_CANCELLED;
            $this->order->save();
        }

        return $this->save();
    }

    /**
     * Obtiene el tiempo estimado de entrega en formato legible.
     */
    public function getEstimatedDeliveryTimeAttribute($value)
    {
        if (!$value) {
            return null;
        }

        return Carbon::parse($value);
    }

    /**
     * Obtiene el tiempo transcurrido desde la creación del pedido.
     */
    public function getElapsedTimeAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Obtiene el tiempo restante estimado para la entrega.
     */
    public function getRemainingTimeAttribute(): ?string
    {
        if (!$this->estimated_delivery_time) {
            return null;
        }

        if ($this->isDelivered() || $this->isCancelled()) {
            return null;
        }

        if ($this->estimated_delivery_time->isPast()) {
            return 'Retrasado';
        }

        return $this->estimated_delivery_time->diffForHumans();
    }

    /**
     * Obtiene la información de estilo para el estado actual del pedido.
     */
    public function getStatusInfo(): array
    {
        $statusInfo = [
            'pending' => [
                'color' => '#92400e', // Naranja oscuro
                'bg' => '#fef3c7',    // Amarillo claro
                'text' => 'Pendiente',
                'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'
            ],
            'assigned' => [
                'color' => '#1e40af', // Azul oscuro
                'bg' => '#dbeafe',    // Azul claro
                'text' => 'Asignado',
                'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'
            ],
            'in_transit' => [
                'color' => '#4338ca', // Índigo oscuro
                'bg' => '#e0e7ff',    // Índigo claro
                'text' => 'En tránsito',
                'icon' => 'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0'
            ],
            'delivered' => [
                'color' => '#065f46', // Verde oscuro
                'bg' => '#d1fae5',    // Verde claro
                'text' => 'Entregado',
                'icon' => 'M5 13l4 4L19 7'
            ],
            'cancelled' => [
                'color' => '#991b1b', // Rojo oscuro
                'bg' => '#fee2e2',    // Rojo claro
                'text' => 'Cancelado',
                'icon' => 'M6 18L18 6M6 6l12 12'
            ]
        ];

        return $statusInfo[$this->status] ?? $statusInfo['pending'];
    }

    /**
     * Scope para filtrar pedidos por estado.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope para filtrar pedidos activos (no entregados ni cancelados).
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [self::STATUS_DELIVERED, self::STATUS_CANCELLED]);
    }

    /**
     * Scope para filtrar pedidos por repartidor.
     */
    public function scopeByDeliveryPerson($query, int $deliveryPersonId)
    {
        return $query->where('delivery_person_id', $deliveryPersonId);
    }

    /**
     * Sobrescribe el método save para capturar errores y registrarlos
     *
     * @param array $options
     * @return bool
     */
    public function save(array $options = [])
    {
        try {
            // Registrar intento de guardado
            $logPath = storage_path('logs/delivery_save.log');
            $logContent = '[' . now()->format('Y-m-d H:i:s') . '] INTENTANDO GUARDAR DeliveryOrder: ' .
                json_encode([
                    'delivery_id' => $this->id,
                    'order_id' => $this->order_id,
                    'status' => $this->status,
                    'address' => $this->delivery_address,
                    'attributes' => $this->getAttributes(),
                    'changes' => $this->getDirty()
                ], JSON_PRETTY_PRINT);

            // Asegurarse de que el directorio existe
            if (!file_exists(dirname($logPath))) {
                mkdir(dirname($logPath), 0755, true);
            }

            file_put_contents($logPath, $logContent . PHP_EOL, FILE_APPEND);

            // Llamar al método save original
            $result = parent::save($options);

            // Registrar resultado
            $logPath = storage_path('logs/delivery_save.log');
            $logContent = '[' . now()->format('Y-m-d H:i:s') . '] RESULTADO GUARDAR: ' .
                ($result ? 'ÉXITO' : 'FALLO') . ' - ID: ' . $this->id;

            file_put_contents($logPath, $logContent . PHP_EOL, FILE_APPEND);

            return $result;

        } catch (\Exception $e) {
            // Registrar error detallado
            \Illuminate\Support\Facades\Log::error('Error al guardar DeliveryOrder', [
                'delivery_id' => $this->id ?? 'no_id',
                'order_id' => $this->order_id ?? 'no_order_id',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'attributes' => $this->getAttributes()
            ]);

            // Guardar en archivo de log específico
            $logPath = storage_path('logs/delivery_errors.log');
            $logContent = '[' . now()->format('Y-m-d H:i:s') . '] ERROR CRÍTICO AL GUARDAR DeliveryOrder: ' .
                $e->getMessage() . "\nDelivery ID: " . ($this->id ?? 'no_id') .
                "\nOrder ID: " . ($this->order_id ?? 'no_order_id') .
                "\nLínea: " . $e->getLine() .
                "\nArchivo: " . $e->getFile() .
                "\nAtributos: " . json_encode($this->getAttributes(), JSON_PRETTY_PRINT) .
                "\nTrace: " . $e->getTraceAsString();

            // Asegurarse de que el directorio existe
            if (!file_exists(dirname($logPath))) {
                mkdir(dirname($logPath), 0755, true);
            }

            file_put_contents($logPath, $logContent . PHP_EOL, FILE_APPEND);

            // Re-lanzar la excepción para que sea manejada por el código que llama
            throw $e;
        }
    }
}
