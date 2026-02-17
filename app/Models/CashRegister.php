<?php

namespace App\Models;

use App\Enums\PaymentMethodEnum;
use App\Traits\CashRegisterCalculations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Modelo CashRegister para gestión de cajas registradoras
 *
 * Este modelo gestiona la apertura y cierre de cajas registradoras,
 * así como el registro de ventas por método de pago.
 */
class CashRegister extends Model
{
    use HasFactory, CashRegisterCalculations;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'opened_by',
        'closed_by',
        'approved_by',
        'opening_amount',
        'expected_amount',
        'actual_amount',
        'difference',
        'opening_datetime',
        'closing_datetime',
        'approval_datetime',
        'observations',
        'is_active',
        'is_approved',
        'approval_notes',
        'cash_sales',
        'card_sales',
        'other_sales',
        'total_sales',
        // Campos de conteo manual
        'manual_yape',
        'manual_plin',
        'manual_card',
        'manual_didi',
        'manual_pedidos_ya',
        'manual_bita_express',
        'manual_otros',
        // Campos de egresos
        'total_expenses',
        'expenses_notes',
        'expense_method',
        // Campos de billetes y monedas
        'bill_200',
        'bill_100',
        'bill_50',
        'bill_20',
        'bill_10',
        'coin_5',
        'coin_2',
        'coin_1',
        'coin_050',
        'coin_020',
        'coin_010',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'opening_amount' => 'decimal:2',
        'expected_amount' => 'decimal:2',
        'actual_amount' => 'decimal:2',
        'difference' => 'decimal:2',
        'cash_sales' => 'decimal:2',
        'card_sales' => 'decimal:2',
        'other_sales' => 'decimal:2',
        'total_sales' => 'decimal:2',
        'opening_datetime' => 'datetime',
        'closing_datetime' => 'datetime',
        'approval_datetime' => 'datetime',
        'is_active' => 'boolean',
        'is_approved' => 'boolean',
        // Campos de conteo manual
        'manual_yape' => 'decimal:2',
        'manual_plin' => 'decimal:2',
        'manual_card' => 'decimal:2',
        'manual_didi' => 'decimal:2',
        'manual_pedidos_ya' => 'decimal:2',
        'manual_bita_express' => 'decimal:2',
        'manual_otros' => 'decimal:2',
        // Campos de egresos
        'total_expenses' => 'decimal:2',
        // Campos de billetes y monedas
        'bill_200' => 'integer',
        'bill_100' => 'integer',
        'bill_50' => 'integer',
        'bill_20' => 'integer',
        'bill_10' => 'integer',
        'coin_5' => 'integer',
        'coin_2' => 'integer',
        'coin_1' => 'integer',
        'coin_050' => 'integer',
        'coin_020' => 'integer',
        'coin_010' => 'integer',
    ];

    // Constantes para estados de caja
    const STATUS_OPEN = 1;
    const STATUS_CLOSED = 0;

    /**
     * Obtiene el usuario que abrió la caja.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    /**
     * Obtiene el usuario que abrió la caja.
     */
    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    /**
     * Obtiene el usuario que cerró la caja.
     */
    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /**
     * Obtiene el usuario que aprobó el cierre de caja.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Obtiene los pagos asociados a esta caja.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Obtiene los movimientos de efectivo asociados a esta caja.
     */
    public function cashMovements(): HasMany
    {
        return $this->hasMany(CashMovement::class);
    }

    /**
     * Obtiene las órdenes asociadas a esta caja.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Obtiene los comprobantes asociados a esta caja a través de sus órdenes.
     */
    public function invoices(): HasManyThrough
    {
        return $this->hasManyThrough(
            Invoice::class,
            Order::class,
            'cash_register_id',
            'order_id',
            'id',
            'id'
        );
    }

    /**
     * Obtiene los egresos detallados asociados a esta caja.
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(CashRegisterExpense::class);
    }

    /**
     * Obtiene los egresos detallados asociados a esta caja (alias para cashRegisterExpenses).
     */
    public function cashRegisterExpenses(): HasMany
    {
        return $this->hasMany(CashRegisterExpense::class);
    }

    /**
     * Verifica si existe una caja abierta.
     *
     * @return CashRegister|null
     */
    public static function getOpenRegister(): ?CashRegister
    {
        return self::where('is_active', self::STATUS_OPEN)->first();
    }

    public static function hasOpenRegister(): bool
    {
        return self::where('is_active', self::STATUS_OPEN)->exists();
    }

    /**
     * Obtiene el ID de la caja registradora activa.
     *
     * @return int|null
     */
    public static function getActiveCashRegisterId(): ?int
    {
        $register = self::getOpenRegister();
        return $register ? $register->id : null;
    }

    /**
     * Abre una nueva caja registradora.
     *
     * @param float $openingAmount Monto inicial
     * @param string|null $observations Observaciones
     * @return CashRegister
     * @throws \Exception Si ya existe una caja abierta
     */
    public static function openRegister(float $openingAmount, ?string $observations = null): CashRegister
    {
        return DB::transaction(function () use ($openingAmount, $observations) {
            // Verificar si ya existe una caja abierta (doble verificación)
            self::validateNoOpenRegisters($openingAmount);

            // Crear la caja
            $cashRegister = self::createNewRegister($openingAmount, $observations);

            // Registrar en el log
            self::logRegisterOpening($cashRegister, $openingAmount);

            return $cashRegister;
        });
    }

    /**
     * Valida que no existan cajas abiertas.
     *
     * @param float $openingAmount Para registro en log
     * @throws \Exception Si ya existe una caja abierta
     * @return void
     */
    private static function validateNoOpenRegisters(float $openingAmount): void
    {
        // lockForUpdate previene race conditions: dos cajeros abriendo caja simultaneamente
        $exists = self::where('is_active', self::STATUS_OPEN)
            ->lockForUpdate()
            ->exists();

        if ($exists) {
            Log::warning('Intento de abrir una caja cuando ya existe una abierta', [
                'user_id' => Auth::id(),
                'opening_amount' => $openingAmount
            ]);
            throw new \Exception('Ya existe una caja abierta. No se puede abrir otra.');
        }
    }

    /**
     * Crea un nuevo registro de caja.
     *
     * @param float $openingAmount Monto inicial
     * @param string|null $observations Observaciones
     * @return CashRegister
     */
    private static function createNewRegister(float $openingAmount, ?string $observations): CashRegister
    {
        return self::create([
            'opened_by' => Auth::id(),
            'opening_amount' => $openingAmount,
            'opening_datetime' => now(),
            'observations' => $observations,
            'is_active' => self::STATUS_OPEN,
            'cash_sales' => 0,
            'card_sales' => 0,
            'other_sales' => 0,
            'total_sales' => 0,
        ]);
    }

    /**
     * Registra en el log la apertura de caja.
     *
     * @param CashRegister $cashRegister Caja registradora
     * @param float $openingAmount Monto inicial
     * @return void
     */
    private static function logRegisterOpening(CashRegister $cashRegister, float $openingAmount): void
    {
        Log::info('Caja registradora abierta', [
            'cash_register_id' => $cashRegister->id,
            'user_id' => Auth::id(),
            'opening_amount' => $openingAmount,
            'opening_datetime' => $cashRegister->opening_datetime
        ]);
    }

    /**
     * Cierra la caja registradora.
     *
     * @param array $data Datos del cierre
     * @return bool
     */
    public function close(array $data): bool
    {
        return DB::transaction(function () use ($data) {
            $this->setClosingData($data);
            $this->updateObservations($data);
            $this->is_active = self::STATUS_CLOSED;

            if (!$this->save()) {
                throw new \RuntimeException("Error al guardar el cierre de caja #{$this->id}");
            }

            $this->logRegisterClosing();

            return true;
        });
    }

    /**
     * Establece los datos de cierre.
     *
     * @param array $data Datos del cierre
     * @return void
     */
    private function setClosingData(array $data): void
    {
        $this->closed_by = Auth::id();
        $this->actual_amount = $data['actual_cash'] ?? 0;
        $this->expected_amount = $data['expected_cash'] ?? 0;
        $this->difference = $data['difference'] ?? 0;
        $this->closing_datetime = now();
    }

    /**
     * Actualiza las observaciones con las notas de cierre.
     *
     * @param array $data Datos del cierre
     * @return void
     */
    private function updateObservations(array $data): void
    {
        if (isset($data['notes'])) {
            $this->observations = $this->observations
                ? $this->observations . "\n" . $data['notes']
                : $data['notes'];
        }
    }

    /**
     * Registra en el log el cierre de caja.
     *
     * @return void
     */
    private function logRegisterClosing(): void
    {
        Log::info('Caja registradora cerrada', [
            'cash_register_id' => $this->id,
            'user_id' => Auth::id(),
            'actual_amount' => $this->actual_amount,
            'expected_amount' => $this->expected_amount,
            'difference' => $this->difference,
            'closing_datetime' => $this->closing_datetime
        ]);
    }

    /**
     * Registra una venta en la caja según el método de pago.
     *
     * @param string $paymentMethod Método de pago
     * @param float $amount Monto
     * @return bool
     */
    public function registerSale(string $paymentMethod, float $amount): bool
    {
        return DB::transaction(function () use ($paymentMethod, $amount) {
            // Actualizar los totales según el método de pago
            $this->updateSalesByPaymentMethod($paymentMethod, $amount);

            // Actualizar el total general
            $this->updateTotalSales();

            // Guardar los cambios
            $saved = $this->save();

            // Registrar en el log si se guardó correctamente
            if ($saved) {
                $this->logSaleRegistration($paymentMethod, $amount);
            }

            return $saved;
        });
    }

    /**
     * Actualiza los totales de ventas según el método de pago.
     *
     * @param string $paymentMethod Método de pago
     * @param float $amount Monto
     * @return void
     */
    private function updateSalesByPaymentMethod(string $paymentMethod, float $amount): void
    {
        // Usar match para un código más limpio y mantenible
        match (true) {
            $paymentMethod === Payment::METHOD_CASH => $this->cash_sales += $amount,
            in_array($paymentMethod, [Payment::METHOD_CARD, Payment::METHOD_CREDIT_CARD, Payment::METHOD_DEBIT_CARD], true) => $this->card_sales += $amount,
            default => $this->other_sales += $amount,
        };
    }

    /**
     * Actualiza el total de ventas.
     *
     * @return void
     */
    private function updateTotalSales(): void
    {
        $this->total_sales = $this->cash_sales + $this->card_sales + $this->other_sales;
    }

    /**
     * Registra en el log la venta.
     *
     * @param string $paymentMethod Método de pago
     * @param float $amount Monto
     * @return void
     */
    private function logSaleRegistration(string $paymentMethod, float $amount): void
    {
        $methodName = match ($paymentMethod) {
            Payment::METHOD_CASH => 'efectivo',
            Payment::METHOD_CARD, Payment::METHOD_CREDIT_CARD, Payment::METHOD_DEBIT_CARD => 'tarjeta',
            Payment::METHOD_DIGITAL_WALLET => 'billetera digital',
            Payment::METHOD_BANK_TRANSFER => 'transferencia bancaria',
            Payment::METHOD_RAPPI => 'rappi',
            Payment::METHOD_BITA_EXPRESS => 'bita express',
            default => $paymentMethod
        };

        Log::info("Venta de S/ {$amount} registrada en caja con {$methodName}", [
            'cash_register_id' => $this->id,
            'payment_method' => $paymentMethod,
            'amount' => $amount,
            'user_id' => Auth::id(),
            'cash_sales' => $this->cash_sales,
            'card_sales' => $this->card_sales,
            'other_sales' => $this->other_sales,
            'total_sales' => $this->total_sales
        ]);
    }

    // calculateExpectedCash() y getCachedExpenses() vienen del trait CashRegisterCalculations
    // como unica fuente de verdad para estos calculos.

    /**
     * Calcula el total de efectivo contado (billetes y monedas).
     *
     * @return float
     */
    public function calculateCountedCash(): float
    {
        return ($this->bill_200 ?? 0) * 200 +
            ($this->bill_100 ?? 0) * 100 +
            ($this->bill_50 ?? 0) * 50 +
            ($this->bill_20 ?? 0) * 20 +
            ($this->bill_10 ?? 0) * 10 +
            ($this->coin_5 ?? 0) * 5 +
            ($this->coin_2 ?? 0) * 2 +
            ($this->coin_1 ?? 0) * 1 +
            ($this->coin_050 ?? 0) * 0.5 +
            ($this->coin_020 ?? 0) * 0.2 +
            ($this->coin_010 ?? 0) * 0.1;
    }

    /**
     * Calcula el total de otros métodos de pago ingresados manualmente.
     *
     * @return float
     */
    public function calculateOtherPayments(): float
    {
        return ($this->manual_yape ?? 0) +
            ($this->manual_plin ?? 0) +
            ($this->manual_card ?? 0) +
            ($this->manual_didi ?? 0) +
            ($this->manual_pedidos_ya ?? 0) +
            ($this->manual_bita_express ?? 0) +
            ($this->manual_otros ?? 0);
    }

    /**
     * Calcula el total contado (efectivo + otros métodos).
     *
     * @return float
     */
    public function calculateTotalCounted(): float
    {
        return $this->calculateCountedCash() + $this->calculateOtherPayments();
    }

    /**
     * Calcula la diferencia final del cierre.
     * NUEVA FÓRMULA: Total contado - Monto esperado (positivo = sobrante, negativo = faltante)
     *
     * @return float
     */
    public function calculateFinalDifference(): float
    {
        return $this->calculateTotalCounted() - $this->calculateExpectedCash();
    }

    /**
     * Realiza la reconciliación final de la caja.
     *
     * @param bool $isApproved Si la caja es aprobada
     * @param string|null $notes Notas de aprobación
     * @param int|null $approvedBy ID del usuario que aprueba (si no se proporciona, se usa el usuario autenticado)
     * @return bool
     * @throws \Exception Si la caja está abierta o ya está aprobada
     */
    public function reconcile(bool $isApproved, ?string $notes = null, ?int $approvedBy = null): bool
    {
        return DB::transaction(function () use ($isApproved, $notes, $approvedBy) {
            $this->validateCanBeReconciled();
            $this->setReconciliationData($isApproved, $notes, $approvedBy);

            if (!$this->save()) {
                throw new \RuntimeException("Error al guardar la reconciliacion de caja #{$this->id}");
            }

            $this->logReconciliation($isApproved, $notes);

            return true;
        });
    }

    /**
     * Valida que la caja pueda ser reconciliada.
     *
     * @throws \Exception Si la caja no puede ser reconciliada
     * @return void
     */
    private function validateCanBeReconciled(): void
    {
        // Verificar que la caja esté cerrada
        if ($this->is_active) {
            throw new \Exception('No se puede reconciliar una caja abierta. Cierre la caja primero.');
        }

        // Verificar que la caja no esté ya aprobada
        if ($this->is_approved) {
            throw new \Exception('Esta caja ya ha sido reconciliada y aprobada.');
        }
    }

    /**
     * Establece los datos de reconciliación.
     *
     * @param bool $isApproved Si la caja es aprobada
     * @param string|null $notes Notas de aprobación
     * @param int|null $approvedBy ID del usuario que aprueba
     * @return void
     */
    private function setReconciliationData(bool $isApproved, ?string $notes, ?int $approvedBy): void
    {
        $this->is_approved = $isApproved;
        $this->approval_notes = $notes;
        $this->approved_by = $approvedBy ?? Auth::id();
        $this->approval_datetime = now();
    }

    /**
     * Registra en el log la reconciliación.
     *
     * @param bool $isApproved Si la caja fue aprobada
     * @param string|null $notes Notas de aprobación
     * @return void
     */
    private function logReconciliation(bool $isApproved, ?string $notes): void
    {
        Log::info('Caja reconciliada', [
            'cash_register_id' => $this->id,
            'approved' => $isApproved,
            'difference' => $this->difference,
            'approved_by' => $this->approved_by,
            'notes' => $notes
        ]);
    }

    /**
     * Obtiene el estado de la caja en formato legible.
     *
     * @return string
     */
    public function getStatusAttribute(): string
    {
        return $this->is_active ? 'Abierta' : 'Cerrada';
    }

    /**
     * Obtiene el color del estado para mostrar en la interfaz.
     *
     * @return string
     */
    public function getStatusColorAttribute(): string
    {
        return $this->is_active ? 'success' : 'danger';
    }

    /**
     * Obtiene el estado de reconciliación en formato legible.
     *
     * @return string
     */
    public function getReconciliationStatusAttribute(): string
    {
        if ($this->is_active) {
            return 'Pendiente de cierre';
        }

        if ($this->is_approved) {
            return 'Aprobada';
        }

        // Si tiene notas de aprobación pero no está aprobada, fue rechazada
        if (!$this->is_approved && $this->approval_notes && $this->approval_datetime) {
            return 'Rechazada';
        }

        return 'Pendiente de reconciliación';
    }

    /**
     * Obtiene el color del estado de reconciliación para mostrar en la interfaz.
     *
     * @return string
     */
    public function getReconciliationStatusColorAttribute(): string
    {
        if ($this->is_active) {
            return 'info';
        }

        if ($this->is_approved) {
            return 'success';
        }

        // Si tiene notas de aprobación pero no está aprobada, fue rechazada
        if (!$this->is_approved && $this->approval_notes && $this->approval_datetime) {
            return 'danger';
        }

        return 'warning';
    }

    /**
     * Obtiene las ventas en efectivo del sistema para esta caja.
     *
     * @return float
     */
    public function getSystemCashSales(): float
    {
        return $this->getSalesByMethod(PaymentMethodEnum::CASH);
    }

    /**
     * Obtiene las ventas con Yape del sistema para esta caja.
     *
     * @return float
     */
    public function getSystemYapeSales(): float
    {
        return $this->getSalesByMethod(PaymentMethodEnum::YAPE);
    }

    /**
     * Obtiene las ventas con Plin del sistema para esta caja.
     *
     * @return float
     */
    public function getSystemPlinSales(): float
    {
        return $this->getSalesByMethod(PaymentMethodEnum::PLIN);
    }

    /**
     * Obtiene las ventas con tarjeta del sistema para esta caja.
     *
     * @return float
     */
    public function getSystemCardSales(): float
    {
        return $this->getSalesByMethod(PaymentMethodEnum::CARD);
    }

    /**
     * Obtiene las ventas con transferencia bancaria del sistema para esta caja.
     *
     * @return float
     */
    public function getSystemBankTransferSales(): float
    {
        return $this->getSalesByMethod(PaymentMethodEnum::BANK_TRANSFER);
    }

    /**
     * Obtiene las ventas con billetera digital (excluyendo Yape y Plin) del sistema para esta caja.
     *
     * @return float
     */
    public function getSystemOtherDigitalWalletSales(): float
    {
        return $this->getSalesByMethod(PaymentMethodEnum::DIGITAL_WALLET);
    }

    /**
     * Obtiene las ventas con Didi Food del sistema para esta caja.
     *
     * @return float
     */
    public function getSystemDidiSales(): float
    {
        return $this->getSalesByMethod(PaymentMethodEnum::DIDI_FOOD);
    }

    /**
     * Obtiene las ventas con PedidosYa del sistema para esta caja.
     *
     * @return float
     */
    public function getSystemPedidosYaSales(): float
    {
        return $this->getSalesByMethod(PaymentMethodEnum::PEDIDOS_YA);
    }

    /**
     * Obtiene las ventas con Bita Express del sistema para esta caja.
     *
     * @return float
     */
    public function getSystemBitaExpressSales(): float
    {
        return $this->getSalesByMethod(PaymentMethodEnum::BITA_EXPRESS);
    }

    /**
     * Delega al trait para mantener una sola fuente de verdad.
     * Los metodos getSystem*Sales() individuales se mantienen por retrocompatibilidad
     * con el Resource y otros consumidores.
     */
    public function getSystemTotalSales(): float
    {
        return $this->getTotalSystemSales();
    }
}
