<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
    use HasFactory;

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
     * Verifica si existe una caja abierta.
     *
     * @return CashRegister|null
     */
    public static function getOpenRegister(): ?CashRegister
    {
        // Usar una transacción para evitar condiciones de carrera
        return DB::transaction(function () {
            return self::where('is_active', self::STATUS_OPEN)
                ->first();
        });
    }

    /**
     * Verifica si existe una caja abierta.
     *
     * @return bool
     */
    public static function hasOpenRegister(): bool
    {
        // Usar una transacción para evitar condiciones de carrera
        return DB::transaction(function () {
            return self::where('is_active', self::STATUS_OPEN)
                ->exists();
        });
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
        if (self::where('is_active', self::STATUS_OPEN)->exists()) {
            Log::warning('Intento de abrir una caja cuando ya existe una abierta', [
                'user_id' => Auth::id(),
                'user_name' => Auth::user()->name,
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
            'user_name' => Auth::user()->name,
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
        $this->setClosingData($data);
        $this->updateObservations($data);
        $this->is_active = self::STATUS_CLOSED;

        $saved = $this->save();

        if ($saved) {
            $this->logRegisterClosing();
        }

        return $saved;
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
            'user_name' => Auth::user()->name,
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
        match(true) {
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
        $methodName = match($paymentMethod) {
            Payment::METHOD_CASH => 'efectivo',
            Payment::METHOD_CARD, Payment::METHOD_CREDIT_CARD, Payment::METHOD_DEBIT_CARD => 'tarjeta',
            Payment::METHOD_DIGITAL_WALLET => 'billetera digital',
            Payment::METHOD_BANK_TRANSFER => 'transferencia bancaria',
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

    /**
     * Calcula el monto esperado en efectivo al cierre.
     *
     * @return float
     */
    public function calculateExpectedCash(): float
    {
        return $this->opening_amount + $this->cash_sales;
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
        $this->validateCanBeReconciled();
        $this->setReconciliationData($isApproved, $notes, $approvedBy);

        $saved = $this->save();

        if ($saved) {
            $this->logReconciliation($isApproved, $notes);
        }

        return $saved;
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
}
