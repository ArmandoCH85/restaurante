<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Models\Invoice;

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
     * Obtiene el siguiente número correlativo de forma atómica, segura y auto-reparable.
     */
    public function getNextNumber(): string
    {
        return DB::transaction(function () {
            while (true) {
                // 1. Bloquear la fila para que ninguna otra transacción pueda modificarla.
                $series = self::where('id', $this->id)->lockForUpdate()->first();

                $currentNumber = $series->current_number;
                $nextNumberFormatted = str_pad($currentNumber, 8, '0', STR_PAD_LEFT);

                // 2. VERIFICACIÓN CRÍTICA: Asegurarse de que el número no esté ya en uso.
                $exists = Invoice::where('series', $series->series)
                                 ->where('number', $nextNumberFormatted)
                                 ->exists();

                // 3. Si el número ya existe, incrementamos el contador y probamos de nuevo.
                //    Esto auto-repara la secuencia si se desincroniza.
                if ($exists) {
                    $series->increment('current_number');
                    continue; // Vuelve al inicio del bucle while
                }

                // 4. Si el número está libre, lo usamos y lo preparamos para el siguiente.
                $series->increment('current_number');
                return $nextNumberFormatted;
            }
        });
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
            'credit_note' => 'Nota de Crédito',
            default => $this->document_type,
        };
    }
}
