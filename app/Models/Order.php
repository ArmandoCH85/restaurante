<?php

namespace App\Models;

use App\Traits\CalculatesIgv;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Log;
use App\Models\DocumentSeries;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use CalculatesIgv;

    /**
     * OPTIMIZACIÓN: Relaciones que se cargan por defecto para evitar N+1
     * Solo cargamos las más críticas para no sobrecargar
     */
    protected $with = ['customer', 'table'];

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
        'cash_register_id',
        'order_datetime',
        'status',
        'subtotal',
        'tax',
        'discount',
        'total',
        'notes',
        'billed',
        'parent_id'
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
     * Obtiene la orden "padre" de la que esta orden fue dividida.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'parent_id');
    }

    /**
     * Obtiene las órdenes "hijas" que resultaron de dividir esta orden.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Order::class, 'parent_id');
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
     * Obtiene el usuario que registró la orden.
     * Esta es una relación alternativa que apunta a la misma columna que employee.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    /**
     * Obtiene los detalles de la orden.
     */
    public function orderDetails(): HasMany
    {
        return $this->hasMany(OrderDetail::class);
    }

    /**
     * Obtiene la información de delivery asociada a la orden.
     */
    public function deliveryOrder(): HasOne
    {
        return $this->hasOne(DeliveryOrder::class);
    }

    /**
     * Obtiene la caja registradora asociada a la orden.
     */
    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
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
     * Verifica si la orden es de tipo delivery.
     */
    public function isDelivery(): bool
    {
        return $this->service_type === 'delivery';
    }

    /**
     * Verifica si la orden es para consumo en el local.
     */
    public function isDineIn(): bool
    {
        return $this->service_type === 'dine_in';
    }

    /**
     * Verifica si la orden es para llevar.
     */
    public function isTakeout(): bool
    {
        return $this->service_type === 'takeout';
    }

    /**
     * Verifica si la orden es para auto-servicio.
     */
    public function isDriveThru(): bool
    {
        return $this->service_type === 'drive_thru';
    }

    /**
     * Procesa las recetas de los productos en la orden y registra los movimientos de inventario
     * utilizando el método FIFO.
     *
     * @param int|null $warehouseId ID del almacén para consumir los ingredientes (opcional)
     * @return array Detalles del procesamiento
     */
    public function processRecipes(?int $warehouseId = null): array
    {
        // Solo procesar si la orden está en preparación o abierta
        if (!in_array($this->status, [self::STATUS_IN_PREPARATION, self::STATUS_OPEN])) {
            return [
                'success' => false,
                'message' => 'La orden no está en un estado válido para procesar recetas',
                'details' => []
            ];
        }

        $processedProducts = [];
        $totalCost = 0;
        $errors = [];

        // Obtener el almacén predeterminado si no se especifica uno
        if (!$warehouseId) {
            $defaultWarehouse = Warehouse::where('is_default', true)->first();
            $warehouseId = $defaultWarehouse ? $defaultWarehouse->id : null;
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

                try {
                    // Verificar si hay suficiente stock
                    if (!$recipe->hasEnoughIngredients($detail->quantity, $warehouseId)) {
                        $errors[] = [
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                            'quantity' => $detail->quantity,
                            'message' => 'No hay suficiente stock para preparar este producto'
                        ];
                        continue;
                    }

                    // Consumir los ingredientes utilizando FIFO
                    $result = $recipe->consumeIngredients(
                        $detail->quantity,
                        $warehouseId,
                        $this->id,
                        $this->employee_id // Usar el empleado asociado a la orden
                    );

                    $processedProducts[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => $detail->quantity,
                        'cost' => $result['total_cost'],
                        'ingredients' => $result['ingredients']
                    ];

                    $totalCost += $result['total_cost'];

                    // Actualizar el costo en el detalle de la orden
                    $detail->cost = $result['total_cost'] / $detail->quantity;
                    $detail->save();

                } catch (\Exception $e) {
                    $errors[] = [
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'quantity' => $detail->quantity,
                        'message' => $e->getMessage()
                    ];
                }
            }
        }

        // Si se procesaron productos, actualizar el estado de la orden
        if (count($processedProducts) > 0 && $this->status === self::STATUS_OPEN) {
            $this->status = self::STATUS_IN_PREPARATION;
            $this->save();
        }

        return [
            'success' => count($errors) === 0,
            'processed_products' => $processedProducts,
            'total_cost' => $totalCost,
            'errors' => $errors,
            'order_id' => $this->id,
            'order_status' => $this->status,
            'warehouse_id' => $warehouseId
        ];
    }

    /**
     * Envía la orden a cocina, cambiando su estado a 'in_preparation'.
     *
     * @return bool
     */
    public function sendToKitchen(): bool
    {
        if ($this->status === self::STATUS_OPEN) {
            $this->status = self::STATUS_IN_PREPARATION;
            $this->save();

            // Actualizar estado de los detalles de la orden
            $this->orderDetails()->update(['status' => 'in_preparation']);

            return true;
        }
        return false;
    }

    /**
     * Marca la orden como lista para servir.
     *
     * @return bool
     */
    public function markAsReady(): bool
    {
        if ($this->status === self::STATUS_IN_PREPARATION) {
            $this->status = self::STATUS_READY;
            $this->save();

            // Actualizar estado de los detalles de la orden
            $this->orderDetails()->update(['status' => 'ready']);

            return true;
        }
        return false;
    }

    /**
     * Marca la orden como entregada al cliente.
     *
     * @return bool
     */
    public function markAsDelivered(): bool
    {
        if ($this->status === self::STATUS_READY) {
            $this->status = self::STATUS_DELIVERED;
            $this->save();

            // Actualizar estado de los detalles de la orden
            $this->orderDetails()->update(['status' => 'delivered']);

            return true;
        }
        return false;
    }

    /**
     * Completa la orden, cambiando su estado a 'completed'.
     * Solo se puede completar si está facturada.
     *
     * @return bool
     */
    public function completeOrder(): bool
    {
        if (!$this->billed) {
            return false; // No se puede completar si no está facturada
        }

        $this->status = self::STATUS_COMPLETED;
        $this->save();

        // Liberar la mesa si es una orden de servicio en local
        if ($this->service_type === 'dine_in' && $this->table_id) {
            $table = Table::find($this->table_id);
            if ($table) {
                $table->status = Table::STATUS_AVAILABLE;
                $table->occupied_at = null;
                $table->save();

                // Registrar en el log
                Log::info('Mesa liberada al completar la orden', [
                    'order_id' => $this->id,
                    'table_id' => $this->table_id,
                    'table_number' => $table->number
                ]);
            }
        }

        return true;
    }

    /**
     * Cancela la orden, cambiando su estado a 'cancelled'.
     *
     * @param string|null $reason Motivo de la cancelación
     * @return bool
     */
    public function cancelOrder(?string $reason = null): bool
    {
        // No se puede cancelar una orden facturada
        if ($this->billed) {
            return false;
        }

        $this->status = self::STATUS_CANCELLED;
        $this->notes = $reason ? ($this->notes ? $this->notes . "\n" . $reason : $reason) : $this->notes;
        $this->save();

        // Actualizar estado de los detalles de la orden
        $this->orderDetails()->update(['status' => 'cancelled']);

        // Liberar la mesa al cancelar la orden
        if ($this->service_type === 'dine_in' && $this->table_id) {
            $table = Table::find($this->table_id);
            if ($table) {
                // Cambiar el estado de la mesa a disponible
                $table->status = Table::STATUS_AVAILABLE;
                $table->occupied_at = null;
                $table->save();

                // Registrar en el log
                Log::info('Mesa liberada al cancelar orden', [
                    'order_id' => $this->id,
                    'table_id' => $this->table_id,
                    'table_status' => $table->status
                ]);
            }
        }

        return true;
    }

    /**
     * Añade un producto a la orden.
     *
     * @param int $productId ID del producto
     * @param int $quantity Cantidad
     * @param float|null $unitPrice Precio unitario (opcional, si no se proporciona se usa el precio del producto)
     * @param string|null $notes Notas adicionales
     * @return OrderDetail
     */
    public function addProduct(int $productId, int $quantity, ?float $unitPrice = null, ?string $notes = null): OrderDetail
    {
        $product = Product::findOrFail($productId);

        // Si no se proporciona un precio unitario, usar el precio del producto
        if ($unitPrice === null) {
            $unitPrice = $product->sale_price;
        }

        $subtotal = $unitPrice * $quantity;

        $orderDetail = new OrderDetail([
            'order_id' => $this->id,
            'product_id' => $productId,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'subtotal' => $subtotal,
            'notes' => $notes,
            'status' => 'pending'
        ]);

        $orderDetail->save();

        // Recalcular totales de la orden
        $this->recalculateTotals();

        // Refrescar el modelo para asegurar que los cambios se reflejen
        $this->refresh();

        return $orderDetail;
    }

    /**
     * Actualiza la cantidad de un producto en la orden.
     *
     * @param int $orderDetailId ID del detalle de orden
     * @param int $quantity Nueva cantidad
     * @return bool
     */
    public function updateProductQuantity(int $orderDetailId, int $quantity): bool
    {
        $orderDetail = $this->orderDetails()->findOrFail($orderDetailId);

        if ($quantity <= 0) {
            // Si la cantidad es 0 o negativa, eliminar el producto
            $orderDetail->delete();
        } else {
            // Actualizar cantidad y subtotal
            $orderDetail->quantity = $quantity;
            $orderDetail->subtotal = $orderDetail->unit_price * $quantity;
            $orderDetail->save();
        }

        // Recalcular totales de la orden
        $this->recalculateTotals();

        return true;
    }

    /**
     * Actualiza el precio unitario de un producto en la orden.
     *
     * @param int $orderDetailId ID del detalle de orden
     * @param float $unitPrice Nuevo precio unitario
     * @return bool
     */
    public function updateProductPrice(int $orderDetailId, float $unitPrice): bool
    {
        $orderDetail = $this->orderDetails()->findOrFail($orderDetailId);

        // Actualizar precio y subtotal
        $orderDetail->unit_price = $unitPrice;
        $orderDetail->subtotal = $unitPrice * $orderDetail->quantity;
        $orderDetail->save();

        // Recalcular totales de la orden
        $this->recalculateTotals();

        return true;
    }

    /**
     * Elimina un producto de la orden.
     *
     * @param int $orderDetailId ID del detalle de orden
     * @return bool
     */
    public function removeProduct(int $orderDetailId): bool
    {
        $orderDetail = $this->orderDetails()->findOrFail($orderDetailId);
        $orderDetail->delete();

        // Recalcular totales de la orden
        $this->recalculateTotals();

        return true;
    }

    /**
     * Recalcula los totales de la orden basándose en los detalles.
     *
     * IMPORTANTE: Los precios en orderDetails YA INCLUYEN IGV
     * Este método calcula cuánto IGV está incluido en esos precios
     */
    public function recalculateTotals(): void
    {
        // Recargar la relación para asegurar datos actualizados
        $this->load('orderDetails');

        // El subtotal de orderDetails ya incluye IGV
        $totalWithIgv = $this->orderDetails->sum('subtotal');

        // Aplicar descuento al total con IGV
        $totalWithIgvAfterDiscount = $totalWithIgv - ($this->discount ?? 0);

        // Calcular el subtotal sin IGV y el IGV incluido
        $subtotalWithoutIgv = $this->calculateSubtotalFromPriceWithIgv($totalWithIgvAfterDiscount);
        $includedIgv = $this->calculateIncludedIgv($totalWithIgvAfterDiscount);

        // Actualizar los valores
        $this->subtotal = $subtotalWithoutIgv;
        $this->tax = $includedIgv;
        $this->total = $totalWithIgvAfterDiscount;

        // Guardar los cambios
        $this->save();

        // Log para depuración
        \Illuminate\Support\Facades\Log::info('Totales recalculados para orden #' . $this->id, [
            'total_with_igv_before_discount' => $totalWithIgv,
            'discount' => $this->discount,
            'total_with_igv_after_discount' => $totalWithIgvAfterDiscount,
            'subtotal_without_igv' => $subtotalWithoutIgv,
            'included_igv' => $includedIgv,
            'final_total' => $this->total,
            'details_count' => $this->orderDetails->count()
        ]);
    }

    /**
     * Aplica un descuento a la orden.
     *
     * @param float $amount Monto del descuento
     * @param string $type Tipo de descuento ('fixed' o 'percentage')
     * @return void
     */
    public function applyDiscount(float $amount, string $type = 'fixed'): void
    {
        if ($type === 'percentage') {
            $this->discount = ($this->subtotal * $amount) / 100;
        } else {
            $this->discount = $amount;
        }

        $this->recalculateTotals();
    }

    /**
     * Obtiene los pagos asociados a la orden.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Registra un nuevo pago para la orden.
     *
     * @param string $paymentMethod Método de pago
     * @param float $amount Monto del pago
     * @param string|null $reference Referencia del pago (número de transacción, etc.)
     * @return Payment
     * @throws \Exception Si no hay una caja abierta para pagos en efectivo
     */
    public function registerPayment(string $paymentMethod, float $amount, ?string $reference = null): Payment
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($paymentMethod, $amount, $reference) {
            // Verificar si hay una caja abierta
            $activeCashRegister = CashRegister::getOpenRegister();

            // Validar requisitos según el método de pago
            if ($paymentMethod === Payment::METHOD_CASH && !$activeCashRegister) {
                throw new \Exception('No hay una caja abierta para registrar pagos en efectivo. Por favor, abra una caja primero.');
            }

            // Crear el registro de pago
            $payment = new Payment([
                'order_id' => $this->id,
                'cash_register_id' => $activeCashRegister ? $activeCashRegister->id : null,
                'payment_method' => $paymentMethod,
                'amount' => $amount,
                'reference_number' => $reference,
                'payment_datetime' => now(),
                'received_by' => \Illuminate\Support\Facades\Auth::id(),
            ]);

            $payment->save();

            // Actualizar los totales de la caja según el método de pago
            if ($activeCashRegister) {
                $activeCashRegister->registerSale($paymentMethod, $amount);
            }

            // Registrar en el log según el método de pago
            $this->logPaymentRegistration($payment, $activeCashRegister);

            // Emitir evento para que los listeners puedan actualizar otros componentes
            event(new \App\Events\PaymentRegistered($payment));

            return $payment;
        });
    }

    /**
     * Registra en el log la información del pago.
     *
     * @param Payment $payment El pago registrado
     * @param CashRegister|null $cashRegister La caja registradora asociada
     * @return void
     */
    private function logPaymentRegistration(Payment $payment, ?CashRegister $cashRegister): void
    {
        $logContext = [
            'order_id' => $this->id,
            'payment_id' => $payment->id,
            'payment_method' => $payment->payment_method,
            'amount' => $payment->amount,
            'has_active_register' => $cashRegister ? 'Sí' : 'No'
        ];

        if ($cashRegister) {
            $logContext['cash_register_id'] = $cashRegister->id;
        }

        $methodName = match($payment->payment_method) {
            Payment::METHOD_CASH => 'efectivo',
            Payment::METHOD_CREDIT_CARD, Payment::METHOD_DEBIT_CARD => 'tarjeta',
            Payment::METHOD_DIGITAL_WALLET => 'billetera digital',
            Payment::METHOD_BANK_TRANSFER => 'transferencia bancaria',
            default => $payment->payment_method
        };

        \Illuminate\Support\Facades\Log::info("Pago con {$methodName} registrado", $logContext);
    }

    /**
     * Obtiene el total pagado de la orden.
     *
     * @return float
     */
    public function getTotalPaid(): float
    {
        return $this->payments()->sum('amount');
    }

    /**
     * Obtiene el saldo pendiente de la orden.
     *
     * @return float
     */
    public function getRemainingBalance(): float
    {
        return $this->total - $this->getTotalPaid();
    }

    /**
     * Verifica si la orden está completamente pagada.
     *
     * @return bool
     */
    public function isFullyPaid(): bool
    {
        return $this->getRemainingBalance() <= 0;
    }

    /**
     * Obtiene las facturas asociadas a la orden.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Obtiene la cotización original si esta orden fue convertida desde una cotización.
     */
    public function quotation(): HasOne
    {
        return $this->hasOne(Quotation::class);
    }

    /**
     * Verifica si esta orden proviene de una cotización con anticipo.
     *
     * @return bool
     */
    public function hasQuotationWithAdvance(): bool
    {
        $quotation = $this->quotation;
        return $quotation && $quotation->hasAdvancePayment();
    }

    /**
     * Obtiene el anticipo de la cotización original si existe.
     *
     * @return float
     */
    public function getQuotationAdvancePayment(): float
    {
        $quotation = $this->quotation;
        return $quotation ? $quotation->advance_payment : 0;
    }

    /**
     * Obtiene las notas del anticipo de la cotización original si existe.
     *
     * @return string|null
     */
    public function getQuotationAdvanceNotes(): ?string
    {
        $quotation = $this->quotation;
        return $quotation ? $quotation->advance_payment_notes : null;
    }

    /**
     * Genera una factura para la orden.
     *
     * @param string $invoiceType Tipo de factura ('receipt', 'invoice', etc.)
     * @param string $series Serie del comprobante
     * @param int $customerId ID del cliente
     * @return Invoice|null
     */
    public function generateInvoice(string $invoiceType, string $series, int $customerId): ?Invoice
    {
        if ($this->billed) {
            return null; // Ya facturado
        }

        return DB::transaction(function () use ($invoiceType, $series, $customerId) {
            // 1. Obtener el siguiente número de factura de forma segura
            $lastInvoice = Invoice::where('series', $series)->lockForUpdate()->latest('number')->first();
            $nextNumber = $lastInvoice ? ((int) $lastInvoice->number) + 1 : 1;

            // 2. Formatear el número con ceros a la izquierda
            $formattedNumber = str_pad($nextNumber, 8, '0', STR_PAD_LEFT);

            // 3. Obtener el cliente
            $customer = Customer::find($customerId);

            // Verificar que el cliente existe
            if (!$customer) {
                throw new \Exception("No se encontró el cliente con ID: {$customerId}");
            }

            // 4. Calcular correctamente subtotal e IGV desde el total
            $correctSubtotal = $this->total / 1.18; // Subtotal sin IGV
            $correctIgv = $this->total - $correctSubtotal; // IGV incluido
            
            // 5. Crear la factura
            $invoice = Invoice::create([
                'order_id' => $this->id,
            'invoice_type' => $invoiceType,
            'series' => $series,
            'number' => $formattedNumber,
                'issue_date' => now(),
                'customer_id' => $customer->id,
                'client_name' => $customer->name,
                'client_document' => $customer->document_number,
                'client_address' => $customer->address,
            'taxable_amount' => round($correctSubtotal, 2),
            'tax' => round($correctIgv, 2),
            'total' => $this->total,
                'status' => 'issued',
                'sunat_status' => 'PENDIENTE',
        ]);

            // 6. Agregar detalles de la factura
        foreach ($this->orderDetails as $detail) {
            $invoice->details()->create([
                'product_id' => $detail->product_id,
                'quantity' => $detail->quantity,
                'unit_price' => $detail->unit_price,
                'subtotal' => $detail->subtotal,
                    'description' => $detail->product->name,
            ]);
        }

            // 6. Actualizar la orden como facturada
            $this->update(['billed' => true, 'status' => self::STATUS_COMPLETED]);

            // 7. Actualizar el correlativo en la serie del documento
            $documentSeries = DocumentSeries::where('series', $series)->first();
            if ($documentSeries) {
                $documentSeries->increment('current_number');
            }

        return $invoice;
        });
    }

    /**
     * Imprime la factura de la orden.
     *
     * @param int $invoiceId ID de la factura a imprimir
     * @return string|null URL del PDF de la factura
     */
    public function printInvoice(int $invoiceId): ?string
    {
        $invoice = $this->invoices()->findOrFail($invoiceId);

        // En una implementación real, aquí se generaría el PDF de la factura
        // y se enviaría a la impresora o se devolvería la URL del PDF

        // Por ahora, devolvemos una URL de ejemplo
        return route('invoices.print', $invoice->id);
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
                $result = $order->processRecipes();

                // Registrar el resultado del procesamiento
                \Illuminate\Support\Facades\Log::info('Procesamiento de recetas para orden #' . $order->id, $result);
            }
        });
    }
}
