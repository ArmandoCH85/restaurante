<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentSeries extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_type',
        'series',
        'current_number',
        'active',
        'description',
    ];

    protected $casts = [
        'active' => 'boolean',
        'current_number' => 'integer',
    ];

    /**
     * Obtiene el siguiente número correlativo y actualiza el contador
     */
    public function getNextNumber(): string
    {
        // Obtener el número actual
        $currentNumber = $this->current_number;

        // Incrementar el contador para la próxima factura
        $this->current_number = $currentNumber + 1;
        $this->save();

        // Devolver el número actual formateado con ceros a la izquierda (8 dígitos)
        return str_pad($currentNumber, 8, '0', STR_PAD_LEFT);
    }

    /**
     * Obtener el tipo de documento en formato legible
     */
    public function getDocumentTypeNameAttribute()
    {
        return match($this->document_type) {
            'invoice' => 'Factura',
            'receipt' => 'Boleta',
            'sales_note' => 'Nota de Venta',
            default => $this->document_type,
        };
    }
}
