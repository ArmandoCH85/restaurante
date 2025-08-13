<?php

namespace App\Models;

use App\Traits\CalculatesIgv;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory, CalculatesIgv;

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
        'employee_id',
        'taxable_amount',
        'tax',
        'total',
        'tax_authority_status',
        'hash',
        'qr_code',
        'order_id',
        'voided_reason',
        'voided_date',
        'payment_method',
        'payment_amount',
        'change_amount',
        'advance_payment_received',
        'advance_payment_notes',
        'pending_balance',
        'client_name',
        'client_document',
        'client_address',
        // Campos SUNAT
        'xml_path',
        'pdf_path',
        'cdr_path',
        'sunat_status',
        'sunat_code',
        'sunat_description',
        'hash_sign',
        'sent_at',
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
        'payment_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
        'advance_payment_received' => 'decimal:2',
        'pending_balance' => 'decimal:2',
        'voided_date' => 'date',
    ];

    /**
     * Relaciones a cargar automáticamente
     */
    protected $with = ['customer'];

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
     * Obtiene el empleado que emitió la factura.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    // Helper methods for testing
    public function isInvoice(): bool
    {
        return $this->invoice_type === 'invoice';
    }

    public function isReceipt(): bool
    {
        return $this->invoice_type === 'receipt';
    }

    public function isSalesNote(): bool
    {
        return $this->invoice_type === 'sales_note';
    }

    public function isPendingSunat(): bool
    {
        return $this->sunat_status === null || $this->sunat_status === 'PENDIENTE';
    }

    public function isAcceptedBySunat(): bool
    {
        return $this->sunat_status === 'ACEPTADO';
    }

    public function isRejectedBySunat(): bool
    {
        return $this->sunat_status === 'RECHAZADO';
    }

    // Scopes
    public function scopeSunatEligible($query)
    {
        return $query->whereIn('invoice_type', ['invoice', 'receipt']);
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

    /**
     * Genera el hash para el comprobante.
     *
     * @return string El hash generado
     */
    public function generateHash(): string
    {
        // En una implementación real, este hash se generaría con los datos del comprobante
        // y la clave privada del emisor según las especificaciones de SUNAT
        $data = [
            'ruc' => config('company.ruc', '20123456789'),
            'tipo_documento' => $this->getSunatDocumentType(),
            'serie' => $this->series,
            'numero' => $this->number,
            'fecha_emision' => $this->issue_date->format('Y-m-d'),
            'total' => $this->total
        ];

        return hash('sha256', json_encode($data));
    }

    /**
     * Genera el código QR para el comprobante.
     *
     * @return string El código QR en formato base64
     */
    public function generateQRCode(): string
    {
        // En una implementación real, se generaría un código QR con los datos del comprobante
        // según las especificaciones de SUNAT
        $data = [
            'ruc' => config('company.ruc', '20123456789'),
            'tipo_documento' => $this->getSunatDocumentType(),
            'serie' => $this->series,
            'numero' => $this->number,
            'monto_total' => $this->total,
            'fecha_emision' => $this->issue_date->format('Y-m-d')
        ];

        // Aquí se usaría una librería para generar el QR
        // Por ahora, devolvemos un placeholder
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=';
    }

    /**
     * Obtiene el tipo de documento según la codificación de SUNAT.
     *
     * @return string El código de tipo de documento SUNAT
     */
    public function getSunatDocumentType(): string
    {
        return match($this->invoice_type) {
            'invoice' => '01', // Factura
            'receipt' => '03', // Boleta
            'credit_note' => '07', // Nota de Crédito
            'debit_note' => '08', // Nota de Débito
            default => '00', // Otros
        };
    }

    /**
     * Envía el comprobante a SUNAT para su validación.
     *
     * @return bool Si el envío fue exitoso
     */
    public function sendToTaxAuthority(): bool
    {
        // En una implementación real, aquí se usaría una librería como greenter o nubefact
        // para enviar el comprobante a SUNAT

        // Por ahora, simulamos una respuesta exitosa
        $this->hash = $this->generateHash();
        $this->qr_code = $this->generateQRCode();
        $this->tax_authority_status = self::STATUS_ACCEPTED;

        return $this->save();
    }

    /**
     * Calcula el subtotal sin IGV basado en el total que incluye IGV
     *
     * @return float
     */
    public function getCorrectSubtotalAttribute(): float
    {
        return $this->calculateSubtotalFromPriceWithIgv($this->total);
    }

    /**
     * Calcula el IGV incluido en el total
     *
     * @return float
     */
    public function getCorrectIgvAttribute(): float
    {
        return $this->calculateIncludedIgv($this->total);
    }

    /**
     * Obtiene el desglose correcto de IGV para mostrar en comprobantes
     *
     * @return array
     */
    public function getCorrectTaxBreakdown(): array
    {
        return $this->getIgvBreakdown($this->total);
    }
}
