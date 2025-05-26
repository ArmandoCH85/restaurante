<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Log;

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
     * Obtiene la información de delivery asociada a la orden.
     */
    public function deliveryOrder(): HasOne
    {
        return $this->hasOne(DeliveryOrder::class);
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

        // Liberar la mesa si es una orden de consumo en local
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
     */
    public function recalculateTotals(): void
    {
        // Recargar la relación para asegurar datos actualizados
        $this->load('orderDetails');

        $subtotal = $this->orderDetails->sum('subtotal');

        // Calcular impuestos (18% IGV)
        $tax = round($subtotal * 0.18, 2);

        // Calcular total
        $total = round($subtotal + $tax - ($this->discount ?? 0), 2);

        // Actualizar los valores
        $this->subtotal = $subtotal;
        $this->tax = $tax;
        $this->total = $total;

        // Guardar los cambios
        $this->save();

        // Log para depuración
        \Illuminate\Support\Facades\Log::info('Totales recalculados para orden #' . $this->id, [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'discount' => $this->discount,
            'total' => $total,
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
     * @param int|null $customerId ID del cliente (opcional, si no se proporciona se usa el cliente de la orden)
     * @return Invoice|null
     */
    public function generateInvoice(string $invoiceType, string $series, ?int $customerId = null): ?Invoice
    {
        // Verificar si la orden está pagada completamente
        if (!$this->isFullyPaid()) {
            return null; // No se puede facturar si no está pagada completamente
        }

        // Verificar si ya está facturada
        if ($this->billed) {
            return null; // Ya está facturada
        }

        // Obtener el siguiente número de factura para la serie
        $lastInvoice = Invoice::where('series', $series)->latest('number')->first();
        $nextNumber = $lastInvoice ? (int)$lastInvoice->number + 1 : 1;
        $formattedNumber = str_pad($nextNumber, 8, '0', STR_PAD_LEFT);

        // Usar el cliente de la orden o el cliente genérico si no hay cliente
        $finalCustomerId = $customerId ?? $this->customer_id ?? 1; // Cliente genérico si no hay cliente

        // Obtener el cliente
        $customer = \App\Models\Customer::find($finalCustomerId);

        // Obtener el método de pago de la última transacción
        $lastPayment = $this->payments()->latest()->first();
        $paymentMethod = $lastPayment ? $lastPayment->payment_method : 'cash';
        $paymentAmount = $lastPayment ? $lastPayment->amount : $this->total;

        // Obtener información del anticipo si existe
        $advancePayment = $this->getQuotationAdvancePayment();
        $advanceNotes = $this->getQuotationAdvanceNotes();
        $pendingBalance = $this->total - $advancePayment;

        $invoice = new Invoice([
            'invoice_type' => $invoiceType,
            'series' => $series,
            'number' => $formattedNumber,
            'issue_date' => now()->format('Y-m-d'),
            'customer_id' => $finalCustomerId,
            'taxable_amount' => $this->subtotal,
            'tax' => $this->tax,
            'total' => $this->total,
            'tax_authority_status' => Invoice::STATUS_PENDING,
            'sunat_status' => in_array($invoiceType, ['invoice', 'receipt']) ? 'PENDIENTE' : 'NO_APLICA',
            'order_id' => $this->id,
            'payment_method' => $paymentMethod,
            'payment_amount' => $paymentAmount,
            'advance_payment_received' => $advancePayment,
            'advance_payment_notes' => $advanceNotes,
            'pending_balance' => $pendingBalance,
            'client_name' => $customer ? $customer->name : 'Cliente General',
            'client_document' => $customer ? $customer->document_number : '00000000',
            'client_address' => $customer ? $customer->address : 'Sin dirección',
        ]);

        $invoice->save();

        // Crear los detalles de la factura
        foreach ($this->orderDetails as $detail) {
            $invoice->details()->create([
                'product_id' => $detail->product_id,
                'description' => $detail->product->name,
                'quantity' => $detail->quantity,
                'unit_price' => $detail->unit_price,
                'subtotal' => $detail->subtotal,
            ]);
        }

        // Marcar la orden como facturada
        $this->billed = true;
        $this->save();

        // Enviar a la autoridad tributaria (SUNAT)
        $invoice->sendToTaxAuthority();

        return $invoice;
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
