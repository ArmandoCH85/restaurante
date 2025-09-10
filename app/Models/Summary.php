<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Summary extends Model
{
    use HasFactory;

    protected $fillable = [
        'correlativo',
        'fecha_referencia',
        'fecha_generacion',
        'ticket',
        'status',
        'sunat_code',
        'sunat_description',
        'receipts_count',
        'total_amount',
        'xml_path',
        'cdr_path',
        'receipts_data',
        'error_message',
        'processing_time_ms',
    ];

    protected $casts = [
        'fecha_referencia' => 'date',
        'fecha_generacion' => 'date',
        'receipts_data' => 'array',
        'total_amount' => 'decimal:2',
        'receipts_count' => 'integer',
        'processing_time_ms' => 'integer',
    ];

    // Estados disponibles
    const STATUS_PENDING = 'PENDIENTE';
    const STATUS_PROCESSING = 'EN_PROCESO';
    const STATUS_SENT = 'ENVIADO';
    const STATUS_ACCEPTED = 'ACEPTADO';
    const STATUS_REJECTED = 'RECHAZADO';
    const STATUS_ERROR = 'ERROR';

    /**
     * Generar siguiente correlativo para resúmenes
     */
    public static function generateCorrelativo(string $fecha): string
    {
        // Obtener la serie activa para resúmenes de boletas
        $documentSeries = \App\Models\DocumentSeries::where('document_type', 'summary')
            ->where('active', true)
            ->first();
        
        if (!$documentSeries) {
            throw new \Exception('No se encontró una serie activa para resúmenes de boletas. Debe crear una serie en: /admin/document-series');
        }
        
        // Obtener el siguiente número de la serie
        $nextNumber = $documentSeries->getNextNumber();
        
        // Formato: RC-YYYYMMDD-001
        $fechaFormat = Carbon::parse($fecha)->format('Ymd');
        
        return "{$documentSeries->series}-{$fechaFormat}-" . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Verificar si el resumen está pendiente
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Verificar si el resumen fue enviado
     */
    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    /**
     * Verificar si el resumen fue aceptado
     */
    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    /**
     * Verificar si el resumen fue rechazado
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Verificar si hay error
     */
    public function hasError(): bool
    {
        return $this->status === self::STATUS_ERROR;
    }

    /**
     * Obtener color del badge según el estado
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_SENT => 'info',
            self::STATUS_ACCEPTED => 'success',
            self::STATUS_REJECTED => 'danger',
            self::STATUS_ERROR => 'danger',
            default => 'secondary'
        };
    }

    /**
     * Obtener ícono según el estado
     */
    public function getStatusIcon(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'heroicon-o-clock',
            self::STATUS_SENT => 'heroicon-o-paper-airplane',
            self::STATUS_ACCEPTED => 'heroicon-o-check-circle',
            self::STATUS_REJECTED => 'heroicon-o-x-circle',
            self::STATUS_ERROR => 'heroicon-o-exclamation-triangle',
            default => 'heroicon-o-question-mark-circle'
        };
    }

    /**
     * Scope para filtrar por estado
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope para filtrar por fecha de referencia
     */
    public function scopeByReferenceDate($query, string $date)
    {
        return $query->whereDate('fecha_referencia', $date);
    }

    /**
     * Scope para resúmenes recientes
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
