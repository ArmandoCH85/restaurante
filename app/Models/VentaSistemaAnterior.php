<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaSistemaAnterior extends Model
{
    use HasFactory;

    protected $table = 'ventas_sistema_anterior';

    protected $fillable = [
        'fecha_venta',
        'caja',
        'cliente',
        'documento',
        'canal_venta',
        'tipo_pago',
        'total',
        'estado',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}