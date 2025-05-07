<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    /**
     * Los atributos que son asignables masivamente.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'table_id',
        'reservation_date',
        'reservation_time',
        'guests_count',
        'status',
        'special_requests',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'reservation_date' => 'date',
        'reservation_time' => 'string', // Cambiado de datetime:H:i a string para evitar problemas de formato
        'guests_count' => 'integer',
    ];

    /**
     * Estados de reserva disponibles
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';

    /**
     * Obtiene el cliente asociado con esta reserva.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Obtiene la mesa asociada con esta reserva.
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    /**
     * Scope para filtrar reservas pendientes.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope para filtrar reservas confirmadas.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    /**
     * Scope para filtrar reservas canceladas.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Scope para filtrar reservas completadas.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope para filtrar reservas activas (pendientes o confirmadas).
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    /**
     * Scope para filtrar reservas para una fecha específica.
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('reservation_date', $date);
    }

    /**
     * Scope para filtrar reservas para una mesa específica.
     */
    public function scopeForTable($query, $tableId)
    {
        return $query->where('table_id', $tableId);
    }

    /**
     * Verifica si la reserva está pendiente.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Verifica si la reserva está confirmada.
     */
    public function isConfirmed(): bool
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    /**
     * Verifica si la reserva está cancelada.
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Verifica si la reserva está completada.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Verifica si la reserva está activa (pendiente o confirmada).
     */
    public function isActive(): bool
    {
        return $this->isPending() || $this->isConfirmed();
    }

    /**
     * Obtiene la fecha y hora de la reserva como un objeto DateTime.
     */
    public function getReservationDateTime()
    {
        // Asegurarse de que reservation_time sea una cadena válida
        $timeString = is_string($this->reservation_time) ? $this->reservation_time : $this->reservation_time->format('H:i:s');
        return $this->reservation_date->copy()->setTimeFromTimeString($timeString);
    }

    /**
     * Verifica si la reserva es para hoy.
     */
    public function isForToday(): bool
    {
        return $this->reservation_date->isToday();
    }

    /**
     * Verifica si la reserva es para una fecha futura.
     */
    public function isForFuture(): bool
    {
        return $this->reservation_date->isFuture() ||
               ($this->reservation_date->isToday() && now()->format('H:i') < $this->reservation_time);
    }

    /**
     * Verifica si la reserva es para una fecha pasada.
     */
    public function isForPast(): bool
    {
        return $this->reservation_date->isPast() ||
               ($this->reservation_date->isToday() && now()->format('H:i') > $this->reservation_time);
    }
}
