<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\CalculatesIgv;

class CreditNote extends Model
{
    use CalculatesIgv;

    protected $fillable = [
        'invoice_id',
        'series',
        'number',
        'issue_date',
        'motivo_codigo',
        'motivo_descripcion',
        'subtotal',
        'tax',
        'total',
        'sunat_status',
        'sunat_code',
        'sunat_description',
        'xml_path',
        'cdr_path',
        'pdf_path',
        'sent_at',
        'retry_count',
        'created_by'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'sent_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'retry_count' => 'integer'
    ];

    // Estados SUNAT
    const STATUS_PENDING = 'PENDIENTE';
    const STATUS_SENDING = 'ENVIANDO';
    const STATUS_ACCEPTED = 'ACEPTADO';
    const STATUS_REJECTED = 'RECHAZADO';
    const STATUS_ERROR = 'ERROR';

    // Códigos de motivo según catálogo SUNAT
    const MOTIVO_ANULACION = '01';
    const MOTIVO_ERROR_RUC = '02';
    const MOTIVO_ERROR_DESCRIPCION = '03';
    const MOTIVO_DESCUENTO_GLOBAL = '04';
    const MOTIVO_DESCUENTO_ITEM = '05';
    const MOTIVO_DEVOLUCION = '06';
    const MOTIVO_BONIFICACION = '07';
    const MOTIVO_DESCUENTO_PROMOCION = '08';
    const MOTIVO_OTROS = '09';

    /**
     * Relación con la factura original
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Relación con el usuario que creó la nota
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Obtiene el tipo de documento según la codificación de SUNAT
     */
    public function getSunatDocumentType(): string
    {
        return '07'; // Nota de Crédito
    }

    /**
     * Verifica si la nota de crédito fue aceptada por SUNAT
     */
    public function isAcceptedBySunat(): bool
    {
        return $this->sunat_status === self::STATUS_ACCEPTED;
    }

    /**
     * Verifica si la nota de crédito fue rechazada por SUNAT
     */
    public function isRejectedBySunat(): bool
    {
        return $this->sunat_status === self::STATUS_REJECTED;
    }

    /**
     * Verifica si la nota de crédito está pendiente de envío
     */
    public function isPending(): bool
    {
        return $this->sunat_status === self::STATUS_PENDING;
    }

    /**
     * Obtiene el número completo del comprobante
     */
    public function getFullNumberAttribute(): string
    {
        return $this->series . '-' . $this->number;
    }

    /**
     * Obtiene la descripción del motivo según el código
     */
    public static function getMotivoDescripcion(string $codigo): string
    {
        return match($codigo) {
            self::MOTIVO_ANULACION => 'Anulación de la operación',
            self::MOTIVO_ERROR_RUC => 'Anulación por error en el RUC',
            self::MOTIVO_ERROR_DESCRIPCION => 'Corrección por error en la descripción',
            self::MOTIVO_DESCUENTO_GLOBAL => 'Descuento global',
            self::MOTIVO_DESCUENTO_ITEM => 'Descuento por ítem',
            self::MOTIVO_DEVOLUCION => 'Devolución',
            self::MOTIVO_BONIFICACION => 'Bonificación',
            self::MOTIVO_DESCUENTO_PROMOCION => 'Descuento por promoción',
            self::MOTIVO_OTROS => 'Otros',
            default => 'Motivo no especificado'
        };
    }

    /**
     * Scope para notas de crédito pendientes
     */
    public function scopePending($query)
    {
        return $query->where('sunat_status', self::STATUS_PENDING);
    }

    /**
     * Scope para notas de crédito aceptadas
     */
    public function scopeAccepted($query)
    {
        return $query->where('sunat_status', self::STATUS_ACCEPTED);
    }

    /**
     * Scope para notas de crédito rechazadas
     */
    public function scopeRejected($query)
    {
        return $query->where('sunat_status', self::STATUS_REJECTED);
    }
}
