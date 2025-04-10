<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'invoice_type',
        'series',
        'number',
        'issue_date',
        'customer_id',
        'taxable_amount',
        'tax',
        'total',
        'tax_authority_status',
        'hash',
        'qr_code',
        'order_id',
        'voided_reason',
        'voided_date',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'issue_date' => 'date',
        'taxable_amount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'voided_date' => 'date',
    ];

    /**
     * Constantes para estados con la autoridad tributaria
     */
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';
    const STATUS_VOIDED = 'voided';

    /**
     * Obtiene la orden asociada a la factura.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Obtiene el cliente asociado a la factura.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Obtiene los detalles de la factura.
     */
    public function details(): HasMany
    {
        return $this->hasMany(InvoiceDetail::class);
    }

    /**
     * Devuelve el número de comprobante formateado (serie-número).
     */
    public function getFormattedNumberAttribute(): string
    {
        return "{$this->series}-{$this->number}";
    }

    /**
     * Devuelve el tipo de comprobante en formato legible.
     */
    public function getDocumentTypeAttribute(): string
    {
        return match($this->invoice_type) {
            'invoice' => 'Factura Electrónica',
            'receipt' => $this->order_id ? 'Boleta Electrónica' : 'Nota de Venta',
            'credit_note' => 'Nota de Crédito',
            'debit_note' => 'Nota de Débito',
            default => 'Comprobante',
        };
    }

    /**
     * Verifica si el comprobante puede ser anulado.
     *
     * Según SUNAT, solo se pueden anular comprobantes:
     * - En estado pendiente o aceptado
     * - Con menos de 7 días desde su emisión
     */
    public function canBeVoided(): bool
    {
        // Solo se pueden anular comprobantes pendientes o aceptados
        if (!in_array($this->tax_authority_status, [self::STATUS_PENDING, self::STATUS_ACCEPTED])) {
            return false;
        }

        // Verificar que no hayan pasado más de 7 días desde la emisión
        $maxVoidDays = 7;
        $daysFromIssue = now()->diffInDays($this->issue_date);

        return $daysFromIssue <= $maxVoidDays;
    }

    /**
     * Anula el comprobante con el motivo indicado.
     */
    public function void(string $reason): bool
    {
        if (!$this->canBeVoided()) {
            return false;
        }

        $this->tax_authority_status = self::STATUS_VOIDED;
        $this->voided_reason = $reason;
        $this->voided_date = now();

        return $this->save();
    }
}
